<?php

namespace StripeIntegration\Payments\Model\Adminhtml\Source;

use Magento\Payment\Model\Method\AbstractMethod;

class SepaCreditBankAccount implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 0,
                'label' => __('Do not collect at the checkout')
            ],
            [
                'value' => 1,
                'label' => __('Collect at the checkout (optional)')
            ],
            [
                'value' => 2,
                'label' => __('Collect at the checkout (required)')
            ]
        ];
    }
}
