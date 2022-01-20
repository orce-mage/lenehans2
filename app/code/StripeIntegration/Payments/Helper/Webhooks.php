<?php

namespace StripeIntegration\Payments\Helper;

use StripeIntegration\Payments\Helper\Logger;
use StripeIntegration\Payments\Exception\WebhookException;

class Webhooks
{
    public $eventOrderIds = [];

    protected $unifiedEvents = [
        "charge.captured",
        "charge.failed",
        "customer.subscription.created",
        "checkout.session.expired",
        "invoice.finalized",
        "invoice.payment_succeeded",
        "invoice.voided",
        "review.closed",
    ];

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Response\Http $response,
        \StripeIntegration\Payments\Logger\WebhooksLogger $webhooksLogger,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \StripeIntegration\Payments\Helper\Api $api,
        \StripeIntegration\Payments\Helper\Generic $helper,
        \StripeIntegration\Payments\Model\InvoiceFactory $invoiceFactory,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \StripeIntegration\Payments\Model\Config $config,
        \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory,
        \Magento\Sales\Model\Service\CreditmemoService $creditmemoService,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Sales\Model\Order\Invoice $invoiceModel,
        \Magento\Framework\UrlInterface $urlInterface,
        \StripeIntegration\Payments\Model\ResourceModel\Webhook\Collection $webhookCollection,
        \StripeIntegration\Payments\Model\ResourceModel\Source\CollectionFactory $sourceCollectionFactory,
        \StripeIntegration\Payments\Model\PaymentIntentFactory $paymentIntentFactory,
        \StripeIntegration\Payments\Model\CheckoutSessionFactory $checkoutSessionFactory,
        \StripeIntegration\Payments\Helper\WebhooksSetup $webhooksSetup
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->webhooksLogger = $webhooksLogger;
        $this->logger = $logger;
        $this->cache = $cache;
        $this->api = $api;
        $this->helper = $helper;
        $this->stripeCustomer = $helper->getCustomerModel();
        $this->eventManager = $eventManager;
        $this->invoiceFactory = $invoiceFactory;
        $this->orderSender = $orderSender;
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->creditmemoService = $creditmemoService;
        $this->transactionFactory = $transactionFactory;
        $this->invoiceModel = $invoiceModel;
        $this->urlInterface = $urlInterface;
        $this->webhookCollection = $webhookCollection;
        $this->sourceCollectionFactory = $sourceCollectionFactory;
        $this->paymentIntentFactory = $paymentIntentFactory;
        $this->checkoutSessionFactory = $checkoutSessionFactory;
        $this->webhooksSetup = $webhooksSetup;
    }

    public function dispatchEvent()
    {
        try
        {
            if ($this->request->getMethod() == 'GET')
                throw new WebhookException("Webhooks are working correctly!", 200);

            // Retrieve the request's body and parse it as JSON
            $body = $this->request->getContent();

            $event = json_decode($body, true);
            $stdEvent = json_decode($body);

            if (empty($event['type']))
                throw new WebhookException(__("Unknown event type"));

            if ($event['type'] == "product.created")
            {
                $this->onProductCreated($event, $stdEvent);
                $this->log("200 OK");
                return;
            }

            if ($this->cache->load($event['id']) && empty($this->request->getParam('dev')))
                throw new WebhookException(__("Event with ID %1 has already been processed.", $event['id']), 202);

            $eventType = $this->getEventType($event);
            $this->log("Received $eventType");

            http_response_code(500);
            $this->eventManager->dispatch($eventType, array(
                    'arrEvent' => $event,
                    'stdEvent' => $stdEvent,
                    'object' => $event['data']['object']
                ));
            http_response_code(200);

            $this->cache($event);
            $this->log("200 OK");
        }
        catch (WebhookException $e)
        {
            if (!empty($e->statusCode))
                http_response_code($e->statusCode);

            $this->error($e->getMessage(), $e->statusCode, true);

            if (!empty($e->statusCode) && !empty($event) && $e->statusCode < 400)
                $this->cache($event);
        }
        catch (\Exception $e)
        {
            if (!empty($e->statusCode))
                http_response_code($e->statusCode);

            $this->log($e->getMessage());
            $this->log($e->getTraceAsString());
            $this->error($e->getMessage());

            if (!empty($e->statusCode) && !empty($event) && $e->statusCode < 400)
                $this->cache($event);
        }
    }

    protected function getEventType(array $event)
    {
        $isUnified = true;
        $eventType = "stripe_payments_webhook_" . str_replace(".", "_", $event['type']);

        try
        {
            // For Stripe Checkout, all events are unified
            $orderId = $this->getOrderIdFromObject($event['data']['object']); // Does not throw an exception
            if ($orderId)
            {
                $order = $this->loadWebhookOrderByIncrementId($orderId, $event); // Throws an exception only if the order is not found in Magento
                if ($order->getPayment() && $order->getPayment()->getMethod())
                {
                    if ($this->helper->isStripeCheckoutMethod($order->getPayment()->getMethod()))
                        return $eventType;
                }
            }

            // For other payment methods, events are processed separately
            $paymentMethod = $this->getPaymentMethodFrom($event);
            if (!in_array($event['type'], $this->unifiedEvents) && !empty($paymentMethod))
                $isUnified = false;

            if (!$isUnified)
            {
                $eventType .= "_$paymentMethod";
                return $eventType;
            }
        }
        catch (\Exception $e)
        {
            $this->log($e->getMessage());
        }

        return $eventType;
    }

    protected function getPaymentMethodFrom($event)
    {
        // Bancontact, Giropay, iDEAL
        if (isset($event['data']['object']['type']))
            $paymentMethod = $event['data']['object']['type'];
        // SOFORT and SEPA
        else if (isset($event['data']['object']['source']['type']))
            $paymentMethod = $event['data']['object']['source']['type'];
        // ACH bank accounts
        else if (isset($event['data']['object']['source']['object']))
            $paymentMethod = $event['data']['object']['source']['object'];
        else if (isset($event['data']['object']['payment_method_types']))
            $paymentMethod = implode("_", $event['data']['object']['payment_method_types']);
        else if (isset($event['data']['object']['payment_method_details']))
            $paymentMethod = $event['data']['object']['payment_method_details']['type'];
        else
            $paymentMethod = '';

        // Magento 2 event names do not allow numbers
        $paymentMethod = str_replace("p24", "przelewy", $paymentMethod);

        return $paymentMethod;
    }

    public function onProductCreated($event, $stdEvent)
    {
        if ($event['data']['object']['name'] == "Webhook Configuration")
        {
            $storeCode = $event['data']['object']['metadata']['store_code'];
            $mode = ucfirst($event['data']['object']['metadata']['mode']) . " Mode";
            $this->log("Received automatic webhook configuration for $storeCode ($mode)");
            $this->eventManager->dispatch("automatic_webhook_configuration", array('event' => $stdEvent));
        }
        else if ($event['data']['object']['name'] == "Webhook Ping")
        {
            $this->webhookCollection->pong($event['data']['object']['metadata']['pk']);
            $this->webhooksSetup->deleteProduct($event['data']['object']['id']);
        }
    }

    public function error($msg, $status = null, $displayError = false)
    {
        if ($status && $status > 0)
            $responseStatus = $status;
        else
            $responseStatus = 202;

        $this->log("$responseStatus $msg");

        if (!$displayError)
            $msg = "An error has occurred. Please check var/log/stripe_payments_webhooks.log for more details.";

        $this->response
            ->setStatusCode($responseStatus)
            ->setHeader('Content-Type', 'text/plain', $overwriteExisting = true)
            ->setHeader('X-Content-Type-Options', 'nosniff', true)
            ->setContent($msg);
    }

    public function log($msg)
    {
        // Magento 2.0.0 - 2.4.3
        if (method_exists($this->webhooksLogger, 'addInfo'))
            $this->webhooksLogger->addInfo($msg);
        // Magento 2.4.4+
        else
            $this->webhooksLogger->info($msg);
    }

    public function verifyWebhookSignature($storeId)
    {
        $signingSecret = $this->config->getWebhooksSigningSecret();
        if (empty($signingSecret))
            return;

        try
        {
            if (!isset($_SERVER['HTTP_STRIPE_SIGNATURE']))
                throw new WebhookException("Webhook signature could not be found in the request payload", 400);

            $payload = $this->request->getContent();
            $event = \Stripe\Webhook::constructEvent($payload, $_SERVER['HTTP_STRIPE_SIGNATURE'], $signingSecret);
        }
        catch(\UnexpectedValueException $e)
        {
            throw new WebhookException("Invalid webhook payload", 400);
        }
        catch(\Stripe\Exception\SignatureVerificationException $e)
        {
            throw new WebhookException("Invalid webhook signature", 400);
        }
    }

    public function cache($event)
    {
        if (empty($event['id']))
            throw new WebhookException("No event ID specified");

        $this->cache->save("processed", $event['id'], array('stripe_payments_webhooks_events_processed'), 24 * 60 * 60);
    }

    // Does not throw an exception
    public function getOrderIdFromObject(array $object)
    {
        // For most payment methods, the order ID is here
        if (!empty($object['metadata']['Order #']))
            return $object['metadata']['Order #'];

        // For orders placed over the Sources API, i.e. Elements based Bancontact, SOFORT etc
        if (!empty($object["source"]['metadata']['Order #']))
            return $object["source"]['metadata']['Order #'];

        // For invoices created from the Magento admin, we have the order ID in the stripe_invoices DB table
        if ($object['object'] == 'invoice')
        {
            $entry = $this->invoiceFactory->create()->load($object['id'], 'invoice_id');
            if ($entry->getOrderIncrementId())
                return $entry->getOrderIncrementId();

            foreach ($object['lines']['data'] as $lineItem)
            {
                // The invoice may include a subscription bought using Stripe Checkout
                if ($lineItem['type'] == "subscription" && !empty($lineItem['metadata']['Order #']))
                    return $lineItem['metadata']['Order #'];
            }
        }
        // If the merchant refunds a charge of a recurring subscription order from the Stripe dashboard, we need to drill down to the parent subscription
        else if ($object['object'] == 'charge' && !empty($object['invoice']) && !empty($object['customer']))
        {
            $this->config->reInitStripeFromCustomerId($object['customer']); // @todo: this statement should not be in this method
            $stripe = $this->config->getStripeClient();
            $invoice = $stripe->invoices->retrieve($object['invoice'], ['expand' => ['subscription']]);
            if (!empty($invoice->subscription->metadata->{"Order #"}))
                return $invoice->subscription->metadata->{"Order #"};
        }
        else if ($object['object'] == "checkout.session")
        {
            $checkoutSessionModel = $this->checkoutSessionFactory->create()->load($object["id"], 'checkout_session_id');
            if ($checkoutSessionModel->getOrderIncrementId())
                return $checkoutSessionModel->getOrderIncrementId();
        }
        // Triggered via stripe_payments_webhook_review_closed
        else if (!empty($object['payment_intent']))
        {
            $paymentIntent = $this->paymentIntentFactory->create()->load($object['payment_intent'], 'pi_id');
            $orderId = $paymentIntent->getOrderIncrementId();
            if ($orderId)
                return $orderId;
        }

        $method = $this->getPaymentMethod($object);

        if ($method == "klarna")
        {
            $orderId = $this->getKlarnaOrderNumber($object);
            if ($orderId)
                return $orderId;
        }
        else if ($method == "sepa_credit_transfer")
        {
            $source = $this->loadSepaCreditTransferSourceFromObject($object);

            if ($source && $source->getOrderIncrementId())
                return $source->getOrderIncrementId();
        }

        return null;
    }

    public function loadSepaCreditTransferSourceFromObject(array $object)
    {
        $collection = $this->sourceCollectionFactory->create();
        $sources = $collection->getSourcesById($object["source"]);
        if ($sources->count() == 0)
            return null;
        else
            return $sources->getFirstItem();
    }

    public function getPaymentMethod(array $object)
    {
        // Most APMs
        if (!empty($object["type"]))
            return $object["type"];

        // ACH Debit
        if (!empty($object["payment_method_details"]["type"]))
            return $object["payment_method_details"]["type"];

        return null;
    }

    public function loadOrderFromInvoiceId($invoiceId, $event)
    {
        $entry = $this->invoiceFactory->create()->load($invoiceId, 'invoice_id');
        if (!$entry->getOrderIncrementId())
            throw new WebhookException("We could not find the order for the invoice associated with this charge.", 202);

        return $this->loadWebhookOrderByIncrementId($entry->getOrderIncrementId(), $event);
    }

    public function loadOrderFromEvent(array $event)
    {
        $orderId = $this->getOrderIdFromObject($event['data']['object']);

        if (empty($orderId))
            throw new WebhookException(__("Received %1 webhook but there was no associated Order #", $event['type']), 202);

        return $this->loadWebhookOrderByIncrementId($orderId, $event);
    }

    public function loadSubscriptionProductFromEvent($event)
    {
        if (empty($event['data']['object']['metadata']['Product ID']))
            throw new WebhookException("Could not find a Product ID in the event's metadata.");

        return $this->helper->loadProductById($event['data']['object']['metadata']['Product ID']);
    }

    public function initStripeFrom($order, $event)
    {
        $paymentMethodCode = $order->getPayment()->getMethod();
        if (strpos($paymentMethodCode, "stripe") !== 0)
            throw new WebhookException("Order #$orderId was not placed using Stripe", 202);

        // For multi-stripe account configurations, load the correct Stripe API key from the correct store view
        if (isset($event['data']['object']['livemode']))
            $mode = ($event['data']['object']['livemode'] ? "live" : "test");
        else
            $mode = null;
        $this->config->reInitStripe($order->getStoreId(), $order->getOrderCurrencyCode(), $mode);
        $this->webhookCollection->pong($this->config->getPublishableKey($mode));
        $this->verifyWebhookSignature($order->getStoreId());
    }

    public function getKlarnaOrderNumber(array $object)
    {
        $sourceId = $object['id'];

        if (empty($sourceId))
            return null;

        if (isset($this->eventOrderIds[$sourceId]))
            return $this->eventOrderIds[$sourceId];

        $orderId = null;

        $wait = 2;
        do
        {
            // With Klarna the source is authorized in the front-end before we have an order number, so we load it from the cache instead
            // because the order number does not exist in the source's metadata
            $orderId = $this->cache->load($sourceId);

            // There is a possibility we receive the webhook before the order is committed to the database (because Magento is slow), so we wait if not found
            if (empty($orderId))
            {
                $wait--;
                sleep(4);
            }
        }
        while (empty($orderId) && $wait > 0);

        return $this->eventOrderIds[$sourceId] = $orderId;
    }

    protected function loadWebhookOrderByIncrementId($orderId, $event, $count = 5)
    {
        if (empty($orderId))
            throw new WebhookException(__("Ignoring %1 webhook event with no associated order ID.", $event['type']), 202);

        $order = $this->helper->loadOrderByIncrementId($orderId);

        if ((empty($order) || empty($order->getId())) && $count >= 0)
        {
            // Webhooks Race Condition: Sometimes we may receive the webhook before Magento commits the order to the database,
            // so we give it a few seconds and try again. Can happen when multiple subscriptions are purchased together.
            sleep(4);
            return $this->loadWebhookOrderByIncrementId($orderId, $event, $count - 1);
        }

        if (empty($order) || empty($order->getId()))
            throw new WebhookException(__("Received %1 webhook with Order #%2 but could not find the order in Magento.", $event['type'], $orderId), 202);

        $this->initStripeFrom($order, $event);

        return $order;
    }

    // Called after a source.chargable event
    public function charge($order, $object, $addTransaction = true, $sendNewOrderEmail = true)
    {
        $orderId = $order->getIncrementId();

        $payment = $order->getPayment();
        if (!$payment)
            throw new WebhookException("Could not load payment method for order #$orderId");

        $orderSourceId = $payment->getAdditionalInformation('source_id');
        $webhookSourceId = $object['id'];
        if ($orderSourceId != $webhookSourceId)
            throw new WebhookException("Received source.chargeable webhook for order #$orderId but the source ID on the webhook $webhookSourceId was different than the one on the order $orderSourceId");

        $stripeParams = $this->config->getStripeParamsFrom($order);

        // Reusable sources may not have an amount set
        if (empty($object['amount']))
        {
            $amount = $stripeParams['amount'];
        }
        else
        {
            $amount = $object['amount'];
        }

        $params = array(
            "amount" => $amount,
            "currency" => $object['currency'],
            "source" => $webhookSourceId,
            "description" => $stripeParams['description'],
            "metadata" => $stripeParams['metadata']
        );

        if ($object["type"] == "klarna")
        {
            if (!$this->config->isAuthorizeOnly("klarna"))
                $params["capture"] = true;
        }

        $statementDescription = $this->getStatementDescriptionFor($order, $object);
        if ($statementDescription !== null)
            $params["statement_descriptor"] = $statementDescription;

        // For reusable sources, we will always need a customer ID
        $customerStripeId = $payment->getAdditionalInformation('customer_stripe_id');
        if (!empty($customerStripeId))
            $params["customer"] = $customerStripeId;

        try
        {
            $charge = \Stripe\Charge::create($params);

            $payment->setTransactionId($charge->id);
            $payment->setLastTransId($charge->id);
            $payment->setIsTransactionClosed(0);

            // Log additional info about the payment
            $info = $this->helper->getClearSourceInfo($object[$object['type']]);
            $payment->setAdditionalInformation('source_info', json_encode($info));
            $payment->save();

            if ($addTransaction)
            {
                if (!$charge->captured)
                    $transactionType = \Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH;
                else
                    $transactionType = \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE;
                //Transaction::TYPE_PAYMENT

                $transaction = $payment->addTransaction($transactionType, null, false);
                $transaction->save();
            }

            if ($charge->status == 'succeeded')
            {
                if ($charge->captured == false)
                    // $invoice = $this->helper->invoicePendingOrder($order, \Magento\Sales\Model\Order\Invoice::NOT_CAPTURE, $charge->id);
                    return;
                else
                    $invoice = $this->helper->invoiceOrder($order, $charge->id);

                if ($sendNewOrderEmail)
                    $this->helper->sendNewOrderEmailFor($order);
            }
            // SEPA, SOFORT and other asynchronous methods will be pending
            else if ($charge->status == 'pending')
            {
                $invoice = $this->helper->invoicePendingOrder($order, $charge->id);

                if ($sendNewOrderEmail)
                    $this->helper->sendNewOrderEmailFor($order);
            }
            else
            {
                // In theory we should never have failed charges because they would throw an exception
                $comment = "Authorization failed. Transaction ID: {$charge->id}. Charge status: {$charge->status}";
                $order->addStatusHistoryComment($comment);
                $order->save();
            }

            return $charge;
        }
        catch (\Stripe\Exception\CardException $e)
        {
            $comment = "Order could not be charged because of a card error: " . $e->getMessage();
            $order->addStatusHistoryComment($comment);
            $order->save();
            $this->log($e->getMessage());
            throw new WebhookException($e->getMessage(), 202);
        }
        catch (\Exception $e)
        {
            $comment = "Order could not be charged because of server side error: " . $e->getMessage();
            $order->addStatusHistoryComment($comment);
            $order->save();
            $this->log($e->getMessage());
            throw new WebhookException($e->getMessage(), 202);
        }
    }

    public function getStatementDescriptionFor($order, $object)
    {
        if ($object["type"] == "klarna")
        {
            return "Order #" . $order->getIncrementId();
        }

        return null;
    }

    public function getCurrentRefundFrom($webhookData)
    {
        $lastRefundDate = 0;
        $currentRefund = null;

        foreach ($webhookData['refunds']['data'] as $refund)
        {
            // There might be multiple refunds, and we are looking for the most recent one
            if ($refund['created'] > $lastRefundDate)
            {
                $lastRefundDate = $refund['created'];
                $currentRefund = $refund;
            }
        }

        return $currentRefund;
    }

    public function refundOfflineOrCancel($order)
    {
        $dbTransaction = $this->transactionFactory->create();

        if ($order->canCreditmemo())
        {
            // In theory we should only have a single Invoice for the stripe_payments_invoice payment method
            foreach($order->getInvoiceCollection() as $invoice)
            {
                $creditmemo = $this->creditmemoFactory->createByOrder($order);
                $creditmemo->setInvoice($invoice);
                $this->creditmemoService->refund($creditmemo, true);
                $dbTransaction->addObject($creditmemo);
                $dbTransaction->addObject($invoice);
            }

        }
        else
        {
            $order->cancel();
        }

        $dbTransaction->addObject($order)->save();
    }

    public function refund($order, $object)
    {
        if ($order->getState() == "holded" && $order->canUnhold())
            $order->unhold();

        $dbTransaction = $this->transactionFactory->create();

        // Check if the order has an invoice with the charge ID we are refunding
        $chargeId = $object['id'];
        $chargeAmount = $object['amount'];
        $currentRefund = $this->getCurrentRefundFrom($object);
        $currency = $currentRefund['currency'];
        $baseToOrderRate = $order->getBaseToOrderRate();
        $payment = $order->getPayment();
        if (isset($object["payment_intent"]))
            $pi = $object["payment_intent"];
        else
            $pi = "not_exists";

        // Calculate the real refund amount
        $isMultiCurrencyRefund = ($currentRefund['currency'] != $order->getOrderCurrencyCode());
        $refundAmount = $this->helper->convertStripeAmountToOrderAmount($currentRefund['amount'], $currentRefund['currency'], $order);
        $baseRefundAmount = $this->helper->convertStripeAmountToBaseOrderAmount($currentRefund['amount'], $currentRefund['currency'], $order);

        $baseTotalNotRefunded = $order->getBaseGrandTotal() - $order->getBaseTotalRefunded();
        $totalNotRefunded = $order->getGrandTotal() - $order->getTotalRefunded();

        $baseOrderCurrency = strtolower($order->getBaseCurrencyCode());
        $orderCurrency = strtolower($order->getCurrencyCode());

        if ($isMultiCurrencyRefund)
            $isPartialRefund = ($totalNotRefunded > $refundAmount);
        else
            $isPartialRefund = ($baseTotalNotRefunded > $baseRefundAmount);

        if (!$order->canCreditmemo())
        {
            if ($order->canCancel())
            {
                if (!$isPartialRefund)
                {
                    $order->cancel();
                    $order->save();
                    return true;
                }
                else if ($isPartialRefund)
                {
                    // Don't do anything on a partial refund, we expect a paynemt_intent.succeeded to arrive for the partial capture.
                    return false;
                }
            }
            else if (!$isPartialRefund)
            {
                $invoices = $order->getInvoiceCollection();
                $canceled = 0;
                foreach ($invoices as $invoice)
                {
                    if ($invoice->canCancel())
                    {
                        $dbTransaction->addObject($invoice);
                        $invoice->cancel();
                        $canceled++;
                    }
                }
                if ($canceled > 0)
                {
                    if ($order->canCancel())
                        $order->cancel();

                    $dbTransaction->addObject($order)->save();
                    return true;
                }
            }

            $msg = __('A refund was issued via Stripe, but a Credit Memo could not be created.');
            $this->helper->addOrderComment($msg, $order);
            $dbTransaction->addObject($order)->save();
            return false;
        }

        if ($baseTotalNotRefunded < $baseRefundAmount)
        {
            $humanReadable1 = $this->helper->addCurrencySymbol($refundAmount, $currency);
            $humanReadable2 = $this->helper->addCurrencySymbol($totalNotRefunded, $currency);
            $msg = __('A refund of %1 was issued via Stripe, but the amount is bigger than the available of %2.', $humanReadable1, $humanReadable2);
            $this->helper->addOrderComment($msg, $order);
            $dbTransaction->addObject($order)->save();
            return false;
        }
        else
        {
            $creditmemo = $this->creditmemoFactory->createByOrder($order);
            $baseDiff = $baseTotalNotRefunded - $baseRefundAmount;

            if ($isPartialRefund)
            {
                // We don't have any information from Stripe on what products we are refunding
                $creditmemo->setItems([]);
                $creditmemo->setShippingAmount(0);
                $creditmemo->setAdjustmentPositive($baseDiff);
                $creditmemo->setAdjustmentNegative(0);
            }
            else
            {
                $creditmemo->setItems($order->getAllItems());
                $creditmemo->setShippingAmount($order->getShippingAmount());
                $creditmemo->setAdjustmentPositive(0);
                $creditmemo->setAdjustmentNegative(0);
            }
        }

        $invoice = $this->getInvoiceWithTransactionId($chargeId, $order);

        if (!$invoice)
            $invoice = $this->getInvoiceWithTransactionId($pi, $order);

        if ($invoice)
            $creditmemo->setInvoice($invoice);

        $creditmemo->setBaseSubtotal(0);
        $creditmemo->setSubtotal(0);
        $creditmemo->setBaseGrandTotal($baseRefundAmount);
        $creditmemo->setGrandTotal($refundAmount);

        $this->creditmemoService->refund($creditmemo, true);

        $comment = __("We refunded %1 through Stripe.", $this->helper->addCurrencySymbol($refundAmount, $currency));
        $order->addStatusToHistory($status = false, $comment);

        $dbTransaction
            ->addObject($order)
            ->addObject($creditmemo)
            ->addObject($payment)
            ->save();

        return true;
    }

    public function getInvoiceWithTransactionId($transactionId, $order)
    {
        foreach($order->getInvoiceCollection() as $item)
        {
            $invoiceTransactionId = $this->helper->cleanToken($item->getTransactionId());
            if ($transactionId == $invoiceTransactionId)
                return $item;
        }

        return null;
    }

    public function removeEndpoint()
    {
        $url = $this->urlInterface->getCurrentUrl();
        $endpoints = \Stripe\WebhookEndpoint::all();
        foreach ($endpoints as $endpoint)
        {
            if (strpos($url, $endpoint->url) === 0)
            {
                $endpoint = \Stripe\WebhookEndpoint::retrieve($endpoint->id);
                $endpoint->delete();
            }
        }
    }

    // When multiple events arrive at the same time, lock the current process so that we don't get DB deadlocks
    // Works similar to a queuing system, but is real time rather than cron-based
    public function lock()
    {
        $wait = 10; // seconds to wait for lock
        $sleep = 2; // poll every X seconds
        do
        {
            $lock = $this->cache->load("stripe_payments_webhooks_lock");
            if ($lock)
            {
                sleep($sleep);
                $wait -= $sleep;
            }

        } while ($lock && $wait > 0);

        $this->cache->save(1, "stripe_payments_webhooks_lock", array(), $lifetime = 60);
    }

    public function unlock()
    {
        $this->cache->remove("stripe_payments_webhooks_lock");
    }
}
