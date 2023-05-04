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

namespace Mageplaza\RewardPointsUltimate\Observer\GraphQl;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\RewardPointsUltimate\Model\InvitationRepository;

/**
 * Class GetInvitation
 * @package Mageplaza\RewardPointsUltimate\Observer\GraphQl
 */
class GetInvitation implements ObserverInterface
{
    /**
     * @var InvitationRepository
     */
    protected $invitationRepository;

    /**
     * GetInvitation constructor.
     *
     * @param InvitationRepository $invitationRepository
     */
    public function __construct(InvitationRepository $invitationRepository)
    {
        $this->invitationRepository = $invitationRepository;
    }

    /**
     * @param EventObserver $observer
     *
     * @return $this|void
     * @throws LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        $searchCriteria = $observer->getEvent()->getSearchCriteria();
        $customerEmail = $observer->getEvent()->getCustomerEmail();
        $invitationObject = $observer->getEvent()->getObject();

        $invitationObject->setInvitations(
            $this->invitationRepository->getReferralByEmail($searchCriteria, $customerEmail)
        );

        return $this;
    }
}
