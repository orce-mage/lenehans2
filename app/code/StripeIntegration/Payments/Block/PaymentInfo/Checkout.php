<?php

namespace StripeIntegration\Payments\Block\PaymentInfo;

use Magento\Framework\Phrase;
use StripeIntegration\Payments\Gateway\Response\FraudHandler;
use StripeIntegration\Payments\Helper\Logger;

class Checkout extends \Magento\Payment\Block\ConfigurableInfo
{
    protected $_template = 'paymentInfo/checkout.phtml';

    public $charges = null;
    public $totalCharges = 0;
    public $charge = null;
    public $cards = array();
    public $subscription = null;
    public $checkoutSession = null;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Gateway\ConfigInterface $config,
        \StripeIntegration\Payments\Helper\Generic $helper,
        \StripeIntegration\Payments\Helper\CheckoutSession $checkoutSessionHelper,
        \StripeIntegration\Payments\Helper\Subscriptions $subscriptions,
        \StripeIntegration\Payments\Model\Config $paymentsConfig,
        \StripeIntegration\Payments\Helper\Api $api,
        \Magento\Directory\Model\Country $country,
        \Magento\Payment\Model\Info $info,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $config, $data);

        $this->helper = $helper;
        $this->subscriptions = $subscriptions;
        $this->paymentsConfig = $paymentsConfig;
        $this->api = $api;
        $this->country = $country;
        $this->info = $info;
        $this->registry = $registry;
        $this->checkoutSessionHelper = $checkoutSessionHelper;
    }

    public function getFormattedAmount()
    {
        $checkoutSession = $this->getCheckoutSession();

        if (empty($checkoutSession->amount_total))
            return '';

        return $this->helper->formatStripePrice($checkoutSession->amount_total, $checkoutSession->currency);
    }

    public function getFormattedSubscriptionAmount()
    {
        $checkoutSession = $this->getCheckoutSession();

        if (empty($checkoutSession->subscription->plan))
            return '';

        return $this->subscriptions->formatInterval(
            $checkoutSession->subscription->plan->amount,
            $checkoutSession->subscription->plan->currency,
            $checkoutSession->subscription->plan->interval_count,
            $checkoutSession->subscription->plan->interval
        );
    }

    public function getPaymentMethod()
    {
        $checkoutSession = $this->getCheckoutSession();
        $paymentIntent = $this->getPaymentIntent();

        if (!empty($paymentIntent->payment_method->type))
            return $paymentIntent->payment_method;
        else if (!empty($checkoutSession->subscription->default_payment_method->type))
            return $checkoutSession->subscription->default_payment_method;

        return null;
    }

    public function getPaymentMethodCode()
    {
        $method = $this->getPaymentMethod();

        if (!empty($method->type))
            return $method->type;

        return null;
    }

    public function getPaymentMethodName($hideLast4 = false)
    {
        $paymentMethodCode = $this->getPaymentMethodCode();

        switch ($paymentMethodCode)
        {
            case "card":
                if ($hideLast4)
                    return $this->getCardBrandName();
                else
                {
                    $last4 = $this->getCardLast4();
                    return __("•••• %1", $last4);
                }
            default:
                return $this->checkoutSessionHelper->getPaymentMethodName($paymentMethodCode);
        }
    }

    public function getPaymentMethodIconUrl()
    {
        $paymentMethodCode = $this->getPaymentMethodCode();

        if (!$paymentMethodCode)
            return null;

        switch ($paymentMethodCode)
        {
            case "card":
                $brand = $this->getCardBrandCode();
                return $this->getCardIconURL($brand);
            default:
                try
                {
                    return $this->getViewFileUrl("StripeIntegration_Payments/img/methods/$paymentMethodCode.svg");
                }
                catch (\Exception $e)
                {
                    return $this->getViewFileUrl("StripeIntegration_Payments/img/methods/bank.svg");
                }
        }
    }

    public function getCardIconURL($brand)
    {
        try
        {
            return $this->getViewFileUrl("StripeIntegration_Payments/img/cards/$brand.svg");
        }
        catch (\Exception $e)
        {
            return $this->getViewFileUrl("StripeIntegration_Payments/img/cards/generic.svg");
        }
    }

    public function getCardBrandCode()
    {
        $card = $this->getCard();

        if (!empty($card->brand))
            return $card->brand;

        return null;
    }

    public function getCardBrandName()
    {
        $brand = $this->getCardBrandCode();
        return $this->helper->cardType($brand);
    }

    public function getCardLast4()
    {
        $card = $this->getCard();

        if (!empty($card->last4))
            return $card->last4;

        return null;
    }

    public function getCheckoutSession()
    {
        if ($this->checkoutSession)
            return $this->checkoutSession;

        $sessionId = $this->getInfo()->getAdditionalInformation("checkout_session_id");
        $checkoutSession = $this->paymentsConfig->getStripeClient()->checkout->sessions->retrieve($sessionId, [
            'expand' => [
                'payment_intent',
                'payment_intent.payment_method',
                'subscription',
                'subscription.default_payment_method',
                'subscription.latest_invoice.payment_intent'
            ]
        ]);

        return $this->checkoutSession = $checkoutSession;
    }

    public function getPaymentIntent()
    {
        $checkoutSession = $this->getCheckoutSession();

        if (!empty($checkoutSession->payment_intent))
            return $checkoutSession->payment_intent;

        if (!empty($checkoutSession->subscription->latest_invoice->payment_intent))
            return $checkoutSession->subscription->latest_invoice->payment_intent;

        return null;
    }

    public function getPaymentStatus()
    {
        $checkoutSession = $this->getCheckoutSession();
        $paymentIntent = $this->getPaymentIntent();

        if (empty($paymentIntent) && empty($checkoutSession->subscription))
            return "pending";

        return $this->getPaymentIntentStatus($paymentIntent);
    }

    public function getPaymentStatusName()
    {
        $status = $this->getPaymentStatus();
        return ucfirst(str_replace("_", " ", $status));
    }

    public function getSubscriptionStatus()
    {
        $checkoutSession = $this->getCheckoutSession();

        if (empty($checkoutSession->subscription))
            return null;

        return $checkoutSession->subscription->status;
    }

    public function getSubscriptionStatusName()
    {
        $checkoutSession = $this->getCheckoutSession();

        if (empty($checkoutSession->subscription))
            return null;

        if ($checkoutSession->subscription->status == "trialing")
            return __("Trial ends %1", date("j M", $checkoutSession->subscription->trial_end));

        return ucfirst($checkoutSession->subscription->status);
    }

    public function getPaymentIntentStatus($paymentIntent)
    {
        if (empty($paymentIntent->status))
            return null;

        switch ($paymentIntent->status)
        {
            case "requires_payment_method":
            case "requires_confirmation":
            case "requires_action":
            case "processing":
                return "pending";
            case "requires_capture":
                return "uncaptured";
            case "canceled":
                if (!empty($paymentIntent->charges->data[0]->failure_code))
                    return "failed";
                else
                    return "canceled";
            case "succeeded":
                if ($paymentIntent->charges->data[0]->refunded)
                    return "refunded";
                else if ($paymentIntent->charges->data[0]->amount_refunded > 0)
                    return "partial_refund";
                else
                    return "succeeded";
            default:
                return null;
        }
    }

    public function getSubscription()
    {
        $checkoutSession = $this->getCheckoutSession();

        if (!empty($checkoutSession->subscription))
            return $checkoutSession->subscription;

        return null;
    }

    public function getCard()
    {
        $method = $this->getPaymentMethod();

        if (!empty($method->card))
            return $method->card;

        return null;
    }

    public function getRiskLevelCode()
    {
        $charge = $this->getCharge();

        if (isset($charge->outcome->risk_level))
            return $charge->outcome->risk_level;

        return '';
    }

    public function getRiskScore()
    {
        $charge = $this->getCharge();

        if (isset($charge->outcome->risk_score))
            return $charge->outcome->risk_score;

        return null;
    }

    public function getRiskEvaluation()
    {
        $risk = $this->getRiskLevelCode();
        return ucfirst(str_replace("_", " ", $risk));
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

    public function getCharge()
    {
        $paymentIntent = $this->getPaymentIntent();

        if (!empty($paymentIntent->charges->data[0]))
            return $paymentIntent->charges->data[0];

        return null;
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

    public function getCustomerId()
    {
        $checkoutSession = $this->getCheckoutSession();

        if (isset($checkoutSession->customer) && !empty($checkoutSession->customer))
            return $checkoutSession->customer;

        return null;
    }

    public function getPaymentId()
    {
        $paymentIntent = $this->getPaymentIntent();

        if (isset($paymentIntent->id))
            return $paymentIntent->id;

        return null;
    }

    public function getMode()
    {
        $checkoutSession = $this->getCheckoutSession();

        if ($checkoutSession->livemode)
            return "";

        return "test/";
    }

    public function getTitle()
    {
        $info = $this->getInfo();
        return $info->getAdditionalInformation("method_title");
    }
}
