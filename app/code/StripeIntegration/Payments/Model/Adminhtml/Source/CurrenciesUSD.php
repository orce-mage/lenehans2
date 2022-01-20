<?php

namespace StripeIntegration\Payments\Model\Adminhtml\Source;

class CurrenciesUSD
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 0,
                'label' => __('All Currencies')
            ],
            [
                'value' => 'USD',
                'label' => __('USD Only')
            ],
        ];
    }
}
