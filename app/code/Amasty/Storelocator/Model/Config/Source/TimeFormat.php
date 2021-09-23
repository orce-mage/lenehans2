<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model\Config\Source;

/**
 * Class TimeFormat
 */
class TimeFormat implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => '0',
                'label' => __('24h'),
            ],
            [
                'value' => '1',
                'label' => __('12h'),
            ]
        ];
    }
}
