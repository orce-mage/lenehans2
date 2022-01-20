<?php

namespace StripeIntegration\Payments\Model;

use Magento\Framework\Validator\Exception;
use Magento\Framework\Exception\LocalizedException;
use StripeIntegration\Payments\Helper\Logger;

class PaymentIntent extends \Magento\Framework\Model\AbstractModel
{
    public $paymentIntent = null;
    public $paymentIntentsCache = [];
    public $quote = null; // Overwrites default quote
    public $order = null;
    public $capture = null; // Overwrites default capture method
    public $savedCard = null;

    const SUCCEEDED = "succeeded";
    const AUTHORIZED = "requires_capture";
    const CAPTURE_METHOD_MANUAL = "manual";
    const CAPTURE_METHOD_AUTOMATIC = "automatic";
    const REQUIRES_ACTION = "requires_action";
    const CANCELED = "canceled";
    const AUTHENTICATION_FAILURE = "payment_intent_authentication_failure";

    public function __construct(
        \StripeIntegration\Payments\Helper\Generic $helper,
        \StripeIntegration\Payments\Helper\Compare $compare,
        \StripeIntegration\Payments\Helper\Rollback $rollback,
        \StripeIntegration\Payments\Helper\SetupIntent $setupIntent,
        \StripeIntegration\Payments\Helper\Subscriptions $subscriptionsHelper,
        \StripeIntegration\Payments\Helper\Address $addressHelper,
        \StripeIntegration\Payments\Model\Config $config,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Session\Generic $session,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
        )
    {
        $this->helper = $helper;
        $this->compare = $compare;
        $this->rollback = $rollback;
        $this->setupIntent = $setupIntent;
        $this->subscriptionsHelper = $subscriptionsHelper;
        $this->addressHelper = $addressHelper;
        $this->cache = $context->getCacheManager();
        $this->config = $config;
        $this->customer = $helper->getCustomerModel();
        $this->quoteFactory = $quoteFactory;
        $this->quoteRepository = $quoteRepository;
        $this->addressFactory = $addressFactory;
        $this->session = $session;
        $this->checkoutHelper = $checkoutHelper;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init('StripeIntegration\Payments\Model\ResourceModel\PaymentIntent');
    }

    // Same as loadFromCache, but does not destroy it if it is invalid
    public function preloadFromCache($quote, $order)
    {
        if (empty($quote))
            return false;

        $quoteId = $quote->getId();

        if (empty($quoteId))
            $quoteId = $quote->getQuoteId(); // Admin order quotes

        if (empty($quoteId))
            return false;

        $key = 'payment_intent_' . $quoteId;
        if ($this->helper->isAPIRequest())
            $paymentIntentId = $this->cache->load($key);
        else
            $paymentIntentId = $this->session->getData($key);

        if (!empty($paymentIntentId) && strpos($paymentIntentId, "pi_") === 0)
        {
            if (isset($this->paymentIntentsCache[$paymentIntentId]) && $this->paymentIntentsCache[$paymentIntentId] instanceof \Stripe\PaymentIntent)
                $this->paymentIntent = $this->paymentIntentsCache[$paymentIntentId];
            else
            {
                try
                {
                    $this->loadPaymentIntent($paymentIntentId, $order);
                    $this->updateCache($quoteId);
                }
                catch (\Exception $e)
                {
                    // If the Stripe API keys or the Mode was changed mid-checkout-session, we may get here
                    return null;
                }
            }
        }
        else
            return false;

        return $quoteId;
    }

    // If we already created any payment intents for this quote, load them
    public function loadFromCache($params, $quote, $order)
    {
        $quoteId = $this->preloadFromCache($quote, $order);
        if (!$quoteId)
            return null;

        if ($this->isInvalid($params, $quote, $order) || $this->hasPaymentActionChanged())
        {
            $this->destroy($quoteId, true);
            return null;
        }

        return $this->paymentIntent;
    }

