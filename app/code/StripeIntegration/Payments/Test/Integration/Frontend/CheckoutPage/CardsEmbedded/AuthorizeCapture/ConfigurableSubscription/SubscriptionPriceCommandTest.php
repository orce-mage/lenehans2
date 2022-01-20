<?php

namespace StripeIntegration\Payments\Test\Integration\Frontend\CheckoutPage\CardsEmbedded\AuthorizeCapture\ConfigurableSubscription;

class SubscriptionPriceCommandTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->helper = $this->objectManager->get(\StripeIntegration\Payments\Helper\Generic::class);
        $this->tests = new \StripeIntegration\Payments\Test\Integration\Helper\Tests($this);
        $this->quote = new \StripeIntegration\Payments\Test\Integration\Helper\Quote();

        $this->stockRegistry = $this->objectManager->get(\Magento\CatalogInventory\Model\StockRegistry::class);
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->compare = new \StripeIntegration\Payments\Test\Integration\Helper\Compare($this);
        $this->subscriptionPriceCommand = $this->objectManager->get(\StripeIntegration\Payments\Setup\Migrate\SubscriptionPriceCommand::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store payment/stripe_payments/active 1
     * @magentoConfigFixture current_store payment/stripe_payments/payment_flow 0
     * @magentoConfigFixture current_store payment/stripe_payments_basic/stripe_mode test
     * @magentoConfigFixture current_store payment/stripe_payments/payment_action authorize_capture
     *
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ApiKeys.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Taxes.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Addresses.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Products.php
     */
    public function testSubscriptionMigration()
    {
        $this->quote->create()
            ->setCustomer('Guest')
            ->setCart("ConfigurableSubscription")
            ->setShippingAddress("California")
            ->setShippingMethod("FlatRate")
            ->setBillingAddress("California")
            ->setPaymentMethod("SuccessCard");

        $order = $this->quote->placeOrder();

        // Stripe checks
        $customerId = $order->getPayment()->getAdditionalInformation("customer_stripe_id");
        $customer = $this->tests->stripe()->customers->retrieve($customerId);
        $this->assertCount(1, $customer->subscriptions->data);
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
                            "amount" => "1000",
                            "currency" => "usd",
                            "interval" => "month",
                            "interval_count" => 1
                        ],
                        "price" => [
                            "recurring" => [
                                "interval" => "month",
                                "interval_count" => 1
                            ],
                            "unit_amount" => "1000"
                        ],
                        "quantity" => 1
                    ]
                ]
            ],
            "metadata" => [
                "Order #" => $order->getIncrementId()
            ],
            "status" => "active",
            "tax_percent" => "8.25"
        ]);
        $invoice = $this->tests->stripe()->invoices->retrieve($customer->subscriptions->data[0]->latest_invoice);
        $this->compare->object($invoice, [
            "amount_due" => 1583,
            "amount_paid" => 1583,
            "amount_remaining" => 0,
            "tax" => 83,
            "total" => 1583
        ]);

        // Trigger webhooks
        $subscription = $customer->subscriptions->data[0];
        $this->tests->event()->triggerSubscriptionEvents($subscription, $this);

        // Reset
        $this->helper->clearCache();

        // Change the subscription price
        $this->assertNotEmpty($customer->subscriptions->data[0]->metadata->{"Product ID"});
        $productId = $customer->subscriptions->data[0]->metadata->{"Product ID"};
        $product = $this->helper->loadProductById($productId);
        $productId = $product->getEntityId();
        $product->setPrice(15);
        $product = $this->tests->saveProduct($product);

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

        $this->subscriptionPriceCommand->run($input, $output);

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

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store payment/stripe_payments/active 1
     * @magentoConfigFixture current_store payment/stripe_payments_basic/stripe_mode test
     * @magentoConfigFixture current_store payment/stripe_payments/payment_flow 0
     * @magentoConfigFixture current_store payment/stripe_payments/payment_action authorize_capture
     *
     * @magentoConfigFixture current_store customer/create_account/default_group 1
     * @magentoConfigFixture current_store customer/create_account/auto_group_assign 1
     * @magentoConfigFixture current_store tax/classes/shipping_tax_class 2
     * @magentoConfigFixture current_store tax/calculation/price_includes_tax 1
     * @magentoConfigFixture current_store tax/calculation/shipping_includes_tax 1
     * @magentoConfigFixture current_store tax/calculation/discount_tax 1
     *
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ApiKeys.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Taxes.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Addresses.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Products.php
     */
    public function testTaxInclusiveSubscriptionMigration()
    {
        $this->quote->create()
            ->setCustomer('Guest')
            ->setCart("ConfigurableSubscription")
            ->setShippingAddress("California")
            ->setShippingMethod("FlatRate")
            ->setBillingAddress("California")
            ->setPaymentMethod("SuccessCard");

        $order = $this->quote->placeOrder();
        $ordersCount = $this->tests->getOrdersCount();

        // Stripe checks
        $customerId = $order->getPayment()->getAdditionalInformation("customer_stripe_id");
        $customer = $this->tests->stripe()->customers->retrieve($customerId);
        $this->assertCount(1, $customer->subscriptions->data);
        $subscription = $customer->subscriptions->data[0];
        $this->compare->object($subscription, [
            "default_tax_rates" => [
                0 => [
                    "description" => "8.25% VAT",
                    "percentage" => "8.25",
                    "inclusive" => true
                ]
            ],
            "items" => [
                "data" => [
                    0 => [
                        "plan" => [
                            "amount" => "1000",
                            "currency" => "usd",
                            "interval" => "month",
                            "interval_count" => 1
                        ],
                        "price" => [
                            "recurring" => [
                                "interval" => "month",
                                "interval_count" => 1
                            ],
                            "unit_amount" => "1000"
                        ],
                        "quantity" => 1
                    ]
                ]
            ],
            "metadata" => [
                "Order #" => $order->getIncrementId()
            ],
            "status" => "active"
        ]);
        $invoice = $this->tests->stripe()->invoices->retrieve($customer->subscriptions->data[0]->latest_invoice);
        $this->assertCount(2, $invoice->lines->data);
        $this->compare->object($invoice, [
            "amount_due" => 1500,
            "amount_paid" => 1500,
            "amount_remaining" => 0,
            "tax" => 114,
            "total" => 1500
        ]);

        // Trigger webhooks
        $subscription = $customer->subscriptions->data[0];
        $this->tests->event()->triggerSubscriptionEvents($subscription, $this);

        // Reset
        $this->helper->clearCache();

        // Change the subscription price
        $this->assertNotEmpty($customer->subscriptions->data[0]->metadata->{"Product ID"});
        $productId = $customer->subscriptions->data[0]->metadata->{"Product ID"};
        $product = $this->helper->loadProductById($productId);
        $productId = $product->getEntityId();
        $product->setPrice(15);
        $product = $this->tests->saveProduct($product);

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

        $this->subscriptionPriceCommand->run($input, $output);

        // Order checks
        $newOrdersCount = $this->tests->getOrdersCount();
        $this->assertEquals($ordersCount + 1, $newOrdersCount);
        $newOrder = $this->tests->getLastOrder();
        $this->compare->object($newOrder->getData(), [
            "state" => "closed",
            "status" => "closed",
            "base_grand_total" => 20.0000,
            "base_shipping_amount" => 4.6200,
            "base_shipping_invoiced" => 4.6200,
            "base_shipping_refunded" => 4.6200,
            "base_shipping_tax_amount" => 0.3800,
            "base_shipping_tax_refunded" => 0.3800,
            "base_subtotal" => 13.8600,
            "base_subtotal_invoiced" => 13.8600,
            "base_subtotal_refunded" => 13.8600,
            "base_tax_amount" => 1.5200,
            "base_tax_invoiced" => 1.5200,
            "base_tax_refunded" => 1.5200,
            "base_total_invoiced" => 20.0000,
            "base_total_offline_refunded" => 20.0000,
            "base_total_paid" => 20.0000,
            "base_total_refunded" => 20.0000,
            "grand_total" => 20.0000,
            "shipping_amount" => 4.6200,
            "shipping_invoiced" => 4.6200,
            "shipping_refunded" => 4.6200,
            "shipping_tax_amount" => 0.3800,
            "shipping_tax_refunded" => 0.3800,
            "subtotal" => 13.8600,
            "subtotal_invoiced" => 13.8600,
            "subtotal_refunded" => 13.8600,
            "tax_amount" => 1.5200,
            "tax_invoiced" => 1.5200,
            "tax_refunded" => 1.5200,
            "total_invoiced" => 20.0000,
            "total_offline_refunded" => 20.0000,
            "total_paid" => 20.0000,
            "total_qty_ordered" => 1.0000,
            "total_refunded" => 20.0000,
            "email_sent" => 1,
            "base_subtotal_incl_tax" => 15.0000,
            "subtotal_incl_tax" => 15.0000,
            "total_due" => 0.0000,
            "shipping_incl_tax" => 5.0000,
            "base_shipping_incl_tax" => 5.0000
        ]);

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
                    "inclusive" => true
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
            "status" => "trialing"
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
        $this->compare->object($upcomingInvoice, [
            "tax" => 152,
            "total" => 2000
        ]);
    }
}
