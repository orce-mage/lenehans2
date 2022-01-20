<?php

namespace StripeIntegration\Payments\Test\Integration\Frontend\AutomaticPaymentMethods\AuthorizeCapture\Trial;

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
     * @magentoConfigFixture current_store payment/stripe_payments_checkout/payment_action authorize_capture
     *
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ApiKeys.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ExchangeRates.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Taxes.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Addresses.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Products.php
     */
    public function testPlaceOrder()
    {
        $this->quote->create()
            ->setCustomer('Guest')
            ->setCart("Trial")
            ->setBillingAddress("California")
            ->setShippingAddress("California")
            ->setShippingMethod("FlatRate")
            ->setPaymentMethod("StripeCheckout");

        // Place the order
        $order = $this->quote->placeOrder();
        $orderIncrementId = $order->getIncrementId();

        // Confirm the payment
        $method = "card";
        $session = $this->tests->checkout()->retrieveSession($order, "Trial");
        $response = $this->tests->checkout()->confirm($session, $order, $method, "California");

        // Wait until the subscription is creared and retrieve it
        $customerId = $response->customer->id;
        $wait = 5;
        do
        {
            $subscriptions = $this->tests->stripe()->subscriptions->all(['limit' => 3, 'customer' => $customerId]);
            if (count($subscriptions->data) > 0)
                break;
            sleep(1);
            $wait--;
        }
        while ($wait > 0);

        $this->assertCount(1, $subscriptions->data);
        $this->tests->compare($subscriptions->data[0], [
            "status" => "trialing",
            "plan" => [
                "amount" => 2666
            ],
            "metadata" => [
                "Order #" => $orderIncrementId
            ]
        ]);
        $this->assertNotEmpty($subscriptions->data[0]->metadata->{"Product ID"});

        $ordersCount = $this->tests->getOrdersCount();

        // Trigger charge.succeeded & payment_intent.succeeded & invoice.payment_succeeded
        $subscription = $subscriptions->data[0];
        $this->tests->event()->triggerSubscriptionEvents($subscription, $this);

        // Refresh the order
        $order = $this->tests->refreshOrder($order);

        $this->tests->compare($order->getData(), [
            "grand_total" => 26.66,
            "total_paid" => 0,
            "total_due" => 26.66,
            "state" => "new",
            "status" => "pending"
        ]);

        // End the trial
        $this->tests->endTrialSubscription($subscription->id);

        // Ensure that a new order was created
        $newOrdersCount = $this->tests->getOrdersCount();
        $this->assertEquals($ordersCount + 1, $newOrdersCount);

        // Refresh the order
        $order = $this->tests->refreshOrder($order);

        $this->tests->compare($order->getData(), [
            "grand_total" => 26.66,
            "total_paid" => 26.66,
            "total_due" => 0,
            "state" => "processing",
            "status" => "processing"
        ]);
    }
}
