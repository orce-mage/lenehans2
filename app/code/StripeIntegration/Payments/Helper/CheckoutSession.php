<?php

namespace StripeIntegration\Payments\Helper;

use Magento\Framework\Exception\LocalizedException;

class CheckoutSession
{
    public function __construct(
        \StripeIntegration\Payments\Model\Config $config,
        \StripeIntegration\Payments\Model\PaymentIntent $paymentIntent,
        \StripeIntegration\Payments\Model\CheckoutSessionFactory $checkoutSessionFactory,
        \StripeIntegration\Payments\Helper\Generic $paymentsHelper,
        \StripeIntegration\Payments\Helper\Locale $localeHelper,
        \StripeIntegration\Payments\Helper\Subscriptions $subscriptions,
        \StripeIntegration\Payments\Helper\Compare $compare,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->config = $config;
        $this->paymentIntent = $paymentIntent;
        $this->checkoutSessionFactory = $checkoutSessionFactory;
        $this->paymentsHelper = $paymentsHelper;
        $this->localeHelper = $localeHelper;
        $this->subscriptions = $subscriptions;
        $this->compare = $compare;
        $this->customer = $paymentsHelper->getCustomerModel();
        $this->scopeConfig = $scopeConfig;
    }

    public function loadFromQuote($quote)
    {
        try
        {
            $checkoutSessionId = $this->getCheckoutSessionIdFromQuote($quote);

            if ($checkoutSessionId)
                return $this->config->getStripeClient()->checkout->sessions->retrieve($checkoutSessionId, ['expand' => ['payment_intent']]);
            else
                return null;
        }
        catch (\Exception $e)
        {
            return null;
        }
    }

    public function getCheckoutSessionModel()
    {
        $quote = $this->paymentsHelper->getQuote();

        if (empty($quote) || empty($quote->getId()))
            return null;

        $checkoutSession = $this->checkoutSessionFactory->create()->load($quote->getId(), 'quote_id');

        return $checkoutSession;
    }

    public function getCheckoutSessionIdFromQuote($quote)
    {
        if (empty($quote) || empty($quote->getId()))
            return null;

        $checkoutSession = $this->checkoutSessionFactory->create()->load($quote->getId(), 'quote_id');

        return $checkoutSession->getCheckoutSessionId();
    }

    public function getOrderForQuote($quote)
    {
        if (empty($quote) || empty($quote->getId()))
            return null;

        $model = $this->checkoutSessionFactory->create()
            ->load($quote->getId(), 'quote_id');

        $orderIncrementId = $model->getOrderIncrementId();

        if (empty($orderIncrementId))
            return null;

        $order = $this->paymentsHelper->loadOrderByIncrementId($orderIncrementId);
        if ($order && $order->getId())
            return $order;

        return null;
    }

    public function cache($checkoutSession, $quote)
    {
        if (empty($quote) || empty($quote->getId()))
            return null;

        if (empty($checkoutSession) || empty($checkoutSession->id))
            return null;

        $this->checkoutSessionFactory->create()
            ->load($quote->getId(), 'quote_id')
            ->setQuoteId($quote->getId())
            ->setCheckoutSessionId($checkoutSession->id)
            ->save();
    }

    public function uncache($checkoutSessionId)
    {
        $this->checkoutSessionFactory->create()
            ->load($checkoutSessionId, 'checkout_session_id')
            ->delete();
    }

    public function load()
    {
        $quote = $this->paymentsHelper->getQuote();
        $checkoutSession = $this->loadFromQuote($quote);
        $params = $this->getSessionParamsFromQuote($quote);

        if (!$checkoutSession)
        {
            return null;
        }
        else if ($this->hasChanged($checkoutSession, $params))
        {
            $this->cancelOrder($checkoutSession, __("The customer returned from Stripe and changed the cart details."));
            $this->cancel($checkoutSession);
            return null;
        }
        else if ($this->hasExpired($checkoutSession))
        {
            $this->cancelOrder($checkoutSession, __("The customer left from the payment page without paying."));
            $this->cancel($checkoutSession);
            return null;
        }

        return $checkoutSession;
    }

