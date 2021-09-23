<?php

namespace MageBig\WidgetPlus\Model\Widget\Source;

class TabMode implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        $types = [
            ['value' => 'newcreated', 'label' => __('New Create')],
            ['value' => 'newupdated', 'label' => __('New Update')],
            ['value' => 'newfromdate', 'label' => __('New From Date')],
            ['value' => 'bestseller', 'label' => __('Best Selling')],
            ['value' => 'mostviewed', 'label' => __('Most Viewed')],
            ['value' => 'discount', 'label' => __('Discount')],
            ['value' => 'rating', 'label' => __('Top Ratting')],
        ];

        return $types;
    }
}