    public function loadFromIdentifier($identifier, $params, $quote, $order)
    {
        if (empty($identifier))
            return null;

        $key = 'payment_intent_' . $identifier;
        if ($this->helper->isAPIRequest())
            $paymentIntentId = $this->cache->load($key);
        else
            $paymentIntentId = $this->session->getData($key);

        if (!empty($paymentIntentId) && strpos($paymentIntentId, "pi_") === 0)
        {
            if (isset($this->paymentIntentsCache[$paymentIntentId]) && $this->paymentIntentsCache[$paymentIntentId] instanceof \Stripe\PaymentIntent)
                $paymentIntent = $this->paymentIntentsCache[$paymentIntentId];
            else
            {
                $this->loadPaymentIntent($paymentIntentId, $order);
                $this->updateCache($identifier, $paymentIntent);
            }
        }
        else
            return null;

        if ($this->isInvalid($params, $quote, $order, $paymentIntent) || $this->hasPaymentActionChanged($paymentIntent))
        {
            $this->destroy($identifier, true, $paymentIntent);
            return null;
        }

        return $paymentIntent;
    }

    public function loadFromPayment($payment, $order = null)
    {
        if (empty($payment))
            throw new LocalizedException(__("Unhandled attempt to place multi-shipping order without a payment object"));

        $paymentIntentId = $payment->getAdditionalInformation("payment_intent_id");

        if (empty($paymentIntentId))
        {
            $this->paymentIntent = null;
            return null;
        }

        try
        {
            $this->loadPaymentIntent($paymentIntentId, $order);
            $this->updateCache($paymentIntentId); // We sent a $paymentIntentId and not a $quoteId intentionally!
            return $this->paymentIntent;
        }
        catch (\Exception $e)
        {
            $this->paymentIntent = null;
            return null;
        }
    }

    public function loadPaymentIntent($paymentIntentId, $order = null)
    {
        $this->paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

        if (!empty($this->paymentIntent->customer))
        {
            $customer = $this->helper->getCustomerModelByStripeId($this->paymentIntent->customer);
            if ($customer)
                $this->customer = $customer;

            if (!$this->customer->getStripeId())
                $this->customer->createStripeCustomer($order, ["id" => $this->paymentIntent->customer]);
        }

    }

    protected function hasPaymentActionChanged($paymentIntent = null)
    {
        if (!$paymentIntent)
            $paymentIntent = $this->paymentIntent;

        $captureMethod = $this->getCaptureMethod();
        return ($captureMethod != $paymentIntent->capture_method);
    }

    public function create($params, $quote, $order = null)
    {
        if (empty($params['amount']) || $params['amount'] <= 0)
            return null;

        if ($this->helper->isMultiShipping() && $order)
            $this->loadFromPayment($order->getPayment(), $order);
        else
            $this->loadFromCache($params, $quote, $order);

        if (!$this->paymentIntent)
        {
            $this->paymentIntent = \Stripe\PaymentIntent::create($params);
            $this->updateCache($quote->getId());

            if ($order)
            {
                $payment = $order->getPayment();
                $payment->setAdditionalInformation("payment_intent_id", $this->paymentIntent->id);
                $payment->setAdditionalInformation("payment_intent_client_secret", $this->paymentIntent->client_secret);
            }
        }
        else if ($this->differentFrom($params, $quote, $order))
        {
            $this->updateFrom($params, $quote, $order);
        }

        return $this->paymentIntent;
    }

    protected function updateCache($quoteId, $paymentIntent = null)
    {
        $key = 'payment_intent_' . $quoteId;
        if (empty($paymentIntent))
            $paymentIntent = $this->paymentIntent;

        $data = $paymentIntent->id;

        if ($this->helper->isAPIRequest())
        {
            $tags = ['stripe_payments_payment_intents'];
            $lifetime = 12 * 60 * 60; // 12 hours
            $this->cache->save($data, $key, $tags, $lifetime);
        }
        else
            $this->session->setData($key, $data);

        $this->paymentIntentsCache[$paymentIntent->id] = $paymentIntent;
    }

    public function getPaymentMethodDetails($quote, $paymentMethodId, $order = null)
    {
        $params = ['payment_method' => $paymentMethodId];
        $paymentMethod = null;

        if ($this->helper->isAdmin())
        {
            $paymentMethod = $this->config->getStripeClient()->paymentMethods->retrieve($paymentMethodId, []);
            if (!empty($paymentMethod->customer))
                $this->customer = $this->helper->getCustomerModelByStripeId($paymentMethod->customer);
        }

        if ($order && $order->getPayment()->getAdditionalInformation("save_card"))
            $save = true;
        else if ($this->config->alwaysSaveCards())
            $save = true;
        else
            $save = false;

        if ($save)
        {
            if (!$this->customer->getStripeId())
                $this->customer->createStripeCustomerIfNotExists(true, $order);

            if ($this->helper->isAdmin() && $this->config->isMOTOExemptionsEnabled())
                $params['save_payment_method'] = true;
            else if ($this->helper->isMultiShipping())
            {
                if ($this->savedCard != $paymentMethodId)
                {
                    $params['save_payment_method'] = true;
                    $this->savedCard = $paymentMethodId;
                }
            }
            else
            {
                if ($this->config->isAuthorizeOnly() && $this->config->retryWithSavedCard())
                    $params['setup_future_usage'] = "off_session";
                else
                    $params['setup_future_usage'] = "on_session";
            }
        }

        if ($this->customer->getStripeId())
            $params["customer"] = $this->customer->getStripeId();

        if ($this->config->isInstallmentPlansEnabled())
            $params["payment_method_options"]["card"]["installments"]["enabled"] = true;

        return $params;
    }