    public function getAvailablePaymentMethods()
    {
        $quote = $this->paymentsHelper->getQuote();
        $methods = [];

        try
        {
            $checkoutSession = $this->load();

            if (!$checkoutSession)
            {
                $params = $this->getSessionParamsFromQuote($quote);
                if (!empty($params["payment_intent_data"])) // In subscription mode, this is not set
                    $params["payment_intent_data"]["description"] = "Retrieval of available payment methods";

                $checkoutSession = $this->create($params, $quote);
            }

            if (!empty($checkoutSession->payment_method_types))
                $methods = $checkoutSession->payment_method_types;

            return $methods;
        }
        catch (\Exception $e)
        {
            $this->paymentsHelper->logError($e->getMessage());
            return ['An error has occurred.'];
        }
    }

    protected function calcTotal($checkoutSessionParams)
    {
        if (empty($checkoutSessionParams["line_items"][0]["price_data"]["unit_amount"]))
            return 0;

        $total = 0;
        foreach ($checkoutSessionParams["line_items"] as $lineItem)
            $total += $lineItem["price_data"]["unit_amount"] * $lineItem["quantity"];

        return $total;
    }

    // Compares parameters which may affect which payment methods will be available at the Stripe Checkout landing page
    public function hasChanged($checkoutSession, $params)
    {
        if (isset($params["mode"]) && $params["mode"] == "subscription")
        {
            $comparisonParams = [
                "amount_total" => $this->calcTotal($params),
                "currency" => $params["line_items"][0]["price_data"]["currency"],
                "payment_intent" => "unset",
                "mode" => $params["mode"]
            ];
        }
        else
        {
            $comparisonParams = [
                "amount_total" => $this->calcTotal($params),
                "currency" => $params["line_items"][0]["price_data"]["currency"],
                "payment_intent" => [
                    "capture_method" => $params["payment_intent_data"]["capture_method"]
                ],
                "submit_type" => $params["submit_type"]
            ];

            // Shipping country may affect payment methods
            if (!empty($params["payment_intent_data"]["shipping"]["address"]["country"]))
                $comparisonParams["payment_intent"]["shipping"]["address"]["country"] = $params["payment_intent_data"]["shipping"]["address"]["country"];
            else
                $comparisonParams["payment_intent"]["shipping"] = "unset";

            // Save customer card may affect payment methods
            if (!empty($params["payment_intent_data"]["setup_future_usage"]))
                $comparisonParams["payment_intent"]["setup_future_usage"] = $params["payment_intent_data"]["setup_future_usage"];
            else
                $comparisonParams["payment_intent"]["setup_future_usage"] = "unset";

            // Customer does not affect which payment methods are available, but it may do in the future based on Radar risk level or customer credit score
            if (!empty($params["customer"]))
                $comparisonParams["customer"] = $params["customer"];
        }

        if ($this->compare->isDifferent($checkoutSession, $comparisonParams))
            return true;

        $lineItems = $this->config->getStripeClient()->checkout->sessions->allLineItems($checkoutSession->id, ['limit' => 100]);
        if (count($lineItems->data) != count($params['line_items']))
            return true;

        $comparisonParams = [];
        foreach ($lineItems->data as $i => $item)
        {
            $comparisonParams[$i] = [
                'price' => [
                    'currency' => $params['line_items'][$i]['price_data']['currency'],
                    'unit_amount' => $params['line_items'][$i]['price_data']['unit_amount'],
                ],
                'quantity' => $params['line_items'][$i]['quantity']
            ];

            if (!isset($params['line_items'][$i]['recurring']))
                $comparisonParams[$i]['price']['recurring'] = "unset";
            else
            {
                $comparisonParams[$i]['price']['recurring']['interval'] = $params['line_items'][$i]['recurring']['interval'];
                $comparisonParams[$i]['price']['recurring']['interval_count'] = $params['line_items'][$i]['recurring']['interval_count'];
            }
        }

        if ($this->compare->isDifferent($lineItems->data, $comparisonParams))
            return true;

        return false;
    }

    public function create($params, $quote)
    {
        if (empty($params))
            return null;

        $checkoutSession = $this->config->getStripeClient()->checkout->sessions->create($params);
        $this->cache($checkoutSession, $quote);
        return $checkoutSession;
    }

