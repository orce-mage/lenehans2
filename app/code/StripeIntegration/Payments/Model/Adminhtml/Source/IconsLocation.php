<?php

namespace StripeIntegration\Payments\Model\Adminhtml\Source;

class IconsLocation
{
    public function toOptionArray()
    {
        return [
            [
                'value' => "left",
                'label' => __('Left hand side of the title')
            ],
            [
                'value' => "right",
                'label' => __('Right hand side of the title')
            ]
        ];
    }
}
