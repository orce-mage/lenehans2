<?php

namespace StripeIntegration\Payments\Helper;

use StripeIntegration\Payments\Helper\Logger;
use Magento\Framework\Exception\CouldNotSaveException;

class SubscriptionSwitch
{
    public $couponCodes = [];
    public $subscriptions = [];
    public $invoices = [];
    public $paymentIntents = [];

    protected $transaction = null;

    public function __construct(
        \StripeIntegration\Payments\Helper\Rollback $rollback,
        \StripeIntegration\Payments\Helper\Generic $paymentsHelper,
        \StripeIntegration\Payments\Model\Config $config,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \StripeIntegration\Payments\Helper\RecurringOrder $recurringOrder,
        \StripeIntegration\Payments\Model\PaymentIntent $paymentIntent,
        \StripeIntegration\Payments\Model\SubscriptionFactory $subscriptionFactory
    ) {
        $this->rollback = $rollback;
        $this->paymentsHelper = $paymentsHelper;
        $this->config = $config;
        $this->customer = $paymentsHelper->getCustomerModel();
        $this->transactionFactory = $transactionFactory;
        $this->recurringOrder = $recurringOrder;
        $this->paymentIntent = $paymentIntent;
        $this->subscriptionFactory = $subscriptionFactory;
    }

    // This is called once, it loads all subscriptions from all configured Stripe accounts
    protected function initForOrder($order)
    {
        $store = $order->getStore();
        $storeId = $store->getId();

        if (!empty($this->subscriptions[$storeId]))
            return;

        $mode = $this->config->getConfigData("mode", "basic", $storeId);
        $currency = $store->getDefaultCurrency()->getCurrencyCode();

        if (!$this->config->reInitStripe($storeId, $currency, $mode))
            throw new \Exception("Order #" . $order->getIncrementId() . " could not be migrated because Stripe could not be initialized for store " . $store->getName() . " ($mode mode)");

        $params = ['limit' => 100];
        $customerId = $order->getPayment()->getAdditionalInformation("customer_stripe_id");
        if (!empty($customer))
            $params["customer"] = $customerId;

        $subscriptions = \StripeIntegration\Payments\Model\Config::$stripeClient->subscriptions->all($params);

        foreach ($subscriptions->autoPagingIterator() as $key => $subscription)
        {
            if (!isset($subscription->metadata->{"Order #"}))
                continue;

            if (!isset($subscription->metadata->{"Product ID"}))
                continue;

            $productIDs = explode(",", $subscription->metadata->{"Product ID"});
            foreach ($productIDs as $productID)
                $this->subscriptions[$storeId][$subscription->metadata->{"Order #"}][$productID] = $subscription;
        }
    }

    public function run($order, $fromProduct, $toProduct)
    {
        $this->initForOrder($order);

        if (!$order->getId())
            throw new \Exception("Invalid subscription order specified");

        if (!$fromProduct->getId() || !$toProduct->getId())
            throw new \Exception("Invalid subscription product specified");

        if (!$fromProduct->getStripeSubEnabled())
            throw new \Exception($this->fromProduct->getName() . " is not a subscription product");

        if (!$toProduct->getStripeSubEnabled())
            throw new \Exception($this->toProduct->getName() . " is not a subscription product");

        if (!$this->isSubscriptionActive($order->getStore()->getId(), $order->getIncrementId(), $fromProduct->getId()))
            return false;

        try
        {
            $this->transaction = $this->transactionFactory->create();
            $this->rollback->reset();
            $newOrder = $this->beginMigration($order, $fromProduct, $toProduct);
            $this->transaction->save();
            $this->rollback->reset();

            // Now cancel the old subscription
            try
            {
                $subscription = $this->subscriptions[$order->getStore()->getId()][$order->getIncrementId()][$fromProduct->getId()];
                $this->subscriptionFactory->create()->cancel($subscription->id);
            }
            catch (\Exception $e)
            {
                throw new \Exception("A new order #{$newOrder->getIncrementId()} was created successfully but we could not cancel the old subscription with ID {$subscription->id}: " . $e->getMessage());
            }

            return true;
        }
        catch (\Exception $e)
        {
            $this->rollback->run($e);
            throw $e;
        }
    }

