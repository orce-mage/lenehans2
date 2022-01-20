<?php

namespace StripeIntegration\Payments\Test\Integration\Frontend\CheckoutPage\AutomaticPaymentMethods\AuthorizeCapture\MixedTrial;

class PlaceOrderTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->tests = new \StripeIntegration\Payments\Test\Integration\Helper\Tests($this);
        $this->quote = new \StripeIntegration\Payments\Test\Integration\Helper\Quote();
    }

    /**
     * @magentoConfigFixture current_store payment/stripe_payments/active 1
     * @magentoConfigFixture current_store payment/stripe_payments_basic/stripe_mode test
     * @magentoConfigFixture current_store payment/stripe_payments/payment_flow 1
     *
     * @magentoConfigFixture current_store currency/options/base USD
     * @magentoConfigFixture current_store currency/options/allow EUR,USD
     * @magentoConfigFixture current_store currency/options/default EUR
     *
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ApiKeys.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Taxes.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ExchangeRates.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Products.php
     */
    public function testPlaceOrder()
    {
        $this->quote->create()
            ->setCustomer('Guest')
            ->setCart("MixedTrial")
            ->setShippingAddress("NewYork")
            ->setShippingMethod("FlatRate")
            ->setBillingAddress("NewYork")
            ->setPaymentMethod("StripeCheckout");

        $order = $this->quote->placeOrder();
        $orderIncrementId = $order->getIncrementId();

        // Confirm the payment
        $method = "card";
        $session = $this->tests->checkout()->retrieveSession($order, "MixedTrial");
        $response = $this->tests->checkout()->confirm($session, $order, $method, "NewYork");
        $this->tests->checkout()->authenticate($response->payment_intent, $method);
        $paymentIntent = $this->tests->stripe()->paymentIntents->retrieve($response->payment_intent->id);

        // Assert order status, amount due, invoices
        $this->assertEquals("new", $order->getState());
        $this->assertEquals("pending", $order->getStatus());
        $this->assertEquals(0, $order->getInvoiceCollection()->count());

        // Stripe checks
        $customerId = $session->customer;
        $customer = $this->tests->stripe()->customers->retrieve($customerId);
        $this->assertCount(1, $customer->subscriptions->data);
        $this->tests->compare($customer->subscriptions->data[0], [
            "status" => "trialing",
            "plan" => [
                "amount" => 1346
            ]
        ]);

        // Trigger webhooks charge.succeeded & payment_intent.succeeded & invoice.payment_succeeded
        $ordersCount = $this->tests->getOrdersCount();
        $subscription = $customer->subscriptions->data[0];
        $this->tests->event()->triggerSubscriptionEvents($subscription, $this);

        // Ensure that no new order was created
        $newOrdersCount = $this->tests->getOrdersCount();
        $this->assertEquals($ordersCount, $newOrdersCount);

        // Refresh the order object
        $order = $this->tests->refreshOrder($order);

        // Assert order status, amount due
        $this->assertEquals($session->amount_total / 100, round($order->getTotalPaid(), 2));
        $this->assertEquals($order->getGrandTotal() - $order->getTotalPaid(), $order->getTotalDue());
        $this->assertEquals("processing", $order->getState());
        $this->assertEquals("processing", $order->getStatus());

        // Assert Magento invoice, invoice items, invoice totals
        $this->assertEquals(1, $order->getInvoiceCollection()->count());
        $invoice = $order->getInvoiceCollection()->getFirstItem();
        $this->assertEquals($order->getTotalPaid(), $invoice->getGrandTotal());

        // Shipping should be 4.25 and 5, but Magento force uses the full order shipping amount on partial invoices
        // https://github.com/magento/magento2/issues/26286
        $this->assertEquals(8.5, $invoice->getShippingAmount());
        $this->assertEquals(10, $invoice->getBaseShippingAmount());

        // Stripe checks
        $trialSubscriptionAmount = ($order->getGrandTotal() - $order->getTotalPaid()) * 100;
        $this->assertNotEmpty($customer->subscriptions->data[0]->latest_invoice);

        $upcomingInvoice = $this->tests->stripe()->invoices->upcoming(['customer' => $customer->id]);
        $this->assertCount(1, $upcomingInvoice->lines->data);
        $this->tests->compare($upcomingInvoice, [
            "tax" => 0,
            "total" => $trialSubscriptionAmount
        ]);

        // Activate the subscription
        $ordersCount = $this->tests->getOrdersCount();
        $this->tests->endTrialSubscription($customer->subscriptions->data[0]->id);
        $newOrdersCount = $this->tests->getOrdersCount();
        $this->assertEquals($ordersCount + 1, $newOrdersCount);

        // Order checks
        $order = $this->tests->refreshOrder($order);
        $this->assertEquals($order->getGrandTotal(), $order->getTotalPaid());
        $this->assertEquals(1, $order->getInvoiceCollection()->count());
    }
}