    public function canCancel($checkoutSession)
    {
        if (empty($checkoutSession->payment_intent->id))
            return false;

        if (in_array($checkoutSession->payment_intent->status, ["canceled", "succeeded"]))
            return false;

        return true;
    }

    public function cancel($checkoutSession)
    {
        try
        {
            if ($this->canCancel($checkoutSession))
                $this->config->getStripeClient()->paymentIntents->cancel($checkoutSession->payment_intent->id, ['cancellation_reason' => 'duplicate']);
        }
        catch (\Exception $e)
        {

        }

        if (!empty($checkoutSession->id))
            $this->uncache($checkoutSession->id);
    }

    protected function getExpirationTime()
    {
        $storeId = $this->paymentsHelper->getStoreId();
        $cookieLifetime = $this->scopeConfig->getValue("web/cookie/cookie_lifetime", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        $oneHour = 1 * 60 * 60;
        $twentyFourHours = 24 * 60 * 60;
        $cookieLifetime = max($oneHour, $cookieLifetime);
        $cookieLifetime = min($twentyFourHours, $cookieLifetime);

        return time() + $cookieLifetime;
    }

    protected function getSessionParamsFrom($lineItems, $subscriptions, $quote, $order = null)
    {
        $returnUrl = $this->paymentsHelper->getUrl('stripe/payment/index', ["payment_method" => "stripe_checkout"]);
        $cancelUrl = $this->paymentsHelper->getUrl('stripe/payment/cancel', ["payment_method" => "stripe_checkout"]);

        $params = [
            'expires_at' => $this->getExpirationTime(),
            'cancel_url' => $cancelUrl,
            'success_url' => $returnUrl,
            'locale' => $this->localeHelper->getStripeCheckoutLocale(),
            'line_items' => $lineItems
        ];

        if (!empty($subscriptions))
        {
            $params["mode"] = "subscription";
            $params["subscription_data"] = [];
            $products = [];

            foreach ($subscriptions as $subscription)
            {
                $product = $subscription['product'];
                $profile = $subscription['profile'];

                if ($order)
                    $metadata = $this->subscriptions->collectMetadata($profile, $product, $order);
                else
                    $metadata = $this->subscriptions->collectMetadata($profile, $product, $quote);

                $products[] = $metadata["Product ID"];

                $params["subscription_data"]["metadata"]["Order #"] = $metadata["Order #"];
                $params["subscription_data"]["metadata"]["Product ID"] = implode(",", $products);

                if ($profile['trial_days'] > 0)
                    $params["subscription_data"]['trial_period_days'] = $profile['trial_days'];
            }
        }
        else
        {
            $params["mode"] = "payment";
            $params["payment_intent_data"] = $this->cleanPaymentIntentData($this->paymentIntent->getParamsFrom($quote, $order));
            $params["submit_type"] = "pay";

            $params["payment_method_options"] = [
                "acss_debit" => [
                    "mandate_options" => [
                        "payment_schedule" => "sporadic",
                        "transaction_type" => "personal"
                    ]
                ]
            ];
        }

        if ($this->config->alwaysSaveCards())
        {
            try
            {
                $this->customer->createStripeCustomerIfNotExists(false, $order);
                $this->stripeCustomer = $this->customer->retrieveByStripeID();
                if (!empty($this->stripeCustomer->id))
                    $params['customer'] = $this->stripeCustomer->id;

                if (!empty($params["payment_intent_data"]))
                {
                    if ($this->config->isAuthorizeOnly() && $this->config->retryWithSavedCard())
                        $params["payment_intent_data"]['setup_future_usage'] = "off_session";
                    else
                        $params["payment_intent_data"]['setup_future_usage'] = "on_session";
                }
            }
            catch (\Stripe\Exception\CardException $e)
            {
                throw new LocalizedException(__($e->getMessage()));
            }
            catch (\Exception $e)
            {
                $this->paymentsHelper->dieWithError(__('An error has occurred. Please contact us to complete your order.'), $e);
            }
        }
        else
        {
            if ($this->paymentsHelper->isCustomerLoggedIn())
                $this->customer->createStripeCustomerIfNotExists(false, $order);

            $this->stripeCustomer = $this->customer->retrieveByStripeID();
            if (!empty($this->stripeCustomer->id))
                $params['customer'] = $this->stripeCustomer->id;
            else if ($order)
                $params['customer_email'] = $order->getCustomerEmail();
            else if ($quote->getCustomerEmail())
                $params['customer_email'] = $quote->getCustomerEmail();
        }

        return $params;
    }

    public function getSessionParamsFromQuote($quote)
    {
        if (empty($quote))
            throw new \Exception("No quote specified for Checkout params.");

        $subscriptions = $this->subscriptions->getSubscriptionsFromQuote($quote);
        $lineItems = $this->getLineItemsForQuote($quote, $subscriptions);
        $params = $this->getSessionParamsFrom($lineItems, $subscriptions, $quote);

        return $params;
    }

    protected function getLineItemsForQuote($quote, $subscriptions)
    {
        $currency = strtolower($quote->getQuoteCurrencyCode());
        $lines = [];
        $lineItemsTax = 0;
        $subscriptionsShipping = 0;
        $hasSubscriptions = false;

        $allSubscriptionsTotal = 0;
        $subscriptionsProductIDs = [];
        $interval = "month";
        $intervalCount = 1;
        foreach ($subscriptions as $subscription)
        {
            $subscriptionTotal = 0;
            $profile = $subscription['profile'];
            $subscriptionsProductIDs[] = $subscription['product']->getId();
            $interval = $profile['interval'];
            $intervalCount = $profile['interval_count'];

            $subscriptionTotal += ($profile['qty'] * $profile['amount_magento']);

            if ($this->subscriptions->chargeShippingRecurringly())
            {
                $subscriptionTotal += $profile['shipping_magento'];

                if (!$this->config->shippingIncludesTax())
                    $subscriptionTotal += $profile['tax_amount_shipping']; // Includes qty calculation

                if (!$this->config->priceIncludesTax())
                    $subscriptionTotal += $profile['tax_amount_item']; // Includes qty calculation
            }

            $subscriptionTotal -= $profile['discount_amount_magento'];

            $allSubscriptionsTotal += round($subscriptionTotal, 2);
        }

        $remainingAmount = $quote->getGrandTotal() - $allSubscriptionsTotal;

        if ($remainingAmount > 0)
        {
            $lineItem = [
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [
                        'name' => __("Amount due"),
                        'metadata' => [
                            'Type' => 'RegularProductsTotal',
                        ]
                    ],
                    'unit_amount' => $this->paymentsHelper->convertMagentoAmountToStripeAmount($remainingAmount, $currency),
                ],
                'quantity' => 1,

            ];

            $lines[] = $lineItem;
        }

        if ($allSubscriptionsTotal > 0)
        {
            $hasSubscriptions = true;
            $lineItem = [
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [
                        'name' => __("Subscriptions"),
                        'metadata' => [
                            'Type' => 'SubscriptionsTotal',
                            'SubscriptionProductIDs' => implode(",", $subscriptionsProductIDs)
                        ]
                    ],
                    'unit_amount' => $this->paymentsHelper->convertMagentoAmountToStripeAmount($allSubscriptionsTotal, $currency),
                    'recurring' => [
                        'interval' => $interval,
                        'interval_count' => $intervalCount
                    ]
                ],
                'quantity' => 1,

            ];

            $lines[] = $lineItem;
        }