    protected function beginMigration($originalOrder, $fromProduct, $toProduct)
    {
        $subscription = $this->subscriptions[$originalOrder->getStore()->getId()][$originalOrder->getIncrementId()][$fromProduct->getId()];
        $customer = \StripeIntegration\Payments\Model\Config::$stripeClient->customers->retrieve($subscription->customer, []);
        $this->customer->loadFromData($subscription->customer, $customer);
        $trialEnd = $subscription->current_period_end - time();

        $quote = $this->recurringOrder->createQuoteFrom($originalOrder);
        $quote->setIsRecurringOrder(false)->setRemoveInitialFee(true);
        $this->recurringOrder->setQuoteCustomerFrom($originalOrder, $quote);
        $this->recurringOrder->setQuoteAddressesFrom($originalOrder, $quote);
        $quote->addProduct($toProduct, $subscription->quantity);
        $this->recurringOrder->setQuoteShippingMethodFrom($originalOrder, $quote);
        $this->recurringOrder->setQuoteDiscountFrom($originalOrder, $quote);
        $data = [
            'additional_data' => [
                'cc_stripejs_token' => $subscription->default_payment_method
            ]
        ];
        $this->recurringOrder->setQuotePaymentMethodFrom($originalOrder, $quote, $data);
        $quote->getPayment()
            ->setAdditionalInformation("is_recurring_subscription", false)
            ->setAdditionalInformation("is_migrated_subscription", false)
            ->setAdditionalInformation("subscription_customer", $subscription->customer)
            ->setAdditionalInformation("subscription_start", $subscription->current_period_end)
            ->setAdditionalInformation("remove_initial_fee", true)
            ->setAdditionalInformation("off_session", true);

        // Collect Totals & Save Quote
        $quote->collectTotals()->save();

        // Create Order From Quote
        $this->paymentIntent->quote = $quote;
        $order = $this->recurringOrder->quoteManagement->submit($quote);

        // The new subscription ID is saved in the transaction ID
        $newSubscription = $this->findCustomerSubscription($subscription->customer, $order->getIncrementId());
        if ($newSubscription)
            $this->setTransactionDetailsFor($order, $newSubscription->id);

        // Notify the customer about the billing changes
        if (false) // @todo: This should be a setting
        {
            $comment = __("Your subscription details have changed for order #%1. A new order #%2 has been created with the new billing details. This message does not mean that your subscription has been billed. The next subscription payment will be on %3.", $originalOrder->getIncrementId(), $order->getIncrementId(), date("jS F Y"));
            $this->paymentsHelper->sendNewOrderEmailWithComment($order, $comment);
        }

        // Cancel the newly created order
        $this->cancel($order);

        // Depreciate the old order
        $comment = __("The billing details for a subscription on this order have changed. Please see order #%1 for information on the new billing details.", $order->getIncrementId());
        $originalOrder->addStatusToHistory($status = false, $comment, $isCustomerNotified = false);
        $this->transaction->addObject($originalOrder);

        return $order;
    }

    protected function cancel($order)
    {
        // No invoices have been created
        if ($order->canCancel())
        {
            $comment = __("This order has been automatically canceled because no payment has been collected for it. It can only be used as a billing details reference for the subscription items in the order. The subscription is still active and a new order will be created when it renews.");
            $order->addStatusToHistory($status = \Magento\Sales\Model\Order::STATE_CANCELED, $comment, $isCustomerNotified = false);
        }
        // Invoices exist
        else
        {
            $comment = __("This order will be automatically closed because no payment has been collected for it. It can only be used as a billing details reference for the subscription items in the order. The subscription is still active and a new order will be created when it renews.");
            $order->addStatusToHistory($status = false, $comment, $isCustomerNotified = false);
            $this->paymentsHelper->cancelOrCloseOrder($order, true);
        }
    }

    protected function findCustomerSubscription($customerId, $orderId)
    {
        $customer = \StripeIntegration\Payments\Model\Config::$stripeClient->customers->retrieve($customerId, []);

        foreach ($customer->subscriptions->data as $subscription)
        {
            if ($subscription->metadata->{"Order #"} == $orderId)
                return $subscription;
        }

        return null;
    }

    protected function setTransactionDetailsFor($order, $transactionId)
    {
        $order->getPayment()
            ->setLastTransId($transactionId)
            ->setIsTransactionClosed(0)
            ->setIsTransactionPending(true);

        $this->transaction->addObject($order);
    }

    protected function isSubscriptionActive($storeId, $orderIncrementId, $productId)
    {
        if (!isset($this->subscriptions[$storeId][$orderIncrementId][$productId]))
            return false;

        $count = count($this->subscriptions[$storeId][$orderIncrementId]);
        if ($count > 1)
            throw new \Exception("The order includes multiple subscriptions.");

        $subscription = $this->subscriptions[$storeId][$orderIncrementId][$productId];

        if ($subscription->status == "active" || $subscription->status == "trialing")
            return true;

        return false;
    }
}
