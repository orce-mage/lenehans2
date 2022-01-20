<?php

namespace StripeIntegration\Payments\Test\Integration\Frontend\PRAPI\ConfigurableSubscription;

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
     * @magentoConfigFixture current_store payment/stripe_payments/payment_flow 0
     *
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ApiKeys.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Taxes.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Products.php
     * @dataProvider addressesProvider
     */
    public function testSubscriptionMigration($shippingAddress, $billingAddress, $payerDetails)
    {
        $monthlySubscriptionProduct = $this->helper->loadProductBySku("simple-monthly-subscription-product");
        $configurableSubscriptionProduct = $this->helper->loadProductBySku("configurable-subscription");
        $attributeId = $this->quote->getAttributeIdByAttributeCode("subscription");

        $request = http_build_query([
            "product" => $configurableSubscriptionProduct->getId(),
            "selected_configurable_option" => $monthlySubscriptionProduct->getId(),
            "related_product" => "",
            "item" => $configurableSubscriptionProduct->getId(),
            "super_attribute[$attributeId]" => "monthly",
            "qty" => 1
        ]);
        $result = $this->apiService->addtocart($request);
        $this->assertNotEmpty($result);

        $data = json_decode($result, true);
        $this->assertNotEmpty($data["results"]);

        $address = [
            "addressLine" => [$shippingAddress["line1"]],
            "country" => $shippingAddress["country"],
            "postalCode" => $shippingAddress["postalCode"],
            "recipient" => $payerDetails["name"],
            "region" => $shippingAddress["region"],
            "city" => $shippingAddress["city"],
            "phone" => $payerDetails["phone"],
            "sortingCode" => "",
            "dependentLocality" => "",
            "organization" => ""
        ];

        $result = $this->apiService->estimate_cart($address);
        $this->assertNotEmpty($result);

        $data = json_decode($result, true);
        $this->assertNotEmpty($data["results"]);

        $selectedShippingMethod = $data["results"][0];
        $result = $this->apiService->apply_shipping($address, $selectedShippingMethod["id"]);
        $this->assertNotEmpty($result);

        $data = json_decode($result, true);
        $this->assertNotEmpty($data["results"]["displayItems"]);

        $stripe = $this->tests->stripe();
        $paymentMethod = $stripe->paymentMethods->create([
          'type' => 'card',
          'card' => [
            'number' => '4242424242424242',
            'exp_month' => 7,
            'exp_year' => 2022,
            'cvc' => '314',
          ],
          'billing_details' => [
            'address' => [
                'city' => $address['city'],
                'country' => $address['country'],
                'line1' => $address['addressLine'][0],
                'postal_code' => $address['postalCode'],
                'state' => $address['region']
            ],
            'email' => $payerDetails["email"],
            'name' => $payerDetails["name"],
            'phone' => $payerDetails["phone"]
          ],
        ]);
        $this->assertNotEmpty($paymentMethod);
        $this->assertNotEmpty($paymentMethod->id);

        $result = [
            "payerEmail" => $payerDetails["email"],
            "payerName" => $payerDetails["name"],
            "payerPhone" => $payerDetails["phone"],
            "shippingAddress" => $address,
            "shippingOption" => $selectedShippingMethod,
            "paymentMethod" => $paymentMethod
        ];

        $result = $this->apiService->place_order($result, "product");
        $this->assertNotEmpty($result);

        $data = json_decode($result, true);
        $this->assertNotEmpty($data);
        $this->assertNotEmpty($data["redirect"]);
        $this->assertStringContainsString("checkout/onepage/success", $data["redirect"]);

        // Load the order
        $session = $this->objectManager->get(\Magento\Checkout\Model\Session::class);
        $this->assertNotEmpty($session->getLastRealOrderId());
        $orderIncrementId = $session->getLastRealOrderId();
        $order = $this->tests->getLastOrder();
        $this->assertEquals($orderIncrementId, $order->getIncrementId());

        // Load the customer
        $customerId = $order->getPayment()->getAdditionalInformation("customer_stripe_id");
        $customer = $this->tests->stripe()->customers->retrieve($customerId);
        $this->assertCount(1, $customer->subscriptions->data);

        // Stripe checks
        $subscription = $customer->subscriptions->data[0];
        $this->assertNotEmpty($subscription->latest_invoice);
        $invoice = $this->tests->stripe()->invoices->retrieve($subscription->latest_invoice);
        $this->compare->object($invoice, [
            "amount_due" => 1584,
            "amount_paid" => 1584,
            "amount_remaining" => 0,
            "paid" => 1,
            "status" => "paid",
            "subtotal" => 1500,
            "tax" => 84,
            "total" => 1584
        ]);
        $this->assertCount(2, $invoice->lines->data);

        // Trigger webhook events
        $this->tests->event()->triggerSubscriptionEvents($subscription, $this);


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
                    "description" => "8.375% VAT",
                    "percentage" => "8.375",
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
            "tax_percent" => "8.375"
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
            "tax" => 126,
            "total" => 2126
        ]);
    }

    public function addressesProvider()
    {
        $data = [
            // Full address
            [
                "shippingAddress" => [
                    "line1" => '1255 Duncan Avenue',
                    "line2" => null,
                    "country" => "US",
                    "postalCode" => "10013",
                    "region" => "New York",
                    "city" => "New York",
                    "phone" => "917-535-4022"
                ],
                "billingAddress" => [
                    "line1" => '1255 Duncan Avenue',
                    "line2" => null,
                    "country" => "US",
                    "postalCode" => "10013",
                    "region" => "New York",
                    "city" => "New York",
                    "phone" => "917-535-4022"
                ],
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
