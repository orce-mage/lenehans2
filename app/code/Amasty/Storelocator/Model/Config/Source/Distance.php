<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model\Config\Source;

/**
 * Class Distance
 */
class Distance implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [
            [
                'value' => 'km',
                'label' => __('Kilometers'),
            ],
            [
                'value' => 'mi',
                'label' => __('Miles'),
            ],
            [
                'value' => 'choose',
                'label' => __('Allow User To Choose'),
            ],
        ];
    }
}
