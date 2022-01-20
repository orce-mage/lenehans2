<?php

namespace StripeIntegration\Payments\Model\Method;

use Magento\Framework\Exception\LocalizedException;

class Fpx extends \StripeIntegration\Payments\Model\Method\Api\PaymentMethods
{
    const METHOD_CODE = 'stripe_payments_fpx';

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = self::METHOD_CODE;

    protected $type = 'fpx';

    public function createPaymentMethod()
    {
        $info = $this->getInfoInstance();

        return \Stripe\PaymentMethod::create([
            'type' => 'fpx',
            'fpx' => [
                'bank' => $info->getAdditionalInformation("bank")
            ],
            'billing_details' => $this->getBillingDetails()
        ]);
    }

    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);

        $info = $this->getInfoInstance();

        if (empty($data['additional_data']['bank']))
            throw new LocalizedException(__("Please select your bank before placing the order"));

        $info->setAdditionalInformation('bank', $data['additional_data']['bank']);

        return $this;
    }
}
