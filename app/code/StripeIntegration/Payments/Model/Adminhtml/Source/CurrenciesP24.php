<?php

namespace StripeIntegration\Payments\Model\Adminhtml\Source;

class CurrenciesP24
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 0,
                'label' => __('All Currencies')
            ],
            [
                'value' => 'EUR,PLN',
                'label' => __('Euro and Polish Złoty Only')
            ],
        ];
    }
}
