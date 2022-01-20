<?php

namespace StripeIntegration\Payments\Test\Integration\Frontend\CheckoutPage\AutomaticPaymentMethods\AuthorizeCapture\MixedTrial;

class RefundTest extends \PHPUnit\Framework\TestCase
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

        // Trigger webhooks charge.succeeded & payment_intent.succeeded & invoice.payment_succeeded
        $customerId = $session->customer;
        $customer = $this->tests->stripe()->customers->retrieve($customerId);
        $subscription = $customer->subscriptions->data[0];
        $this->tests->event()->triggerSubscriptionEvents($subscription, $this);

        // Activate the subscription
        $ordersCount = $this->tests->getOrdersCount();
        $this->tests->endTrialSubscription($customer->subscriptions->data[0]->id);
        $newOrdersCount = $this->tests->getOrdersCount();
        $this->assertEquals($ordersCount + 1, $newOrdersCount);

        // Refund the order
        $order = $this->tests->refreshOrder($order);
        $invoices = $order->getInvoiceCollection();
        foreach ($invoices as $invoice)
        {
            $skus = [];
            $this->assertStringContainsString("pi_", $invoice->getTransactionId());

            foreach ($invoice->getAllItems() as $invoiceItem)
                $skus = [$invoiceItem->getSku() => $invoiceItem->getQty()];

            if ($invoiceItem->getSku() == "simple-product")
                $baseShippingAmount = 5;
            else
                $baseShippingAmount = null;

            $this->tests->refundOnline($invoice, $skus, $baseShippingAmount);
        }

        // Stripe checks
        $customer = $this->tests->stripe()->customers->retrieve($customerId);
        $invoice = $this->tests->stripe()->invoices->retrieve($customer->subscriptions->data[0]->latest_invoice, ['expand' => ['payment_intent']]);
        $this->tests->compare($invoice, [
            "payment_intent" => [
                "charges" => [
                    "data" => [
                        0 => [
                            "amount_refunded" => 1346
                        ]
                    ]
                ]
            ]
        ]);
    }
}