    public function getParamsFrom($quote, $order = null, $paymentMethodId = null)
    {
        if ($order)
        {
            $payment = $order->getPayment();

            if ($this->config->useStoreCurrency($order))
            {
                $amount = $order->getGrandTotal();
                $currency = $order->getOrderCurrencyCode();
            }
            else
            {
                $amount = $order->getBaseGrandTotal();
                $currency = $order->getBaseCurrencyCode();
            }
        }
        else
        {
            if ($this->config->useStoreCurrency($order))
            {
                $amount = $quote->getGrandTotal();
                $currency = $quote->getQuoteCurrencyCode();
            }
            else
            {
                $amount = $quote->getBaseGrandTotal();
                $currency = $quote->getBaseCurrencyCode();
            }
        }

        $cents = 100;
        if ($this->helper->isZeroDecimal($currency))
            $cents = 1;

        $params['amount'] = round($amount * $cents);
        $params['currency'] = strtolower($currency);
        $params['capture_method'] = $this->getCaptureMethod();
        $params["payment_method_types"] = ["card"]; // For now
        $params['confirmation_method'] = 'manual';

        if ($paymentMethodId)
        {
            $extraParams = $this->getPaymentMethodDetails($quote, $paymentMethodId, $order);
            $params = array_merge($params, $extraParams);
        }

        if ($order)
        {
            $params["description"] = $this->helper->getOrderDescription($order);
            $params["metadata"] = $this->config->getMetadata($order);
        }

        $params['amount'] = $this->adjustAmountForSubscriptions($params['amount'], $params['currency'], $quote, $order);

        $statementDescriptor = $this->config->getStatementDescriptor();
        if (!empty($statementDescriptor))
            $params["statement_descriptor"] = $statementDescriptor;
        else
            unset($params['statement_descriptor']);

        $shipping = $this->getShippingAddressFrom($quote, $order);
        if ($shipping)
            $params['shipping'] = $shipping;
        else
            unset($params['shipping']);

        if ($order)
            $customerEmail = $order->getCustomerEmail();
        else
            $customerEmail = $quote->getCustomerEmail();

        if ($customerEmail && $this->config->isReceiptEmailsEnabled())
            $params["receipt_email"] = $customerEmail;

        if ($this->config->isLevel3DataEnabled())
        {
            $level3Data = $this->helper->getLevel3DataFrom($order, $this->config->useStoreCurrency($order));
            if ($level3Data)
                $params["level3"] = $level3Data;
        }

        return $params;
    }

    // Adds initial fees, or removes item amounts if there is a trial set
    protected function adjustAmountForSubscriptions($amount, $currency, $quote, $order = null)
    {
        $cents = 100;
        if ($this->helper->isZeroDecimal($currency))
            $cents = 1;

        if ($order)
            $data = $this->subscriptionsHelper->createSubscriptions($order, true);
        else
            $data = $this->subscriptionsHelper->createSubscriptions($quote, true);

        if (!empty($data['error']))
            throw new LocalizedException($data['error']);

        return round((($amount/$cents) - $data['subscriptionsTotal']) * $cents);
    }

    // Checks if the payment methods in the parameter are the same with the payment methods on $this->paymentMethods
    protected function samePaymentMethods($methods)
    {
        $currentMethods = $this->paymentIntent->payment_method_types;
        return (empty(array_diff($methods, $currentMethods)) &&
            empty(array_diff($currentMethods, $methods)));
    }

    public function getClientSecret($paymentIntent = null)
    {
        if (empty($paymentIntent))
            $paymentIntent = $this->paymentIntent;

        if (empty($paymentIntent))
            return null;

        if (!$this->config->isEnabled())
            return null;

        return $paymentIntent->client_secret;
    }

