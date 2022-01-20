<?php

namespace StripeIntegration\Payments\Model\Adminhtml\Source;

class Enabled
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 0,
                'label' => __('Disabled')
            ),
            array(
                'value' => 1,
                'label' => __('Enabled')
            )
        );
    }
}
