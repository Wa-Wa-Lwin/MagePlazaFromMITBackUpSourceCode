<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Mageplaza\RewardPointsUltimate\Helper\Calculation;
use Mageplaza\RewardPointsUltimate\Helper\Data;

/**
 * Class ReferralsEarning
 * @package Mageplaza\RewardPointsUltimate\Observer
 */
class ReferralsEarning implements ObserverInterface
{
    /**
     * @var Calculation
     */
    protected $calculation;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * ReferralsEarning constructor.
     *
     * @param Calculation $calculation
     * @param Data $helperData
     */
    public function __construct(
        Calculation $calculation,
        Data $helperData
    ) {
        $this->calculation = $calculation;
        $this->helperData  = $helperData;
    }

    /**
     * @param EventObserver $observer
     *
     * @return $this|void
     * @throws NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        /** @var Quote $quote */
        $quote                   = $observer->getEvent()->getQuote();
        $items                   = $observer->getEvent()->getItems();
        $address                 = $observer->getEvent()->getShippingAssignment()->getShipping()->getAddress();
        $rules                   = $this->calculation->getReferralsRule($quote, $address);
        $storeId                 = $quote->getStoreId();
        $earningPointsWithCoupon = $this->helperData->getConfigEarning('earning_point_with_coupon', $storeId);

        if ($quote->getCouponCode() && !$earningPointsWithCoupon) {
            $this->calculation->resetRewardData(
                $items,
                $quote,
                ['mp_reward_earn', 'mp_reward_shipping_earn'],
                ['mp_reward_earn']
            );

            return $this;
        }

        if (!$rules) {
            return $this;
        }

        $items = $observer->getEvent()->getItems();
        $this->calculation->resetRewardData(
            $items,
            $quote,
            ['mp_reward_referral_earn', 'invited_earn'],
            ['mp_reward_referral_earn']
        );
        foreach ($rules as $rule) {
            $mpCustomerEarn = $mpRefererEarn = 0;
            $lastItem       = '';
            $this->calculation->resetDeltaRoundPoint('customer');
            $this->calculation->resetDeltaRoundPoint('referer');
            $totalCustomer = $totalReferer = $this->calculation->getTotalMatchRule($quote, $items, $rule, false, true);
            if ($rule->getCustomerApplyToShipping() && $totalCustomer) {
                $totalReferer -= $this->calculation->getShippingTotalForDiscount($quote);
            }

            if ($totalCustomer) {
                $totalRefererEarn  = $this->calculation->getReferrerPointsByAction($totalReferer, $rule);
                $totalCustomerEarn = $this->calculation->getCustomerPointsByAction($totalCustomer, $rule);
                foreach ($items as $item) {
                    if ($item->getParentItem()) {
                        continue;
                    }

                    if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                        /** @var Item $child */
                        foreach ($item->getChildren() as $child) {
                            $this->calculation->calculateItem(
                                $child,
                                $totalReferer,
                                $totalCustomer,
                                $totalRefererEarn,
                                $totalCustomerEarn,
                                $mpCustomerEarn,
                                $mpRefererEarn,
                                $lastItem
                            );
                        }
                    } else {
                        $this->calculation->calculateItem(
                            $item,
                            $totalReferer,
                            $totalCustomer,
                            $totalRefererEarn,
                            $totalCustomerEarn,
                            $mpCustomerEarn,
                            $mpRefererEarn,
                            $lastItem
                        );
                    }
                }

                /**
                 * Round point last item for referer
                 */
                if ($lastItem && $totalRefererEarn > $mpRefererEarn) {
                    $refererLastChildEarn = $this->calculation->roundPointForReferer(
                        $rule,
                        $totalRefererEarn,
                        $mpRefererEarn
                    );
                    $lastItem->setMpRewardReferralEarn($lastItem->getMpRewardReferralEarn() + $refererLastChildEarn);
                }

                $quote->setMpRewardReferralEarn($quote->getMpRewardReferralEarn() + $mpRefererEarn);

                if ($totalCustomerEarn) {
                    /**
                     * Round point shipping or last item for customer
                     */
                    if ($rule->getCustomerApplyToShipping()) {
                        $mpRewardShippingEarn = $this->calculation->roundPointForCustomer(
                            $rule,
                            $totalCustomerEarn,
                            $mpCustomerEarn,
                            true
                        );
                        $quote->setMpRewardShippingEarn($quote->getMpRewardShippingEarn() + $mpRewardShippingEarn);
                    } elseif ($lastItem && $totalCustomerEarn > $mpCustomerEarn) {
                        $customerLastItemEarn = $this->calculation->roundPointForCustomer(
                            $rule,
                            $totalCustomerEarn,
                            $mpCustomerEarn
                        );
                        $lastItem->setMpRewardEarn($lastItem->getMpRewardEarn() + $customerLastItemEarn);
                    }
                    $quote->setInvitedEarn($quote->getInvitedEarn() + $mpCustomerEarn);
                    $quote->setMpRewardEarn($quote->getMpRewardEarn() + $mpCustomerEarn);
                }
            }

            if ($rule->getStopRulesProcessing()) {
                break;
            }
        }
    }
}