    public function getStatus()
    {
        if (empty($this->paymentIntent))
            return null;

        if (!$this->config->isEnabled())
            return null;

        return $this->paymentIntent->status;
    }

    public function getPaymentIntentID()
    {
        if (empty($this->paymentIntent))
            return null;

        return $this->paymentIntent->id;
    }

    protected function getQuote($quoteId = null)
    {
        // Capturing an expired authorization
        if ($this->quote)
            return $this->quote;

        return $this->helper->getQuote($quoteId);
    }

    public function isInvalid($params, $quote, $order, $paymentIntent = null)
    {
        if ($params['amount'] <= 0)
            return true;

        if (!$paymentIntent)
        {
            if (empty($this->paymentIntent))
                return true;

            $paymentIntent = $this->paymentIntent;
        }

        if ($paymentIntent->status == $this::CANCELED)
            return true;
        else if ($paymentIntent->amount != $params['amount'])
            return true;

        if (!$this->customer->getStripeId())
            $this->customer->createStripeCustomerIfNotExists(true, $order); // This is a troubled line. We shouldn't create a customer if there is no need.

        $customerId = $this->customer->getStripeId();
        if (!empty($paymentIntent->customer) && $paymentIntent->customer != $customerId)
            return true;

        return false;
    }

    public function updateFrom($params, $quote, $order)
    {
        if (empty($quote))
            return $this;

        if (!$this->config->isEnabled())
            return $this;

        if (!$this->paymentIntent)
            return $this;

        if ($this->isSuccessfulStatus())
            return $this;

        if ($this->differentFrom($params, $quote, $order))
        {
            $paymentIntentParams = $this->getFilteredParamsForUpdate($params);

            foreach ($paymentIntentParams as $key => $value)
                $this->paymentIntent->{$key} = $value;

            // We can only set the customer, we cannot change it
            if (!empty($params["customer"]) && empty($this->paymentIntent->customer))
                $this->paymentIntent->customer = $params["customer"];

            $this->updatePaymentIntent($quote);
        }
    }

    // Performs an API update of the PI
    public function updatePaymentIntent($quote)
    {
        $this->paymentIntent->save();
        $this->updateCache($quote->getId());
    }

    public function destroy($quoteId, $cancelPaymentIntent = false, $paymentIntent = null)
    {
        if (!$paymentIntent)
        {
            $paymentIntent = $this->paymentIntent;
            $this->paymentIntent = null;
        }

        $key = 'payment_intent_' . $quoteId;
        if ($this->helper->isAPIRequest())
            $this->cache->remove($key);
        else
            $this->session->unsetData($key);

        if ($paymentIntent && $cancelPaymentIntent && $paymentIntent->status != $this::CANCELED)
            $paymentIntent->cancel();

        if (isset($this->paymentIntentsCache[$key]))
            unset($this->paymentIntentsCache[$key]);
    }

    // At the final place order step, if the amount and currency has not changed, Magento will not call
    // the quote observer. But the customer may have changed the shipping address, in which case a
    // payment intent update is needed. We want to unset the amount and currency in this case because
    // the Stripe API will throw an error, because the PI has already been authorized at the checkout
    protected function getFilteredParamsForUpdate($params)
    {
        $newParams = [];
        $allowedParams = ["amount", "currency", "description", "metadata", "shipping", "level3", "on_behalf_of"];

        foreach ($allowedParams as $key)
        {
            if (isset($params[$key]))
                $newParams[$key] = $params[$key];
        }

        if ($newParams["amount"] == $this->paymentIntent->amount)
            unset($newParams["amount"]);

        if ($newParams["currency"] == $this->paymentIntent->currency)
            unset($newParams["currency"]);

        if (empty($newParams["shipping"]))
            $newParams["shipping"] = null; // Unsets it through the API

        return $newParams;
    }

    public function differentFrom($params, $quote, $order = null)
    {
        $expectedValues = [
            "amount" => $params["amount"],
            "currency" => $params["currency"]
        ];

        foreach (["metadata", "description", "shipping", "level3"] as $key)
        {
            if (empty($params[$key]))
                $expectedValues[$key] = "unset";
            else
                $expectedValues[$key] = $params[$key];
        }

        return $this->compare->isDifferent($this->paymentIntent, $expectedValues);
    }

