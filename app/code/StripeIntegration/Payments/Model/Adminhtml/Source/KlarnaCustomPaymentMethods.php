<?php

namespace StripeIntegration\Payments\Model\Adminhtml\Source;

class KlarnaCustomPaymentMethods
{
    public function toOptionArray()
    {
        return [
            [
                'value' => "pay_now",
                'label' => "Pay now"
            ],
            [
                'value' => "pay_later",
                'label' => "Pay later"
            ],
            [
                'value' => "pay_over_time",
                'label' => "Slice it (Global)"
            ],
            [
                'value' => "payin4",
                'label' => '- Slice it (US Only) > Pay in 4'
            ],
            [
                'value' => "installments",
                'label' => '- Slice it (US Only) > Installments'
            ],
        ];
    }
}
