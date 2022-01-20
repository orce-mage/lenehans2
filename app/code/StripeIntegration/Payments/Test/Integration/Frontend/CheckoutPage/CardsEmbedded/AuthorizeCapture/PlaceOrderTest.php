<?php

namespace StripeIntegration\Payments\Test\Integration\Frontend\CheckoutPage\CardsEmbedded\AuthorizeCapture;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\SessionFactory as CheckoutSessionFactory;
use PHPUnit\Framework\Constraint\StringContains;

class PlaceOrderTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->tests = new \StripeIntegration\Payments\Test\Integration\Helper\Tests($this);

        $this->checkoutSession = $this->objectManager->get(CheckoutSessionFactory::class)->create();
        $this->transportBuilder = $this->objectManager->get(\Magento\TestFramework\Mail\Template\TransportBuilderMock::class);
        $this->eventManager = $this->objectManager->get(\Magento\Framework\Event\ManagerInterface::class);
        $this->orderSender = $this->objectManager->get(\Magento\Sales\Model\Order\Email\Sender\OrderSender::class);
        $this->helper = $this->objectManager->get(\StripeIntegration\Payments\Helper\Generic::class);
        $this->stripeConfig = $this->objectManager->get(\StripeIntegration\Payments\Model\Config::class);
        $this->quote = new \StripeIntegration\Payments\Test\Integration\Helper\Quote();
        $this->orderRepository = $this->objectManager->get(\Magento\Sales\Api\OrderRepositoryInterface::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store payment/stripe_payments/active 1
     * @magentoConfigFixture current_store payment/stripe_payments_basic/stripe_mode test
     * @magentoConfigFixture current_store payment/stripe_payments/payment_flow 0
     * @magentoConfigFixture current_store payment/stripe_payments/payment_action authorize_capture
     *
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ApiKeys.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Taxes.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Addresses.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Products.php
     */
    public function testNormalCart()
    {
        $this->quote->create()
            ->setCustomer('Guest')
            ->setCart("Normal")
            ->setShippingAddress("California")
            ->setShippingMethod("FlatRate")
            ->setBillingAddress("California")
            ->setPaymentMethod("SuccessCard");

        $order = $this->quote->placeOrder();

        // Refresh the order object
        $order = $this->tests->reloadOrder($order);

        $invoicesCollection = $order->getInvoiceCollection();

        $this->assertEquals("processing", $order->getState());
        $this->assertEquals("processing", $order->getStatus());
        $this->assertNotEmpty($invoicesCollection);
        $this->assertEquals(1, $invoicesCollection->count());

        $invoice = $invoicesCollection->getFirstItem();

        $this->assertEquals(2, count($invoice->getAllItems()));
        $this->assertEquals(\Magento\Sales\Model\Order\Invoice::STATE_PAID, $invoice->getState());

        $transactions = $this->helper->getOrderTransactions($order);
        $this->assertEquals(1, count($transactions));
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store payment/stripe_payments/active 1
     * @magentoConfigFixture current_store payment/stripe_payments_basic/stripe_mode test
     * @magentoConfigFixture current_store payment/stripe_payments/payment_flow 0
     * @magentoConfigFixture current_store payment/stripe_payments/payment_action authorize_capture
     *
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ApiKeys.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Taxes.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Addresses.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Products.php
     */
    public function testMixedCart()
    {
        $this->quote->create()
            ->setCustomer('Guest')
            ->setCart("Mixed")
            ->setShippingAddress("California")
            ->setShippingMethod("FlatRate")
            ->setBillingAddress("California")
            ->setPaymentMethod("SuccessCard");

        $order = $this->quote->placeOrder();

        $invoicesCollection = $order->getInvoiceCollection();
        $this->assertEquals("processing", $order->getState());
        $this->assertEquals("processing", $order->getStatus());
        $this->assertNotEmpty($invoicesCollection);
        $this->assertEquals(1, $invoicesCollection->count());

        $stripe = $this->stripeConfig->getStripeClient();

        $customerId = $order->getPayment()->getAdditionalInformation("customer_stripe_id");
        $customer = $stripe->customers->retrieve($customerId);
        $this->assertEquals(1, count($customer->subscriptions->data));
        $subscription = $customer->subscriptions->data[0];
        $this->assertNotEmpty($subscription->latest_invoice);
        $invoiceId = $subscription->latest_invoice;

        // Process the subscription's charge.succeeded event
        $invoice = $stripe->invoices->retrieve($invoiceId, ['expand' => ['charge']]);
        $subscriptionPaymentIntentId = $invoice->charge->payment_intent;
        $this->tests->event()->trigger("charge.succeeded", $invoice->charge, $this);

        // Process the subscription's invoice.payment_succeeded event
        $ordersCount = $this->objectManager->get('Magento\Sales\Model\Order')->getCollection()->count();
        $this->tests->event()->trigger("invoice.payment_succeeded", $invoice, $this);

        // Process the regular products charge.succeeded event
        $paymentIntentId = $order->getPayment()->getLastTransId();
        $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId);
        $charge =  $paymentIntent->charges->data[0];
        $this->assertNotEquals($charge->id, $invoice->charge->id);
        $this->tests->event()->trigger("charge.succeeded", $charge, $this);

        // Ensure that no new order was created
        $newOrdersCount = $this->objectManager->get('Magento\Sales\Model\Order')->getCollection()->count();
        $this->assertEquals($ordersCount, $newOrdersCount);

        // Refresh the order object
        $order = $this->tests->reloadOrder($order);

        $invoicesCollection = $order->getInvoiceCollection();

        $this->assertEquals("processing", $order->getState());
        $this->assertEquals("processing", $order->getStatus());
        $this->assertNotEmpty($invoicesCollection);
        $this->assertEquals(1, $invoicesCollection->count());

        $invoice = $invoicesCollection->getFirstItem();

        $this->assertEquals(2, count($invoice->getAllItems()));
        $this->assertEquals(\Magento\Sales\Model\Order\Invoice::STATE_PAID, $invoice->getState());
        $this->assertEquals($paymentIntentId, $invoice->getTransactionId());

        // Check that the transaction IDs have been associated with the order
        $transactions = $this->helper->getOrderTransactions($order);
        $this->assertEquals(2, count($transactions));
        foreach ($transactions as $key => $transaction)
        {
            if ($transaction->getTxnId() == $subscriptionPaymentIntentId)
            {
                $this->assertEquals("capture", $transaction->getTxnType());
                $this->assertTrue($transaction->getAdditionalInformation("is_subscription"));
                $this->assertEquals(38.15, $transaction->getAdditionalInformation("amount"));
            }
            else
            {
                $this->assertEquals($paymentIntentId, $transaction->getTxnId());
                $this->assertEquals("capture", $transaction->getTxnType());
                $this->assertFalse($transaction->getAdditionalInformation("is_subscription"));
                $this->assertEquals(31.65, $transaction->getAdditionalInformation("amount"));
            }
        }

        // Partially refund the non-subscription items of the invoice
        $this->tests->refundOnline($invoice, ["simple-product" => 2], $baseShipping = 10);

        // Refresh the order object
        $order = $this->tests->reloadOrder($order);
        $this->assertEquals("processing", $order->getState());
        $this->assertEquals("processing", $order->getStatus());
        $this->assertEquals(31.65, $order->getTotalRefunded());

        $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId);
        $this->assertEquals(3165, $paymentIntent->charges->data[0]->amount);
        $this->assertEquals(3165, $paymentIntent->charges->data[0]->amount_captured);
        $this->assertEquals(3165, $paymentIntent->charges->data[0]->amount_refunded);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store payment/stripe_payments/active 1
     * @magentoConfigFixture current_store payment/stripe_payments_basic/stripe_mode test
     * @magentoConfigFixture current_store payment/stripe_payments/payment_flow 0
     * @magentoConfigFixture current_store payment/stripe_payments/payment_action authorize_capture
     *
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ApiKeys.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Taxes.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Addresses.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Products.php
     */
    public function testZeroAmountCart()
    {
        $this->quote->create()
            ->setCustomer('Guest')
            ->setCart("ZeroAmount")
            ->setShippingAddress("California")
            ->setShippingMethod("FlatRate")
            ->setBillingAddress("California")
            ->setPaymentMethod("SuccessCard");

        $order = $this->quote->placeOrder();

        $stripe = $this->stripeConfig->getStripeClient();

        $customerId = $order->getPayment()->getAdditionalInformation("customer_stripe_id");
        $customer = $stripe->customers->retrieve($customerId);
        $this->assertEquals(1, count($customer->subscriptions->data));
        $subscription = $customer->subscriptions->data[0];
        $this->assertNotEmpty($subscription->latest_invoice);
        $invoiceId = $subscription->latest_invoice;

        // Get the current orders count
        $ordersCount = $this->objectManager->get('Magento\Sales\Model\Order')->getCollection()->count();

        // Process the subscription's invoice.payment_succeeded event
        $invoice = $stripe->invoices->retrieve($invoiceId, ['expand' => ['charge']]);
        $this->assertNotEmpty($invoice->subscription);
        $subscriptionId = $invoice->subscription;
        $this->assertEmpty($invoice->charge);
        $this->assertEquals(0, $invoice->amount_due);
        $this->assertEquals(0, $invoice->amount_paid);
        $this->assertEquals(0, $invoice->amount_remaining);

        $this->tests->event()->trigger("invoice.payment_succeeded", $invoice, $this);

        // Ensure that no new order was created
        $newOrdersCount = $this->objectManager->get('Magento\Sales\Model\Order')->getCollection()->count();
        $this->assertEquals($ordersCount, $newOrdersCount);

        // Refresh the order object
        $order = $this->tests->reloadOrder($order);
        $this->assertEquals("complete", $order->getState());
        $this->assertEquals("complete", $order->getStatus());
        $this->assertEquals(0, $order->getTotalPaid());
        $this->assertEquals(10.83, $order->getTotalDue());

        // Check that an invoice was created
        $invoicesCollection = $order->getInvoiceCollection();
        $this->assertEquals(1, $invoicesCollection->count());

        // End the trial
        $stripe->subscriptions->update($subscriptionId, ['trial_end' => "now"]);
        $subscription = $stripe->subscriptions->retrieve($subscriptionId, ['expand' => ['latest_invoice']]);

        $ordersCount = $this->objectManager->get('Magento\Sales\Model\Order')->getCollection()->count();

        // Trigger webhook events for the trial end
        $this->tests->event()->triggerSubscriptionEvents($subscription, $this);

        // Refresh the order object
        $order = $this->tests->reloadOrder($order);

        // Check that an invoice was created
        $invoicesCollection = $order->getInvoiceCollection();
        $this->assertNotEmpty($invoicesCollection);
        $this->assertEquals(1, $invoicesCollection->count());

        $invoice = $invoicesCollection->getFirstItem();

        $this->assertEquals(2, count($invoice->getAllItems()));
        $this->assertEquals(\Magento\Sales\Model\Order\Invoice::STATE_PAID, $invoice->getState());
        $this->assertEquals("cannot_capture_subscriptions", $invoice->getTransactionId());
        $this->assertEquals(10.83, $order->getTotalPaid());
        $this->assertEquals(0, $order->getTotalDue());

        // Check that the transaction IDs have been associated with the order
        $transactions = $this->helper->getOrderTransactions($order);
        $this->assertEquals(1, count($transactions));
        foreach ($transactions as $key => $transaction)
        {
            $this->assertEquals($subscription->latest_invoice->payment_intent, $transaction->getTxnId());
            $this->assertEquals("capture", $transaction->getTxnType());
            $this->assertTrue($transaction->getAdditionalInformation("is_subscription"));
            $this->assertEquals(10.83, $transaction->getAdditionalInformation("amount"));
        }

        // Ensure that a new order was created
        $newOrdersCount = $this->objectManager->get('Magento\Sales\Model\Order')->getCollection()->count();
        $this->assertEquals($ordersCount + 1, $newOrdersCount);

        // Refresh the order object
        $order = $this->tests->reloadOrder($order);
        $this->assertEquals(10.83, $order->getTotalPaid());
        $this->assertEquals(0, $order->getTotalDue());

        // Check the newly created order
        $newOrder = $this->objectManager->get('Magento\Sales\Model\Order')->getCollection()->setOrder('increment_id','DESC')->getFirstItem();
        $this->assertNotEquals($order->getIncrementId(), $newOrder->getIncrementId());
        $this->assertEquals("complete", $newOrder->getState());
        $this->assertEquals("complete", $newOrder->getStatus());
        $this->assertEquals(10.83, $newOrder->getGrandTotal());
        $this->assertEquals(10.83, $newOrder->getTotalPaid());
        $this->assertEquals(1, $newOrder->getInvoiceCollection()->getSize());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store payment/stripe_payments/active 1
     * @magentoConfigFixture current_store payment/stripe_payments_basic/stripe_mode test
     * @magentoConfigFixture current_store payment/stripe_payments/payment_flow 0
     * @magentoConfigFixture current_store payment/stripe_payments/payment_action authorize_capture
     *
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ApiKeys.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Taxes.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Addresses.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Products.php
     */
    public function testTrialCartCheckoutTotals()
    {
        $this->quote->create()
            ->setCustomer('Guest')
            ->setCart("Trial")
            ->setShippingAddress("California")
            ->setShippingMethod("FlatRate")
            ->setBillingAddress("California")
            ->setPaymentMethod("SuccessCard");

        $order = $this->quote->mockOrder();

        $uiConfigProvider = $this->objectManager->get(\StripeIntegration\Payments\Model\Ui\ConfigProvider::class);
        $uiConfig = $uiConfigProvider->getConfig();
        $this->assertNotEmpty($uiConfig["payment"]["stripe_payments"]["trialingSubscriptions"]);
        $trialSubscriptionsConfig = $uiConfig["payment"]["stripe_payments"]["trialingSubscriptions"];

        $this->assertEquals($order->getSubtotal(), $trialSubscriptionsConfig["subscriptions_total"], "Subtotal");
        $this->assertEquals($order->getBaseSubtotal(), $trialSubscriptionsConfig["base_subscriptions_total"], "Base Subtotal");

        $this->assertEquals($order->getShippingAmount(), $trialSubscriptionsConfig["shipping_total"], "Shipping");
        $this->assertEquals($order->getBaseShippingAmount(), $trialSubscriptionsConfig["base_shipping_total"], "Base Shipping");

        $this->assertEquals($order->getDiscountAmount(), $trialSubscriptionsConfig["discount_total"], "Discount");
        $this->assertEquals($order->getBaseDiscountAmount(), $trialSubscriptionsConfig["base_discount_total"], "Base Discount");

        $this->assertEquals($order->getTaxAmount(), $trialSubscriptionsConfig["tax_total"], "Tax");
        $this->assertEquals($order->getBaseTaxAmount(), $trialSubscriptionsConfig["tax_total"], "Base Tax");
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store payment/stripe_payments/active 1
     * @magentoConfigFixture current_store payment/stripe_payments_basic/stripe_mode test
     * @magentoConfigFixture current_store payment/stripe_payments/payment_flow 0
     *
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ApiKeys.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Taxes.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Addresses.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Products.php
     */
    public function testNewOrderEmail()
    {
        $this->quote->create()
            ->setCustomer('Guest')
            ->setCart("Normal")
            ->setShippingAddress("California")
            ->setShippingMethod("FlatRate")
            ->setBillingAddress("California")
            ->setPaymentMethod("SuccessCard");

        $order = $this->quote->placeOrder();
        $quote = $this->quote->getQuote();

        if ($order)
        {
            $this->eventManager->dispatch(
                'checkout_type_onepage_save_order_after',
                ['order' => $order, 'quote' => $quote]
            );

            if ($order->getCanSendNewEmailFlag()) {
                $this->orderSender->send($order);
            }

            $this->checkoutSession
                ->setLastQuoteId($order->getQuoteId())
                ->setLastSuccessQuoteId($order->getQuoteId())
                ->setLastOrderId($order->getId())
                ->setLastRealOrderId($order->getIncrementId())
                ->setLastOrderStatus($order->getStatus());
        }

        $this->eventManager->dispatch(
            'checkout_submit_all_after',
            [
                'order' => $order,
                'quote' => $quote
            ]
        );

        $message = $this->transportBuilder->getSentMessage();
        $subject = __('Your %1 order confirmation', $order->getStore()->getFrontendName())->render();
        $assert = $this->logicalAnd(
            new StringContains($order->getBillingAddress()->getName()),
            new StringContains(
                'Thank you for your order from ' . $order->getStore()->getFrontendName()
            ),
            new StringContains(
                "Your Order <span class=\"no-link\">#{$order->getIncrementId()}</span>"
            )
        );

        $this->assertEquals($message->getSubject(), $subject);
        $this->assertThat($message->getBody()->getParts()[0]->getRawContent(), $assert);

        return $order;
    }
}