    public function isLevel3DataDifferent($params)
    {
        if (empty($this->paymentIntent->level3) && empty($params['level3']))
            return false;

        if (empty($this->paymentIntent->level3) && !empty($params['level3']))
            return true;

        if (!empty($this->paymentIntent->level3) && empty($params['level3']))
            return true;

        $comparisonKeys1 = ["merchant_reference", "customer_reference", "shipping_address_zip", "shipping_from_zip", "shipping_amount"];
        $comparisonKeys2 = ["product_code", "product_description", "unit_cost", "quantity", "tax_amount", "discount_amount"];

        foreach ($comparisonKeys1 as $key)
        {
            if (empty($params['level3'][$key]) && !empty($this->paymentIntent->level3->{$key}))
                return true;

            if (!empty($params['level3'][$key]) && empty($this->paymentIntent->level3->{$key}))
                return true;

            if (empty($params['level3'][$key]) && empty($this->paymentIntent->level3->{$key}))
                continue;

            if ($this->paymentIntent->level3->{$key} != $params['level3'][$key])
                return true;
        }

        if (empty($this->paymentIntent->level3->line_items) && !empty($params['level3']['line_items']))
            return true;

        if (!empty($this->paymentIntent->level3->line_items) && empty($params['level3']['line_items']))
            return true;

        if (empty($this->paymentIntent->level3->line_items) && empty($params['level3']['line_items']))
            return false;

        if (count($this->paymentIntent->level3->line_items) != count($params['level3']['line_items']))
            return true;

        foreach ($this->paymentIntent->level3->line_items as $key => $lineItem)
        {
            $paramItem = $params['level3']['line_items'][$key];
            foreach ($comparisonKeys2 as $key)
            {

                if (empty($paramItem[$key]) && !empty($lineItem->{$key}))
                    return true;

                if (!empty($paramItem[$key]) && empty($lineItem->{$key}))
                    return true;

                if (empty($paramItem[$key]) && empty($lineItem->{$key}))
                    continue;

                if ($lineItem->{$key} != $paramItem[$key])
                    return true;
            }
        }

        return false;
    }

    public function isMetadataDifferent($params)
    {
        if (empty($params["metadata"]))
            return false;

        foreach ($params["metadata"] as $key => $value)
        {
            if ($this->paymentIntent->metadata[$key] != $value)
                return true;
        }

        return false;
    }

    public function isDescriptionDifferent($params)
    {
        if (empty($params["description"]) && empty($this->paymentIntent->description))
            return false;

        if (empty($params["description"]))
            return true;

        if (empty($this->paymentIntent->description))
            return true;

        return ($params["description"] != $this->paymentIntent->description);
    }

    public function isAddressDifferentFrom($quote, $order = null)
    {
        $newShipping = $this->getShippingAddressFrom($quote, $order);

        // If both are empty, they are the same
        if (empty($this->paymentIntent->shipping) && empty($newShipping))
            return false;

        // If one of them is empty, they are different
        if (empty($this->paymentIntent->shipping) && !empty($newShipping))
            return true;

        if (!empty($this->paymentIntent->shipping) && empty($newShipping))
            return true;

        $comparisonKeys1 = ["name", "phone"];
        $comparisonKeys2 = ["city", "country", "line1", "line2", "postal_code", "state"];

        foreach ($comparisonKeys1 as $key) {
            if ($this->paymentIntent->shipping->{$key} != $newShipping[$key])
                return true;
        }

        foreach ($comparisonKeys2 as $key) {
            if ($this->paymentIntent->shipping->address->{$key} != $newShipping["address"][$key])
                return true;
        }

        return false;
    }

    public function getShippingAddressFrom($quote, $order = null)
    {
        if ($order)
            $obj = $order;
        else
            $obj = $quote;

        if (empty($obj) || $obj->getIsVirtual())
            return null;

        $address = $obj->getShippingAddress();

        if (empty($address))
            return null;

        // This is the case where we only have the quote
        if (empty($address->getFirstname()))
            $address = $this->addressFactory->create()->load($address->getAddressId());

        if (empty($address->getFirstname()))
            return null;

        return $this->addressHelper->getStripeShippingAddressFromMagentoAddress($address);
    }

    public function isSuccessfulStatus($paymentIntent = null)
    {
        if (empty($paymentIntent))
            $paymentIntent = $this->paymentIntent;

        return ($paymentIntent->status == PaymentIntent::SUCCEEDED ||
            $paymentIntent->status == PaymentIntent::AUTHORIZED);
    }

