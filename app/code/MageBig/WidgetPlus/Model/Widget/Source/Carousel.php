<?php

namespace MageBig\WidgetPlus\Model\Widget\Source;

class Carousel implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $types = [
            ['value' => 0, 'label' => __('No')],
            ['value' => 1, 'label' => __('Carousel')],
            ['value' => 2, 'label' => __('Slideshow')],
        ];

        return $types;
    }
}
