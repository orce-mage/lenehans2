<?php

namespace MageBig\WidgetPlus\Model\Widget\Source;

class Position implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $types = [
            ['value' => 0, 'label' => __('No')],
            ['value' => 1, 'label' => __('Left')],
            ['value' => 2, 'label' => __('Right')],
        ];

        return $types;
    }
}
