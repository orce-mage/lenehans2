<?php

namespace MageBig\WidgetPlus\Model\Widget\Source;

class Type implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $types = [
            ['value' => 'featured', 'label' => __('Featured Products')],
            ['value' => 'newcreated', 'label' => __('New Products')],
            ['value' => 'newupdated', 'label' => __('New Update')],
            ['value' => 'newfromdate', 'label' => __('New From Date')],
            ['value' => 'bestseller', 'label' => __('Best Selling')],
            ['value' => 'mostviewed', 'label' => __('Most Viewed')],
            ['value' => 'discount', 'label' => __('Discount Products')],
            ['value' => 'rating', 'label' => __('Top Ratting')],
            ['value' => 'related', 'label' => __('Related Products')],
            ['value' => 'upsell', 'label' => __('Up-Sell Products')],
            ['value' => 'random', 'label' => __('Random Products')]
        ];

        return $types;
    }
}
