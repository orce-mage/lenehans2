<?php

namespace StripeIntegration\Payments\Model\Method;

use Magento\Framework\Exception\LocalizedException;

class PayPal extends \StripeIntegration\Payments\Model\Method\Api\PaymentMethods
{
    const METHOD_CODE = 'stripe_payments_paypal';

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = self::METHOD_CODE;

    protected $type = 'paypal';

    public function createPaymentMethod()
    {
        $info = $this->getInfoInstance();

        return \Stripe\PaymentMethod::create([
            'type' => 'paypal',
            'billing_details' => $this->getBillingDetails()
        ]);
    }

    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);

        $info = $this->getInfoInstance();

        // if (empty($data['additional_data']['bank']))
        //     throw new LocalizedException(__("Please select your bank before placing the order"));

        // $info->setAdditionalInformation('bank', $data['additional_data']['bank']);

        return $this;
    }

    public function adjustParamsForPaymentIntent(&$params, $order)
    {
        if ($this->config->isAuthorizeOnly("paypal"))
        {
            $params["capture_method"] = "manual";
        }
    }

    protected function getPaymentMethodOptions()
    {
        return ["preferred_locale" => $this->localeHelper->getLocale()];
    }

    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if ($amount > 0 && $this->helper->isAdmin())
        {
            $token = $payment->getTransactionId();
            if (empty($token))
                $token = $payment->getLastTransId(); // In case where the transaction was not created during the checkout, i.e. with a Stripe Webhook redirect

            if ($token)
            {
                // Capture an authorized payment from the admin area
                $this->helper->capture($token, $payment, $amount, false);
            }

            return $this;
        }

        return parent::capture($payment, $amount);
    }

    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if (!$this->config->initStripe())
            return false;

        if (parent::isAvailable($quote) === false) {
            return false;
        }

        if (!$this->isActive($quote ? $quote->getStoreId() : null)) {
            return false;
        }

        if (!$quote) {
            return false;
        }

        if (!in_array($quote->getQuoteCurrencyCode(), ["EUR", "GBP"]))
            return false;

        $allowedCountries = explode(",", $this->config->getConfigData("specificcountry", "paypal"));
        if ($quote->getIsVirtual())
            $address = $quote->getBillingAddress();
        else
            $address = $quote->getShippingAddress();

        if (empty($address) || empty($address->getCountryId()))
            return true;

        if (!in_array($address->getCountryId(), $allowedCountries))
            return false;

        return true;
    }
}
