<?php

namespace StripeIntegration\Payments\Model\Adminhtml\Source;

class CcAutoDetect
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 0,
                'label' => __('Disabled')
            ],
            [
                'value' => 1,
                'label' => __('Show all accepted card types')
            ],
            [
                'value' => 2,
                'label' => __('Show only the detected card type')
            ],
        ];
    }
}
