<?php

namespace StripeIntegration\Payments\Test\Integration\Frontend\CheckoutPage\CardsEmbedded\AuthorizeCapture;

class RefundsTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->tests = new \StripeIntegration\Payments\Test\Integration\Helper\Tests($this);
        $this->compare = new \StripeIntegration\Payments\Test\Integration\Helper\Compare($this);
        $this->quote = new \StripeIntegration\Payments\Test\Integration\Helper\Quote();

        $this->helper = $this->objectManager->get(\StripeIntegration\Payments\Helper\Generic::class);
        $this->stripeConfig = $this->objectManager->get(\StripeIntegration\Payments\Model\Config::class);
        $this->subscriptionFactory = $this->objectManager->get(\StripeIntegration\Payments\Model\SubscriptionFactory::class);
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
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
    public function testTrialRefunds()
    {
        $this->quote->create()
            ->setCustomer('Guest')
            ->addProduct('simple-product', 1)
            ->addProduct('virtual-monthly-subscription-product', 1)
            ->addProduct('simple-trial-monthly-subscription-product', 1)
            ->setShippingAddress("California")
            ->setShippingMethod("FlatRate")
            ->setBillingAddress("California")
            ->setPaymentMethod("SuccessCard");

        $order = $this->quote->placeOrder();

        // Invoice checks
        $invoicesCollection = $order->getInvoiceCollection();
        $this->assertEquals(1, $invoicesCollection->count());
        $invoice = $invoicesCollection->getFirstItem();
        $this->assertTrue($invoice->getIsPaid());

        // Order checks
        $this->assertEquals(42.49, $order->getBaseGrandTotal());
        $this->assertEquals(42.49, $order->getGrandTotal());
        $this->assertEquals(42.49, $order->getTotalInvoiced());
        $this->assertEquals(42.49, $order->getTotalPaid());
        $this->assertEquals(0, $order->getTotalDue());
        $this->assertEquals(0, $order->getTotalRefunded());
        $this->assertEquals("processing", $order->getState());
        $this->assertEquals("processing", $order->getStatus());

        // Stripe checks
        $stripe = $this->stripeConfig->getStripeClient();
        $customerId = $order->getPayment()->getAdditionalInformation("customer_stripe_id");
        $customer = $stripe->customers->retrieve($customerId);
        $this->assertEquals(2, count($customer->subscriptions->data));

        // Trigger all webhooks
        $subscriptions = array_reverse($customer->subscriptions->data);
        foreach ($subscriptions as $subscription)
            $this->tests->event()->triggerSubscriptionEvents($subscription, $this);

        $this->tests->event()->triggerPaymentIntentEvents($order->getPayment()->getLastTransId(), $this);

        // Refresh the order object
        $order = $this->tests->reloadOrder($order);

        // Invoice checks
        $invoicesCollection = $order->getInvoiceCollection();
        $this->assertEquals(1, $invoicesCollection->count());
        $invoice = $invoicesCollection->getFirstItem();
        $this->assertEquals(\Magento\Sales\Model\Order\Invoice::STATE_PAID, $invoice->getState());

        // Order checks
        $this->assertEquals(42.49, $order->getBaseGrandTotal());
        $this->assertEquals(42.49, $order->getGrandTotal());
        $this->assertEquals(42.49, $order->getTotalInvoiced());
        $this->assertEquals(26.66, $order->getTotalPaid());
        $this->assertEquals(15.83, $order->getTotalDue());
        $this->assertEquals(0, $order->getTotalRefunded());
        $this->assertEquals(0, $order->getTotalCanceled());
        $this->assertEquals("processing", $order->getState());
        $this->assertEquals("processing", $order->getStatus());

        // Refund the order
        $this->assertTrue($order->canCreditmemo());
        $this->markTestIncomplete("Refunding manually refunds 26.66. Refunding through the tests refunds 27.49, i.e. the tax for item 3 is not removed.");
        $this->tests->refundOnline($invoice, ['simple-product' => 1, 'virtual-monthly-subscription-product' => 1], $baseShipping = 5);

        // Refresh the order object
        $order = $this->tests->reloadOrder($order);

        // Invoice checks
        $invoicesCollection = $order->getInvoiceCollection();
        $this->assertEquals(1, $invoicesCollection->count());
        $this->assertEquals(\Magento\Sales\Model\Order\Invoice::STATE_PAID, $invoice->getState());

        // Order checks
        $this->assertEquals(42.49, $order->getBaseGrandTotal());
        $this->assertEquals(42.49, $order->getGrandTotal());
        $this->assertEquals(42.49, $order->getTotalInvoiced());
        $this->assertEquals(26.66, $order->getTotalPaid());
        $this->assertEquals(15.83, $order->getTotalDue());
        $this->assertEquals(26.66, $order->getTotalRefunded());
        $this->assertEquals(0, $order->getTotalCanceled());
        $this->assertFalse($order->canCreditmemo());
        $this->assertEquals("processing", $order->getState());
        $this->assertEquals("processing", $order->getStatus());

        // Expire the trial subscription
        $ordersCount = $this->objectManager->get('Magento\Sales\Model\Order')->getCollection()->count();
        foreach ($subscriptions as $subscription)
        {
            if ($subscription->status == "trialing")
            {
                $stripe->subscriptions->update($subscription->id, ['trial_end' => "now"]);
                $subscription = $stripe->subscriptions->retrieve($subscription->id, ['expand' => ['latest_invoice']]);

                // Trigger webhook events for the trial end
                $this->tests->event()->triggerSubscriptionEvents($subscription, $this);

            }
        }

        // Check that a new order was created
        $newOrdersCount = $this->objectManager->get('Magento\Sales\Model\Order')->getCollection()->count();
        $this->assertEquals($ordersCount + 1, $newOrdersCount);

        // Refresh the order object
        $order = $this->tests->refreshOrder($order);

        // Invoice checks
        $invoicesCollection = $order->getInvoiceCollection();
        $this->assertEquals(1, $invoicesCollection->count());
        $this->assertEquals(\Magento\Sales\Model\Order\Invoice::STATE_PAID, $invoice->getState());

        // Order checks
        $this->assertEquals(42.49, $order->getBaseGrandTotal());
        $this->assertEquals(42.49, $order->getGrandTotal());
        $this->assertEquals(42.49, $order->getTotalInvoiced());
        $this->assertEquals(42.49, $order->getTotalPaid());
        $this->assertEquals(0, $order->getTotalDue());
        $this->assertEquals(26.66, $order->getTotalRefunded());
        $this->assertEquals(0, $order->getTotalCanceled());
        $this->assertEquals("processing", $order->getState());
        $this->assertEquals("processing", $order->getStatus());

        // Refund the trial subscription via the 1st order
        $this->assertTrue($order->canCreditmemo());
        $this->markTestIncomplete("The original order does not have the transaction ID of the activated trial subscription. Solution to be implemented together with M178.");
        $this->tests->refundOnline($invoice, ['simple-trial-monthly-subscription-product' => 1], $baseShipping = 5);

        // Refresh the order object
        $order = $this->tests->reloadOrder($order);

        // Order checks
        $this->assertEquals(42.49, $order->getBaseGrandTotal());
        $this->assertEquals(42.49, $order->getGrandTotal());
        $this->assertEquals(42.49, $order->getTotalInvoiced());
        $this->assertEquals(42.49, $order->getTotalPaid());
        $this->assertEquals(0, $order->getTotalDue());
        $this->assertEquals(42.49, $order->getTotalRefunded());
        $this->assertEquals(0, $order->getTotalCanceled());
        $this->assertFalse($order->canCreditmemo());
        $this->assertEquals("closed", $order->getState());
        $this->assertEquals("closed", $order->getStatus());

        // @todo - check that the newly created order has also been closed


        // Stripe checks
        $charges = $stripe->charges->all(['limit' => 10, 'customer' => $customer->id]);

        $expected = [
            ['amount' => 1583, 'amount_captured' => 1583, 'amount_refunded' => 1583],
            ['amount' => 1583, 'amount_captured' => 1583, 'amount_refunded' => 1583],
            ['amount' => 1083, 'amount_captured' => 1083, 'amount_refunded' => 1083],
        ];

        for ($i = 0; $i < count($charges); $i++)
        {
            $this->assertEquals($expected[$i]['amount'], $charges->data[$i]->amount, "Charge $i");
            $this->assertEquals($expected[$i]['amount_captured'], $charges->data[$i]->amount_captured, "Charge $i");
            $this->assertEquals($expected[$i]['amount_refunded'], $charges->data[$i]->amount_refunded, "Charge $i");
        }
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
    public function testPartialRefunds()
    {
        $this->quote->create()
            ->setCustomer('Guest')
            ->addProduct('simple-product', 1)
            ->addProduct('virtual-product', 1)
            ->setShippingAddress("California")
            ->setShippingMethod("FlatRate")
            ->setBillingAddress("California")
            ->setPaymentMethod("SuccessCard");

        $order = $this->quote->placeOrder();

        // We are not hardcoding the value because running the whole file vs running the test case only produces a different grand total, 26.66 vs 26.65
        $orderGrandTotal = $order->getGrandTotal();
        $stripeGrandTotal = $orderGrandTotal * 100;

        $this->assertEquals("processing", $order->getState());
        $this->assertEquals("processing", $order->getStatus());

        // Trigger all webhooks
        $this->tests->event()->triggerPaymentIntentEvents($order->getPayment()->getLastTransId(), $this);

        // Refresh the order object
        $order = $this->tests->reloadOrder($order);

        // Invoice checks
        $invoicesCollection = $order->getInvoiceCollection();
        $this->assertEquals(1, $invoicesCollection->count());
        $invoice = $invoicesCollection->getFirstItem();
        $this->assertEquals(\Magento\Sales\Model\Order\Invoice::STATE_PAID, $invoice->getState());

        // Partially refund the order
        $this->assertTrue($order->canCreditmemo());
        $this->tests->refundOnline($invoice, ['simple-product' => 1], $baseShipping = 5);

        // Refresh the order object
        $order = $this->tests->reloadOrder($order);

        // Order checks
        $this->assertEquals($orderGrandTotal, $order->getTotalInvoiced());
        $this->assertEquals($orderGrandTotal, $order->getTotalPaid());
        $this->assertEquals(0, $order->getTotalDue());
        $this->assertEquals(15.83, $order->getTotalRefunded());
        $this->assertEquals(0, $order->getTotalCanceled());
        $this->assertTrue($order->canCreditmemo());
        $this->assertEquals("complete", $order->getState());
        $this->assertEquals("complete", $order->getStatus());

        // Stripe checks
        $paymentIntentId = $order->getPayment()->getLastTransId();
        $paymentIntent = $this->stripeConfig->getStripeClient()->paymentIntents->retrieve($paymentIntentId);
        $this->compare->object($paymentIntent, [
            "amount" => $stripeGrandTotal,
            "amount_capturable" => 0,
            "amount_received" => $stripeGrandTotal,
            "status" => "succeeded",
            "charges" => [
                "data" => [
                    0 => [
                        "amount" => $stripeGrandTotal,
                        "amount_captured" => $stripeGrandTotal,
                        "amount_refunded" => 1583,
                        "status" => "succeeded"
                    ]
                ]
            ]
        ]);

        // Refund the remaining amount
        $this->assertTrue($order->canCreditmemo());
        $this->tests->refundOnline($invoice, ['virtual-product' => 1]);

        // Refresh the order object
        $order = $this->tests->reloadOrder($order);

        // Invoice checks
        $invoicesCollection = $order->getInvoiceCollection();
        $this->assertEquals(1, $invoicesCollection->count());
        $this->assertEquals(\Magento\Sales\Model\Order\Invoice::STATE_PAID, $invoice->getState());

        // Order checks
        $this->assertEquals($orderGrandTotal, $order->getTotalInvoiced());
        $this->assertEquals($orderGrandTotal, $order->getTotalPaid());
        $this->assertEquals(0, $order->getTotalDue());
        $this->assertEquals($orderGrandTotal, $order->getTotalRefunded());
        $this->assertEquals(0, $order->getTotalCanceled());
        $this->assertFalse($order->canCreditmemo());
        $this->assertEquals("closed", $order->getState());
        $this->assertEquals("closed", $order->getStatus());

        // Stripe checks
        $paymentIntent = $this->stripeConfig->getStripeClient()->paymentIntents->retrieve($paymentIntentId);
        $this->compare->object($paymentIntent, [
            "amount" => $stripeGrandTotal,
            "amount_capturable" => 0,
            "amount_received" => $stripeGrandTotal,
            "status" => "succeeded",
            "charges" => [
                "data" => [
                    0 => [
                        "amount" => $stripeGrandTotal,
                        "amount_captured" => $stripeGrandTotal,
                        "amount_refunded" => $stripeGrandTotal,
                        "status" => "succeeded"
                    ]
                ]
            ]
        ]);
    }
}
