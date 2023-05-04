<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  Mageplaza
 * @package   Mageplaza_RewardPointsUltimate
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Ui\DataProvider\Product\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Form\Field;

/**
 * Class SellProductByPoints
 * @package Mageplaza\RewardPointsUltimate\Ui\DataProvider\Product\Modifier
 */
class SellProductByPoints extends AbstractModifier
{
    const IS_ACTIVE = 'mp_rw_is_active';

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var $_meta
     */
    protected $_meta;

    /**
     * SellProductByPoints constructor.
     *
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @param array $meta
     *
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $this->_meta = $meta;
        $this->customizeEnableFieldField();

        return $this->_meta;
    }

    /**
     * @return $this
     */
    protected function customizeEnableFieldField()
    {
        $groupCode = $this->getGroupCodeByField($this->_meta, 'container_' . static::IS_ACTIVE);
        if (!$groupCode) {
            return $this;
        }

        // enable field
        $containerPath = $this->arrayManager->
        findPath('container_' . static::IS_ACTIVE, $this->_meta, null, 'children');
        $this->_meta   = $this->arrayManager->merge($containerPath, $this->_meta, [
            'children' => [
                static::IS_ACTIVE => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'dataScope'         => static::IS_ACTIVE,
                                'additionalClasses' => 'admin__field-x-small enab',
                                'component'         => 'Mageplaza_RewardPointsUltimate/js/sell-product-by-points',
                                'componentType'     => Field::NAME,
                                'prefer'            => 'toggle',
                                'valueMap'          => [
                                    'false' => '0',
                                    'true'  => '1',
                                ],
                                'exports'           => [
                                    'checked' => '${$.parentName}.' . static::IS_ACTIVE . ':enableSellProduct',
                                    '__disableTmpl' => ['checked' => false]
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        return $this;
    }
}
