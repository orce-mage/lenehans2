<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model\Config\Source;

/**
 * Class RadiusType
 */
class RadiusType implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'select',
                'label' => __('Dropdown'),
            ],
            [
                'value' => 'range',
                'label' => __('Slider'),
            ]
        ];
    }
}
