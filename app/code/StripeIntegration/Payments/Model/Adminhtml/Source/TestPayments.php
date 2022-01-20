<?php

namespace StripeIntegration\Payments\Model\Adminhtml\Source;

class TestPayments
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 0,
                'label' => __('Disabled (Use this in Live Mode)')
            ],
            [
                'value' => 1,
                'label' => __('Funds are never sent')
            ],
            [
                'value' => 2,
                'label' => __('The full amount is sent')
            ]
        ];
    }
}