        if ($remainingAmount < 0 && $hasSubscriptions)
        {
            // A discount that should have been applied on subscriptions, has not been applied on subscriptions
        }

        return $lines;
    }

    protected function cleanPaymentIntentData($data)
    {
        $supportedParams = ['application_fee_amount', 'capture_method', 'description', 'metadata', 'on_behalf_of', 'receipt_email', 'setup_future_usage', 'shipping', 'statement_descriptor', 'statement_descriptor_suffix', 'transfer_data', 'transfer_group'];

        $params = [];

        foreach ($data as $key => $value)
            if (in_array($key, $supportedParams))
                $params[$key] = $value;

        return $params;
    }

    public function getSessionParamsForOrder($order)
    {
        $amount = $order->getGrandTotal();
        $currency = strtolower($order->getOrderCurrencyCode());
        $subscriptions = $this->subscriptions->getSubscriptionsFromOrder($order);
        $lineItems = $this->getLineItemsForOrder($order, $subscriptions);

        $this->checkIfCartIsSupported($subscriptions);

        $params = $this->getSessionParamsFrom($lineItems, $subscriptions, $order->getQuote(), $order);

        return $params;
    }

    public function checkIfCartIsSupported($subscriptions)
    {
        if (empty($subscriptions))
            return true;

        if (!$this->areInvoicedTogether($subscriptions))
            throw new LocalizedException(__("Subscriptions that do not renew together must be bought separately."));

        return true;
    }

    public function areInvoicedTogether($subscriptions)
    {
        $startingTimes = [];
        $endingTimes = [];
        $now = time();

        foreach ($subscriptions as $subscription)
        {
            $starts = $now;
            if (!empty($subscription['profile']['trial_end']))
                $starts = $subscription['profile']['trial_end'];
            else if (!empty($subscription['profile']['trial_days']))
                $starts = strtotime("+" . $subscription['profile']['trial_days'] . " days");

            $ends = $starts + strtotime("+" . $subscription['profile']['interval_count'] . " " . $subscription['profile']['interval']);

            $startingTimes[$starts] = $starts;
            $endingTimes[$ends] = $ends;
        }

        if (count($startingTimes) > 1)
            return false;

        if (count($endingTimes) > 1)
            return false;

        return true;
    }

    public function getLineItemsForOrder($order, $subscriptions)
    {
        $currency = strtolower($order->getOrderCurrencyCode());
        $cents = $this->paymentsHelper->isZeroDecimal($currency) ? 1 : 100;
        $orderItems = $order->getAllVisibleItems();
        $lines = [];
        $lineItemsTax = 0;
        $subscriptionsShipping = 0;
        $hasSubscriptions = false;

        $allSubscriptionsTotal = 0;
        $subscriptionsProductIDs = [];
        $interval = "month";
        $intervalCount = 1;
        foreach ($subscriptions as $subscription)
        {
            $subscriptionTotal = 0;
            $profile = $subscription['profile'];
            $subscriptionsProductIDs[] = $subscription['product']->getId();
            $interval = $profile['interval'];
            $intervalCount = $profile['interval_count'];

            $subscriptionTotal += ($profile['qty'] * $profile['amount_magento']);

            if ($this->subscriptions->chargeShippingRecurringly())
            {
                $subscriptionTotal += $profile['shipping_magento'];

                if (!$this->config->shippingIncludesTax())
                    $subscriptionTotal += $profile['tax_amount_shipping']; // Includes qty calculation

                if (!$this->config->priceIncludesTax())
                    $subscriptionTotal += $profile['tax_amount_item']; // Includes qty calculation
            }

            $subscriptionTotal -= $profile['discount_amount_magento'];

            $allSubscriptionsTotal += round($subscriptionTotal, 2);
        }

        $remainingAmount = $order->getGrandTotal() - $allSubscriptionsTotal;

        if ($remainingAmount > 0)
        {
            $lineItem = [
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [
                        'name' => __("Amount due"),
                        'metadata' => [
                            'Type' => 'RegularProductsTotal',
                        ]
                    ],
                    'unit_amount' => $this->paymentsHelper->convertMagentoAmountToStripeAmount($remainingAmount, $currency),
                ],
                'quantity' => 1,

            ];

            $lines[] = $lineItem;
        }

        if ($allSubscriptionsTotal > 0)
        {
            $hasSubscriptions = true;
            $lineItem = [
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [
                        'name' => __("Subscriptions"),
                        'metadata' => [
                            'Type' => 'SubscriptionsTotal',
                            'SubscriptionProductIDs' => implode(",", $subscriptionsProductIDs)
                        ]
                    ],
                    'unit_amount' => $this->paymentsHelper->convertMagentoAmountToStripeAmount($allSubscriptionsTotal, $currency),
                    'recurring' => [
                        'interval' => $interval,
                        'interval_count' => $intervalCount
                    ]
                ],
                'quantity' => 1,

            ];

            $lines[] = $lineItem;
        }

        if ($remainingAmount < 0 && $hasSubscriptions)
        {
            // A discount that should have been applied on subscriptions, has not been applied on subscriptions
        }

        return $lines;
    }

    public function getPaymentIntentUpdateParams($params, $paymentIntent, $filterParams = [])
    {
        $updateParams = [];
        $allowedParams = ["amount", "currency", "description", "metadata"];

        foreach ($allowedParams as $key)
        {
            if (!empty($filterParams) && !in_array($key, $filterParams))
                continue;

            if (isset($params[$key]))
                $updateParams[$key] = $params[$key];
        }

        if (!empty($updateParams["amount"]) && $updateParams["amount"] == $paymentIntent->amount)
            unset($updateParams["amount"]);

        if (!empty($updateParams["currency"]) && $updateParams["currency"] == $paymentIntent->currency)
            unset($updateParams["currency"]);

        return $updateParams;
    }

    public function getLastTransactionId(\Magento\Payment\Model\InfoInterface $payment)
    {
        if ($payment->getLastTransId())
            return $payment->getLastTransId();

        if ($payment->getAdditionalInformation("checkout_session_id"))
        {
            $csId = $payment->getAdditionalInformation("checkout_session_id");
            $cs = $this->config->getStripeClient()->checkout->sessions->retrieve($csId, ['expand' => ['payment_intent', 'subscription']]);
            if (!empty($cs->payment_intent->id))
                return $cs->payment_intent->id;
        }

        return null;
    }

    public function getPaymentMethodName($code)
    {
        switch ($code)
        {
            case 'visa': return "Visa";
            case 'amex': return "American Express";
            case 'mastercard': return "MasterCard";
            case 'discover': return "Discover";
            case 'diners': return "Diners Club";
            case 'jcb': return "JCB";
            case 'unionpay': return "UnionPay";
            case 'cartes_bancaires': return "Cartes Bancaires";
            case 'bacs_debit': return "BACS Direct Debit";
            case 'au_becs_debit': return "BECS Direct Debit";
            case 'boleto': return "Boleto";
            case 'acss_debit': return "ACSS Direct Debit / Canadian PADs";
            case 'ach_debit': return "ACH Direct Debit";
            case 'oxxo': return "OXXO";
            case 'klarna': return "Klarna";
            case 'sepa': return "SEPA Direct Debit";
            case 'sepa_debit': return "SEPA Direct Debit";
            case 'sepa_credit': return "SEPA Credit Transfer";
            case 'sofort': return "SOFORT";
            case 'ideal': return "iDEAL";
            case 'paypal': return "PayPal";
            case 'wechat': return "WeChat Pay";
            case 'alipay': return "Alipay";
            case 'grabpay': return "GrabPay";
            case 'afterpay_clearpay': return "Afterpay / Clearpay";
            case 'multibanco': return "Multibanco";
            case 'p24': return "P24";
            case 'giropay': return "Giropay";
            case 'eps': return "EPS";
            case 'bancontact': return "Bancontact";
            default:
                return ucwords(str_replace("_", " ", $code));
        }
    }

    public function cancelOrder($checkoutSession, $orderComment)
    {
        if (empty($checkoutSession->id))
            return;

        $checkoutSessionModel = $this->checkoutSessionFactory->create()->load($checkoutSession->id, 'checkout_session_id');

        if (!$checkoutSessionModel->getOrderIncrementId())
            return;

        $order = $this->paymentsHelper->loadOrderByIncrementId($checkoutSessionModel->getOrderIncrementId());
        if (!$order || !$order->getId())
            return;

        $state = \Magento\Sales\Model\Order::STATE_CANCELED;
        $status = $order->getConfig()->getStateDefaultStatus($state);
        $order->addStatusToHistory($status, $orderComment, $isCustomerNotified = false);
        $this->paymentsHelper->saveOrder($order);

        $checkoutSessionModel->setOrderIncrementId(null)->save();
    }

    public function hasExpired($checkoutSession)
    {
        return ($checkoutSession->status == "expired" || $checkoutSession->status == "complete");
    }
}
