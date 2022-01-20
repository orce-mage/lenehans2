<?php

namespace StripeIntegration\Payments\Test\Integration\Frontend\PRAPI\Normal;

class PlaceOrderTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->tests = new \StripeIntegration\Payments\Test\Integration\Helper\Tests($this);
        $this->request = $this->objectManager->get(\Magento\Framework\App\Request\Http::class);
        $this->helper = $this->objectManager->get(\StripeIntegration\Payments\Helper\Generic::class);
        $this->apiService = $this->objectManager->get(\StripeIntegration\Payments\Api\Service::class);
        $this->stripeConfig = $this->objectManager->get(\StripeIntegration\Payments\Model\Config::class);
        $this->session = $this->objectManager->get(\Magento\Checkout\Model\Session::class);
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
    public function testAddresses($shippingAddress, $billingAddress, $payerDetails)
    {
        $product = $this->helper->loadProductBySku("simple-product");
        $request = http_build_query([
            "product" => $product->getId(),
            "related_product" => "",
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

        $stripe = $this->stripeConfig->getStripeClient();
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

        // Load the payment intent
        $paymentIntentId = $order->getPayment()->getLastTransId();
        $this->assertNotEmpty($paymentIntentId);
        $paymentIntent = $this->stripeConfig->getStripeClient()->paymentIntents->retrieve($paymentIntentId);

        // Stripe checks
        $this->assertEquals($order->getGrandTotal() * 100, $paymentIntent->amount);
        $this->assertCount(1, $paymentIntent->charges->data);
        $this->assertEquals(1584, $paymentIntent->charges->data[0]->amount);
        $this->assertEquals("succeeded", $paymentIntent->charges->data[0]->status);
        $this->assertEquals("Order #$orderIncrementId by Jerry Flint", $paymentIntent->description);
        $this->assertEquals($orderIncrementId, $paymentIntent->metadata->{"Order #"});

        // Trigger webhook events
        // $this->tests->event()->triggerPaymentIntentEvents($paymentIntent, $this);

        // Stripe checks
        // $paymentIntent = $this->stripeConfig->getStripeClient()->paymentIntents->retrieve($paymentIntentId);

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
            ],
            // Shipping address is missing the region
            // [
            //     "shippingAddress" => [
            //         "line1" => '1255 Duncan Avenue',
            //         "line2" => null,
            //         "country" => "US",
            //         "postalCode" => "10013",
            //         "region" => null,
            //         "city" => "New York",
            //         "phone" => "917-535-4022"
            //     ],
            //     "billingAddress" => [
            //         "line1" => '1255 Duncan Avenue',
            //         "line2" => null,
            //         "country" => "US",
            //         "postalCode" => "10013",
            //         "region" => "New York",
            //         "city" => "New York",
            //         "phone" => "917-535-4022"
            //     ],
            //     "payerDetails" => [
            //         'email' => 'jerryflint@example.com',
            //         'name' => 'Jerry Flint',
            //         'phone' => "917-535-4022"
            //     ]
            // ],
        ];

        return $data;
    }
}
