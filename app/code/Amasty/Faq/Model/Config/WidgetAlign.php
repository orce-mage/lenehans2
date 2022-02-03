<?php

namespace Amasty\Faq\Model\Config;

use Magento\Framework\Option\ArrayInterface;

class WidgetAlign implements ArrayInterface
{
    const LEFT = 'am-widget-left';
    const CENTER = 'am-widget-center';
    const RIGHT = 'am-widget-right';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::LEFT, 'label' => __('Left')],
            ['value' => self::CENTER, 'label' => __('Center')],
            ['value' => self::RIGHT, 'label' => __('Right')]
        ];
    }
}