    public function refreshCache($quoteId, $order = null)
    {
        if (!$this->paymentIntent)
            return;

        $this->loadPaymentIntent($this->paymentIntent->id, $order);
        $this->updateCache($quoteId);
    }

    public function getCaptureMethod()
    {
        // Overwrite for when capturing an expired authorization
        if ($this->capture)
            return $this->capture;

        if ($this->config->isAuthorizeOnly())
            return PaymentIntent::CAPTURE_METHOD_MANUAL;

        return PaymentIntent::CAPTURE_METHOD_AUTOMATIC;
    }

    public function requiresAction($paymentIntent = null)
    {
        if (empty($paymentIntent))
            $paymentIntent = $this->paymentIntent;

        return (
            !empty($paymentIntent->status) &&
            $paymentIntent->status == $this::REQUIRES_ACTION
        );
    }

    public function triggerAuthentication($piSecrets)
    {
        if (count($piSecrets) > 0)
        {
            if (!$this->helper->isMultiShipping())
                $this->rollback->run();

            if ($this->helper->isAdmin())
                $this->helper->dieWithError(__("This card cannot be used because it requires a 3D Secure authentication by the customer. Your Stripe account needs to be MOTO enabled to use 3D Secure cards from the admin area."));

            // Front-end checkout case, this will trigger the 3DS modal.
            $this->helper->dieWithError("Authentication Required: " . implode(",", $piSecrets));
        }
    }

    public function redirectToMultiShippingAuthorizationPage($payment, $paymentIntentId)
    {
        $this->session->setAuthorizationRedirect("stripe/authorization/multishipping");
        $payment->setIsTransactionPending(true);
        $payment->setIsTransactionClosed(0);
        $payment->setIsFraudDetected(false);
        $payment->setAdditionalInformation('authentication_pending', true);
        $payment->setTransactionId($paymentIntentId);
        $payment->setLastTransId($paymentIntentId);

        return $this->paymentIntent;
    }

    public function getInstallmentPlan($payment)
    {
        if (!$this->paymentIntent)
            return null;

        $selectedPlan = $payment->getAdditionalInformation("selected_plan");

        if (!is_numeric($selectedPlan) || $selectedPlan < 0)
            return null;

        if (!isset($this->paymentIntent->payment_method_options->card->installments->available_plans[$selectedPlan]))
            return null;

        $plan = $this->paymentIntent->payment_method_options->card->installments->available_plans[$selectedPlan];

        return [
            "type" => $plan->type,
            "interval" => $plan->interval,
            "count" => $plan->count
        ];
    }

    public function getConfirmParams($order)
    {
        $confirmParams = [];

        if ($this->helper->isAdmin() && $this->config->isMOTOExemptionsEnabled())
            $confirmParams["payment_method_options"]["card"]["moto"] = "true";

        if ($installmentPlan = $this->getInstallmentPlan($order->getPayment()))
            $confirmParams["payment_method_options"]["card"]["installments"]["plan"] = $installmentPlan;

        // This should only trigger with Apple Pay and with Installments where we only had the quote at the PI creation time
        if (empty($this->paymentIntent->payment_method) || $this->helper->isMultiShipping())
            $confirmParams["payment_method"] = $order->getPayment()->getAdditionalInformation("token");

        return $confirmParams;
    }

    public function attachPaymentMethodToCustomer($paymentMethodId, $order)
    {
        $this->customer->createStripeCustomerIfNotExists(true, $order);
        $paymentMethod = \Stripe\PaymentMethod::retrieve($paymentMethodId);

        if (empty($paymentMethod->customer))
        {
            $this->config->getStripeClient()->paymentMethods->attach($paymentMethodId, [ 'customer' => $this->customer->getStripeId() ]);
        }
        else if ($paymentMethod->customer != $this->customer->getStripeId())
        {
            $this->config->getStripeClient()->paymentMethods->detach($paymentMethodId, []);
            $this->config->getStripeClient()->paymentMethods->attach($paymentMethodId, [ 'customer' => $this->customer->getStripeId() ]);
        }
    }

