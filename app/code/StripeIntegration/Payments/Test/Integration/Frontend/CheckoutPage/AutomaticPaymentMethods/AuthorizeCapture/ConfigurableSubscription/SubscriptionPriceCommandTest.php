<?php

namespace StripeIntegration\Payments\Test\Integration\Frontend\CheckoutPage\AutomaticPaymentMethods\AuthorizeCapture\ConfigurableSubscription;

class SubscriptionPriceCommandTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->helper = $this->objectManager->get(\StripeIntegration\Payments\Helper\Generic::class);
        $this->tests = new \StripeIntegration\Payments\Test\Integration\Helper\Tests($this);
        $this->quote = new \StripeIntegration\Payments\Test\Integration\Helper\Quote();
        $this->compare = new \StripeIntegration\Payments\Test\Integration\Helper\Compare($this);

        $this->subscriptionPriceCommand = $this->objectManager->get(\StripeIntegration\Payments\Setup\Migrate\SubscriptionPriceCommand::class);
        $this->apiService = $this->objectManager->get(\StripeIntegration\Payments\Api\Service::class);
    }

    /**
     * @magentoConfigFixture current_store payment/stripe_payments/active 1
     * @magentoConfigFixture current_store payment/stripe_payments_basic/stripe_mode test
     * @magentoConfigFixture current_store payment/stripe_payments/payment_flow 1
     *
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ApiKeys.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Taxes.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Products.php
     * @dataProvider addressesProvider
     */
    public function testSubscriptionMigration($shippingAddress, $billingAddress, $payerDetails)
    {
        $monthlySubscriptionProduct = $this->helper->loadProductBySku("simple-monthly-subscription-product");

        $this->quote->create()
            ->setCustomer('Guest')
            ->setCart("ConfigurableSubscription")
            ->setShippingAddress($shippingAddress)
            ->setShippingMethod("FlatRate")
            ->setBillingAddress($billingAddress)
            ->setPaymentMethod("StripeCheckout");

        $order = $this->quote->placeOrder();

        // Confirm the payment
        $method = "card";
        $session = $this->tests->checkout()->retrieveSession($order);
        $response = $this->tests->checkout()->confirm($session, $order, $method, $billingAddress);
        $this->tests->checkout()->authenticate($response->payment_intent, $method);
        $paymentIntent = $this->tests->stripe()->paymentIntents->retrieve($response->payment_intent->id);

        // Assert order status, amount due, invoices
        $this->assertEquals("new", $order->getState());
        $this->assertEquals("pending", $order->getStatus());
        $this->assertEquals($session->amount_total / 100, round($order->getGrandTotal(), 2));
        $this->assertEquals($session->amount_total / 100, round($order->getTotalDue(), 2));
        $this->assertEquals(0, $order->getInvoiceCollection()->count());

        // Stripe checks
        $customerId = $session->customer;
        $customer = $this->tests->stripe()->customers->retrieve($customerId);
        $this->assertCount(1, $customer->subscriptions->data);

        $ordersCount = $this->tests->getOrdersCount();

        // Trigger webhooks
        // 1. customer.created
        // 2. payment_method.attached
        // 3. payment_intent.created
        // 4. customer.updated
        // 5. invoice.created
        // 6. invoice.finalized
        // 7. customer.subscription.created
        // 8 & 9 & 10. charge.succeeded & payment_intent.succeeded & invoice.payment_succeeded
        $subscription = $customer->subscriptions->data[0];
        $this->tests->event()->triggerSubscriptionEvents($subscription, $this);
        // 11. invoice.updated
        // 12. checkout.session.completed
        // 13. customer.subscription.updated
        // 14. invoice.paid

        // Ensure that no new order was created
        $newOrdersCount = $this->tests->getOrdersCount();
        $this->assertEquals($ordersCount, $newOrdersCount);

        // Refresh the order object
        $order = $this->tests->refreshOrder($order);

        // Assert order status, amount due, invoices, invoice items, invoice totals
        $this->assertEquals($session->amount_total / 100, round($order->getGrandTotal(), 2));
        $this->assertEquals(0, $order->getTotalDue());
        $this->assertEquals($session->amount_total / 100, round($order->getTotalPaid(), 2));
        $this->assertEquals(1, $order->getInvoiceCollection()->count());
        $this->assertEquals("processing", $order->getState());
        $this->assertEquals("processing", $order->getStatus());

        // Reset
        $this->helper->clearCache();

        // Change the subscription price
        $monthlySubscriptionProduct->setPrice(15);
        $monthlySubscriptionProduct = $this->tests->saveProduct($monthlySubscriptionProduct);
        $productId = $monthlySubscriptionProduct->getId();

        // Migrate the existing subscription to the new price
        $inputFactory = $this->objectManager->get(\Symfony\Component\Console\Input\ArgvInputFactory::class);
        $input = $inputFactory->create([
            "argv" => [
                null,
                $productId,
                $productId,
                $order->getId(),
                $order->getId()
            ]
        ]);
        $output = $this->objectManager->get(\Symfony\Component\Console\Output\ConsoleOutput::class);

        $orderCount = $this->tests->getOrdersCount();

        $this->subscriptionPriceCommand->run($input, $output);

        // Ensure that a new order was created
        $newOrderCount = $this->tests->getOrdersCount();
        $this->assertEquals($orderCount + 1, $newOrderCount);

        // Stripe checks
        $customer = $this->tests->stripe()->customers->retrieve($customerId);
        $this->assertCount(1, $customer->subscriptions->data);

        // Trigger webhooks
        $subscription = $customer->subscriptions->data[0];
        $this->tests->event()->triggerSubscriptionEvents($subscription, $this);

        // Stripe checks
        $this->assertNotEmpty($customer->subscriptions->data[0]->latest_invoice);
        $invoice = $this->tests->stripe()->invoices->retrieve($customer->subscriptions->data[0]->latest_invoice);
        $this->compare->object($customer->subscriptions->data[0], [
            "default_tax_rates" => [
                0 => [
                    "description" => "8.25% VAT",
                    "percentage" => "8.25",
                    "inclusive" => false
                ]
            ],
            "items" => [
                "data" => [
                    0 => [
                        "plan" => [
                            "amount" => "1500",
                            "currency" => "usd",
                            "interval" => "month",
                            "interval_count" => 1
                        ],
                        "price" => [
                            "recurring" => [
                                "interval" => "month",
                                "interval_count" => 1
                            ],
                            "unit_amount" => "1500"
                        ],
                        "quantity" => 1
                    ]
                ]
            ],
            "metadata" => [
                "Product ID" => $productId
                // "Order #" => $order->getIncrementId()
            ],
            "status" => "trialing",
            "tax_percent" => "8.25"
        ]);
        // All should be zero because it is a trial subscription
        $this->compare->object($invoice, [
            "amount_due" => 0,
            "amount_paid" => 0,
            "amount_remaining" => 0,
            "tax" => 0,
            "total" => 0
        ]);

        $upcomingInvoice = $this->tests->stripe()->invoices->upcoming(['customer' => $customer->id]);
        $this->assertCount(2, $upcomingInvoice->lines->data);
        $this->compare->object($upcomingInvoice, [
            "tax" => 124,
            "total" => 2124
        ]);
    }

    public function addressesProvider()
    {
        $data = [
            // Full address
            [
                "shippingAddress" => "California",
                "billingAddress" => "California",
                "payerDetails" => [
                    'email' => 'jerryflint@example.com',
                    'name' => 'Jerry Flint',
                    'phone' => "917-535-4022"
                ]
            ]
        ];

        return $data;
    }
}
