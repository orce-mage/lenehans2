<?php

namespace StripeIntegration\Payments\Model\Method;

use Magento\Framework\Exception\LocalizedException;

class Oxxo extends \StripeIntegration\Payments\Model\Method\Api\PaymentMethods
{
    const METHOD_CODE = 'stripe_payments_oxxo';

    protected $_code = self::METHOD_CODE;
    protected $_infoBlockType = 'StripeIntegration\Payments\Block\PaymentInfo\Oxxo';

    protected $type = 'oxxo';

    protected $_canRefund = false;
    protected $_canRefundInvoicePartial = false;

    public function createPaymentMethod()
    {
        $expires = $this->config->getConfigData('expires_after_days', 'oxxo');
        if (!is_numeric($expires))
            $expires = 3;

        $expires = max(1, $expires);
        $expires = min(7, $expires);

        return \Stripe\PaymentMethod::create([
            'type' => 'oxxo',
            'billing_details' => $this->getBillingDetails()
        ]);
    }

    public function adjustParamsForPaymentIntent(&$params, $order)
    {
        $expires = $this->config->getConfigData('expires_after_days', 'oxxo');
        if (!is_numeric($expires))
            $expires = 3;

        $expires = max(1, $expires);
        $expires = min(7, $expires);

        $params['payment_method_options'] = [
            'oxxo' => [
                'expires_after_days' => $expires
            ]
        ];
    }

    protected function onPaymentIntentConfirmed($paymentIntent, $order)
    {
        $payment = $order->getPayment();

        if (empty($paymentIntent->next_action->oxxo_display_details->number))
            throw new LocalizedException(__('Sorry, the OXXO voucher could not be created, please contact us for more help.'));

        $data = [
            "Voucher Number" => $paymentIntent->next_action->oxxo_display_details->number,
            "Valid Until" => date('m/d/Y', $paymentIntent->next_action->oxxo_display_details->expires_after)
        ];
        $payment->setAdditionalInformation('source_info', json_encode($data));
        $payment->setAdditionalInformation('voucher_link', $paymentIntent->next_action->oxxo_display_details->hosted_voucher_url);
    }
}
