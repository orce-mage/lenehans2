<?php
/**
 * Copyright © www.magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageBig\AjaxFilter\Model\Config\Source;

/**
 * Config category source
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class RatingTypes implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray($addEmpty = true)
    {
        return [
            ['value' => 'up', 'label' => __('Rating - Up')],
            ['value' => 'interval', 'label' => __('Rating - Interval')]
        ];
    }
}
