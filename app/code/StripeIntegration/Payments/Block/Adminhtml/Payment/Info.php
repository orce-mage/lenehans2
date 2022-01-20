<?php

namespace StripeIntegration\Payments\Block\Adminhtml\Payment;

use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;
use StripeIntegration\Payments\Gateway\Response\FraudHandler;
use StripeIntegration\Payments\Helper\Logger;

class Info extends ConfigurableInfo
{
    public $charges = null;
    public $totalCharges = 0;
    public $charge = null;
    public $cards = array();
    public $subscription = null;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Gateway\ConfigInterface $config,
        \StripeIntegration\Payments\Helper\Generic $helper,
        \StripeIntegration\Payments\Model\Config $paymentsConfig,
        \StripeIntegration\Payments\Helper\Api $api,
        \Magento\Directory\Model\Country $country,
        \Magento\Payment\Model\Info $info,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $config, $data);

        $this->helper = $helper;
        $this->paymentsConfig = $paymentsConfig;
        $this->api = $api;
        $this->country = $country;
        $this->info = $info;
        $this->registry = $registry;
    }

    public function appliesToPaymentMethod()
    {
        $method = $this->getMethod()->getMethod();

        switch ($method)
        {
            case "stripe_payments_checkout":
            case "stripe_payments_invoice":
                return false;
            default:
                return true;
        }

        return true;
    }

    public function shouldDisplayStripeSection()
    {
        $charge = $this->getCharge();
        $isCard = false;

        if (isset($charge->payment_method_details->type) && $charge->payment_method_details->type == "card")
            $isCard = true;

        return ($this->isStripeMethod() && $isCard);
    }

    public function getMethod()
    {
        $order = $this->registry->registry('current_order');
        return $order->getPayment();
    }

    public function getInfo()
    {
        $payment = $this->getMethod();
        $this->info->setData($payment->getData());
        return $this->info;
    }

    public function getSourceInfo()
    {
        $info = $this->getInfo()->getAdditionalInformation('source_info');

        if (empty($info))
            return null;

        $data = json_decode($info, true);

        return $data;
    }

    public function getBrand()
    {
        $card = $this->getCard();

        if (empty($card))
            return null;

        return $this->helper->cardType($card->brand);
    }

    public function getLast4()
    {
        $card = $this->getCard();

        if (empty($card))
            return null;

        return $card->last4;
    }

    public function getCard()
    {
        $charge = $this->getCharge();

        if (empty($charge))
            return null;

        if (!empty($charge->source))
        {
            if (isset($charge->source->object) && $charge->source->object == 'card')
                return $charge->source;

            if (isset($charge->source->type) && $charge->source->type == 'three_d_secure')
            {
                $cardId = $charge->source->three_d_secure->card;
                if (isset($this->cards[$cardId]))
                    return $this->cards[$cardId];

                $card = new \stdClass();
                $card = $charge->source->three_d_secure;
                $this->cards[$cardId] = $card;

                return $this->cards[$cardId];
            }
        }

        // Payment Methods API
        if (!empty($charge->payment_method_details->card))
            return $charge->payment_method_details->card;

        // Sources API
        if (!empty($charge->source->card))
            return $charge->source->card;

        return null;
    }

    public function getStreetCheck()
    {
        $card = $this->getCard();

        if (empty($card))
            return 'unchecked';

        // Payment Methods API
        if (!empty($card->checks->address_line1_check))
            return $card->checks->address_line1_check;

        // Sources API
        if (!empty($card->address_line1_check))
            return $card->address_line1_check;

        return 'unchecked';
    }

    public function getZipCheck()
    {
        $card = $this->getCard();

        if (empty($card))
            return 'unchecked';

        // Payment Methods API
        if (!empty($card->checks->address_postal_code_check))
            return $card->checks->address_postal_code_check;

        // Sources API
        if (!empty($card->address_zip_check))
            return $card->address_zip_check;

        return 'unchecked';

    }

    public function getCVCCheck()
    {
        $card = $this->getCard();

        if (empty($card))
            return 'unchecked';

        // Payment Methods API
        if (!empty($card->checks->cvc_check))
            return $card->checks->cvc_check;

        // Sources API
        if (!empty($card->cvc_check))
            return $card->cvc_check;

        return 'unchecked';
    }

    public function getRadarRisk()
    {
        $charge = $this->getCharge();

        if (isset($charge->outcome->risk_level))
            return $charge->outcome->risk_level;

        return 'Unchecked';
    }

    public function getChargeOutcome()
    {
        $charge = $this->getCharge();

        if (isset($charge->outcome->type))
            return $charge->outcome->type;

        return 'None';
    }

    public function isStripeMethod()
    {
        $method = $this->getMethod()->getMethod();

        if (strpos($method, "stripe_payments") !== 0 || $method == "stripe_payments_invoice")
            return false;

        return true;
    }

    public function nextCharge()
    {
        if (!$this->isStripeMethod())
            return null;

        if ($this->charges === null)
        {
            $this->charges = $this->getCharges();
            $this->totalCharges = count($this->charges);
        }

        $this->charge = array_pop($this->charges);
        return $this->charge;
    }

    public function getCharges()
    {
        $order = $this->registry->registry('current_order');

        $this->charges = [$this->getCharge()];

        return $this->charges;
    }

    public function getCharge()
    {
        if (!$this->isStripeMethod())
            return null;

        if (!empty($this->charge))
            return $this->charge;

        if ($this->charge === false)
            return false;

        return $this->charge = $this->retrieveCharge($this->getMethod()->getLastTransId());
    }

    public function retrieveCharge($chargeId)
    {
        try
        {
            $token = $this->helper->cleanToken($chargeId);

            return $this->api->retrieveCharge($token);
        }
        catch (\Exception $e)
        {
            return false;
        }
    }

    public function getCaptured()
    {
        $charge = $this->getCharge();

        if (isset($charge->captured) && $charge->captured == 1)
            return "Yes";

        return 'No';
    }

    public function getRefunded()
    {
        $charge = $this->getCharge();

        if (isset($charge->amount_refunded) && $charge->amount_refunded > 0)
            return $this->helper->formatStripePrice($charge->amount_refunded, $charge->currency);

        return 'No';
    }

    public function getCustomerId()
    {
        $charge = $this->getCharge();

        if (isset($charge->customer) && !empty($charge->customer))
            return $charge->customer;

        return null;
    }

    public function getPaymentId()
    {
        $charge = $this->getCharge();

        if (isset($charge->id))
            return $charge->id;

        return null;
    }

    public function getSubscription()
    {
        if (!$this->isStripeMethod())
            return null;

        if ($this->subscription)
            return $this->subscription;

        try
        {
            $token = $this->helper->cleanToken($this->getMethod()->getLastTransId());

            if (strpos($token, "sub_") === 0)
                return $this->subscription = \StripeIntegration\Payments\Model\Config::$stripeClient->subscriptions->retrieve($token, []);

            return null;
        }
        catch (\Exception $e)
        {
            return null;
        }
    }

    public function getMode()
    {
        $object = $this->getCharge();

        if (empty($object))
            $object = $this->getSubscription();

        if ($object->livemode)
            return "";

        return "test/";
    }

    public function getPaymentLocation()
    {
        $charge = $this->getCharge();

        if (isset($charge->metadata->{"Payment Location"}))
            return $charge->metadata->{"Payment Location"};

        return null;
    }

    public function getCardCountry()
    {
        $charge = $this->getCharge();

        if (isset($charge->payment_method_details->card->country))
            $country = $charge->payment_method_details->card->country;
        else if (isset($charge->source->country))
            $country = $charge->source->country;
        else if (isset($charge->source->card->country))
            $country = $charge->source->card->country;
        else
            return "Unknown";

        return $this->country->load($country)->getName();
    }

    public function getSourceType()
    {
        $charge = $this->getCharge();

        if (!isset($charge->source->type))
            return null;

        return ucwords(str_replace("_", " ", $charge->source->type));
    }

    public function getTitle()
    {
        $info = $this->getInfo();

        if ($info->getAdditionalInformation('is_prapi'))
        {
            $type = $info->getAdditionalInformation("prapi_title");
            if ($type)
                return __("%1 via Stripe", $type);

            return __("Digital Wallet Payment via Stripe");
        }

        return $this->getMethod()->getTitle();
    }
}
