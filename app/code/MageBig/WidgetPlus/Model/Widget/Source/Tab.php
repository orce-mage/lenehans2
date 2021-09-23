<?php

namespace MageBig\WidgetPlus\Model\Widget\Source;

class Tab implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $types = [
            ['value' => 'categories', 'label' => __('Categories')],
            ['value' => 'collections', 'label' => __('Collections')],
        ];

        return $types;
    }
}
