<?php

namespace StripeIntegration\Payments\Model\Adminhtml\Source;

class SubscriptionsShipping
{
    public function toOptionArray()
    {
        return [
            [
                'value' => "add_to_subscription",
                'label' => __("Add to the subscription's invoice")
            ],
            [
                'value' => "charge_once",
                'label' => __("Charge only once")
            ],
        ];
    }
}
