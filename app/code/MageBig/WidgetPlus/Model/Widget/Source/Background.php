<?php

namespace MageBig\WidgetPlus\Model\Widget\Source;

class Background implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $types = [
            ['value' => 0, 'label' => __('No')],
            ['value' => 1, 'label' => __('Background Image')],
            ['value' => 2, 'label' => __('Background Parallax')],
            ['value' => 3, 'label' => __('Background Video')]
        ];

        return $types;
    }
}