    public function confirmAndAssociateWithOrder($order, $payment)
    {
        if ($payment->getAdditionalInformation("is_recurring_subscription"))
            return null;

        if (!$payment->getAdditionalInformation("token"))
            $this->helper->dieWithError("An error occurred while tokenizing the card details.");

        // Whether the payment attempt succeeds or fails, we want to have a new SetupIntent at the next payment attempt
        $this->setupIntent->destroy();

        $hasSubscriptions = $this->helper->hasSubscriptionsIn($order->getAllItems());

        $quote = $order->getQuote();

        if (empty($quote) || !is_numeric($quote->getGrandTotal()))
            $this->quote = $quote = $this->getQuote($order->getQuoteId());

        if (empty($quote) || !is_numeric($quote->getGrandTotal()))
        {
            if ($this->helper->isAdmin())
                $this->helper->dieWithError(__("Sorry, this invoice cannot be manually captured."));
            else
                $this->helper->dieWithError(__("Invalid quote used for Payment Intent"));
        }

        // Save the quote so that we don't lose the reserved order ID in the case of a payment error
        $quote->save();

        // We mainly call this for 3DS orders, so that the customer object is preloaded on the 2nd payment attempt.
        // Loading the customer is needed if a database transaction rolled back the stripe_customers table at an Authentication Required error.
        $this->preloadFromCache($quote, $order);

        // Create subscriptions if any
        $params = $this->getParamsFrom($quote, $order, $payment->getAdditionalInformation("token"));
        $piSecrets = $this->createSubscriptionsFor($order, $params);

        if (!$this->paymentIntent // When capturing expired authorizations, we set $this->paymentIntent before confirming it with the order
            || $this->helper->isMultiShipping()
            || $this->isInvalid($params, $quote, $order, $this->paymentIntent)
            || $this->differentFrom($params, $quote, $order)
            )
        {
            $this->paymentIntent = $this->create($params, $quote, $order); // Load or create the Payment Intent
        }


        // If this is a subscriptions only order, no payment intent will be created
        if (!$this->paymentIntent && $hasSubscriptions)
        {
            if (count($piSecrets) > 0 && $this->helper->isMultiShipping())
            {
                reset($piSecrets);
                $paymentIntentId = key($piSecrets); // count($piSecrets) should always be 1 here
                return $this->redirectToMultiShippingAuthorizationPage($payment, $paymentIntentId);
            }

            $this->triggerAuthentication($piSecrets);

            // Let's save the Stripe customer ID on the order's payment in case the customer registers after placing the order
            if (!empty($this->subscriptionData['stripeCustomerId']))
                $payment->setAdditionalInformation("customer_stripe_id", $this->subscriptionData['stripeCustomerId']);

            $payment->setLastTransId("cannot_capture_subscriptions");

            return null;
        }

        if (!$this->paymentIntent)
            throw new LocalizedException(__("Unable to create payment intent"));

        if (!$this->isSuccessfulStatus())
        {
            $confirmParams = $this->getConfirmParams($order);

            if (!empty($this->paymentIntent->setup_future_usage) && $this->paymentIntent->setup_future_usage)
                $this->deleteSavedCard($payment->getAdditionalInformation("token"));

            try
            {
                $this->updateData($this->paymentIntent->id, $order);
                $this->paymentIntent->confirm($confirmParams);
                $this->prepareRollback();
            }
            catch (\Exception $e)
            {
                $this->prepareRollback();
                return $this->helper->maskException($e);
            }

            if ($this->requiresAction())
                $piSecrets[] = $this->getClientSecret();

            if (count($piSecrets) > 0 && $this->helper->isMultiShipping())
            {
                $order->setCanSendNewEmailFlag(false);
                return $this->redirectToMultiShippingAuthorizationPage($payment, $this->paymentIntent->id);
            }
        }

        $this->triggerAuthentication($piSecrets);

        // If this method is called, we should also clear the PI from cache because it cannot be reused
        $object = clone $this->paymentIntent;
        $this->destroy($quote->getId());

        $this->processAuthenticatedOrder($order, $object);

        return $object;
    }

    public function prepareRollback($paymentIntent = null)
    {
        if (empty($paymentIntent))
            $paymentIntent = $this->paymentIntent;

        if (empty($paymentIntent->charges->data))
            return;

        foreach ($paymentIntent->charges->data as $charge)
        {
            if ($charge->captured)
            {
                $this->rollback->addCharge($charge->id);
            }
            else
            {
                $this->rollback->addAuthorization($paymentIntent->id);
                break;
            }
        }
    }

