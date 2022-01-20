<?php

namespace StripeIntegration\Payments\Model\Adminhtml\Source;

class CurrenciesEU
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 0,
                'label' => __('All Currencies')
            ],
            [
                'value' => 'EUR',
                'label' => __('Euro Only')
            ],
        ];
    }
}
