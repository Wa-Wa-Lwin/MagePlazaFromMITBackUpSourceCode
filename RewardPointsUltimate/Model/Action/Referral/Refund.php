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

namespace Mageplaza\RewardPointsUltimate\Model\Action\Referral;

use Mageplaza\RewardPoints\Model\Action\Earning as CoreEarning;

/**
 * Class Refund
 * @package Mageplaza\RewardPointsUltimate\Model\Action\Referral
 */
class Refund extends CoreEarning
{
    const CODE = 'referral_refund';

    /**
     * @inheritdoc
     */
    public function getActionLabel()
    {
        return __('Referral Refund');
    }

    /**
     * @inheritdoc
     */
    public function getTitle($transaction)
    {
        return $this->getComment($transaction, 'Taken back points on referral friend refunded order #%1');
    }
}