    public function setTransactionDetails($order, $paymentIntent)
    {
        $payment = $order->getPayment();
        $payment->setTransactionId($paymentIntent->id);
        $payment->setLastTransId($paymentIntent->id);
        $payment->setIsTransactionClosed(0);
        $payment->setIsFraudDetected(false);

        if (!empty($paymentIntent->charges->data[0]))
        {
            $charge = $paymentIntent->charges->data[0];

            if ($this->config->isStripeRadarEnabled() &&
                isset($charge->outcome->type) &&
                $charge->outcome->type == 'manual_review')
            {
                $payment->setAdditionalInformation("stripe_outcome_type", $charge->outcome->type);
            }
        }

        // Let's save the Stripe customer ID on the order's payment in case the customer registers after placing the order
        if (!empty($paymentIntent->customer))
            $payment->setAdditionalInformation("customer_stripe_id", $paymentIntent->customer);
    }

    public function processAuthenticatedOrder($order, $paymentIntent)
    {
        $this->setTransactionDetails($order, $paymentIntent);

        if (!empty($paymentIntent->charges->data[0]))
        {
            $order->getPayment()->setIsTransactionPending(false);

            if ($paymentIntent->charges->data[0]->captured == false)
                $order->getPayment()->setIsTransactionClosed(false);
            else
                $order->getPayment()->setIsTransactionClosed(true);
        }
        else
        {
            $order->getPayment()->setIsTransactionPending(true);
        }

        $shouldCreateInvoice = $this->config->isAuthorizeOnly() && $this->config->isAutomaticInvoicingEnabled();

        if ($shouldCreateInvoice)
        {
            $invoice = $order->prepareInvoice();
            $invoice->setTransactionId($paymentIntent->id);
            $invoice->register();
            $order->addRelatedObject($invoice);
        }

        if (!empty($paymentIntent->payment_method_options->card->installments->plan->count))
        {
            $plan = $paymentIntent->payment_method_options->card->installments->plan;
            $comment = __("The balance for this order will be paid over a %1 %2 period.", $plan->count, $plan->interval);
            $order->addStatusToHistory($status = false, $comment, $isCustomerNotified = false);
        }
    }

    protected function createSubscriptionsFor($order, &$params)
    {
        if (!$this->helper->hasSubscriptionsIn($order->getAllItems()))
            return [];

        if ($this->quote)
            $quote = $this->quote; // Used when migrating subscriptions from the CLI
        else
            $quote = $this->quoteRepository->get($order->getQuoteId());

        $trialEnd = $order->getPayment()->getAdditionalInformation("subscription_start");
        $this->subscriptionData = $data = $this->subscriptionsHelper->createSubscriptions($order, false, $trialEnd);

        $piSecrets = $data['piSecrets'];
        $createdSubscriptions = $data['createdSubscriptions'];
        $params["customer"] = $data['stripeCustomerId'];

        if (empty($createdSubscriptions))
            return [];

        // The following is needed for the Multishipping page, in theory there should be only a single piSecret because multiple subscriptions are disallowed
        foreach ($piSecrets as $paymentIntentId => $clientSecret)
        {
            $order->getPayment()
                ->setAdditionalInformation("payment_intent_id", $paymentIntentId)
                ->setAdditionalInformation("payment_intent_client_secret", $clientSecret);
        }

        return $piSecrets;
    }

    protected function setOrderState($order, $state)
    {
        $status = $order->getConfig()->getStateDefaultStatus($state);
        $order->setState($state)->setStatus($status);
    }

    public function getDescription()
    {
        if (empty($this->paymentIntent->description))
            return null;

        return $this->paymentIntent->description;
    }

    protected function deleteSavedCard($paymentMethodId)
    {
        // If the card is already saved, delete the old one so that the customer's saved cards are not duplicated
        // This also ensures that billing address updates are reflected in the payment
        $card = $this->customer->findCardByPaymentMethodId($paymentMethodId);
        if ($card && $paymentMethodId != $card->id && strpos($card->id, "pm_") === 0)
        {
            $paymentMethod = \Stripe\PaymentMethod::retrieve($card->id);
            if (!empty($paymentMethod->customer))
                $paymentMethod->detach();
        }
    }

    public function updateData($paymentIntentId, $order)
    {
        $this->load($paymentIntentId, 'pi_id');

        $this->setPiId($paymentIntentId);
        $this->setQuoteId($order->getQuoteId());
        $this->setOrderIncrementId($order->getIncrementId());
        $customerId = $order->getCustomerId();
        if (!empty($customerId))
            $this->setCustomerId($customerId);
        $this->setPmId($order->getPayment()->getAdditionalInformation("token"));
        $this->save();
    }
}
