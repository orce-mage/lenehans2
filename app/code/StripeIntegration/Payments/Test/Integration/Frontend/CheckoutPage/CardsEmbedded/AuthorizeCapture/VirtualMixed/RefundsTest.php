<?php

namespace StripeIntegration\Payments\Test\Integration\Frontend\CheckoutPage\CardsEmbedded\AuthorizeCapture\Mixed;

class RefundsTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->tests = new \StripeIntegration\Payments\Test\Integration\Helper\Tests($this);
        $this->quote = new \StripeIntegration\Payments\Test\Integration\Helper\Quote();

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
    public function testFullRefunds()
    {
        $this->quote->create()
            ->setCustomer('Guest')
            ->setCart('VirtualMixed')
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
        $this->assertEquals(21.66, $order->getBaseGrandTotal());
        $this->assertEquals(21.66, $order->getGrandTotal());
        $this->assertEquals(21.66, $order->getTotalInvoiced());
        $this->assertEquals(21.66, $order->getTotalPaid());
        $this->assertEquals(0, $order->getTotalDue());
        $this->assertEquals(0, $order->getTotalRefunded());
        $this->assertEquals(0, $order->getTotalCanceled());
        $this->assertEquals("complete", $order->getState());
        $this->assertEquals("complete", $order->getStatus());

        // Stripe checks
        $stripe = $this->tests->stripe();
        $customerId = $order->getPayment()->getAdditionalInformation("customer_stripe_id");
        $customer = $stripe->customers->retrieve($customerId);
        $this->assertEquals(1, count($customer->subscriptions->data));

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
        $this->assertEquals(21.66, $order->getBaseGrandTotal());
        $this->assertEquals(21.66, $order->getGrandTotal());
        $this->assertEquals(21.66, $order->getTotalInvoiced());
        $this->assertEquals(21.66, $order->getTotalPaid());
        $this->assertEquals(0, $order->getTotalDue());
        $this->assertEquals(0, $order->getTotalRefunded());
        $this->assertEquals(0, $order->getTotalCanceled());
        $this->assertEquals("complete", $order->getState());
        $this->assertEquals("complete", $order->getStatus());

        $orderTransactions = $this->tests->helper()->getOrderTransactions($order);
        $this->assertCount(2, $orderTransactions);

        // Refund the order
        $this->assertTrue($order->canCreditmemo());
        $this->tests->refundOnline($invoice, []);

        // Refresh the order object
        $order = $this->tests->reloadOrder($order);

        // Invoice checks
        $invoicesCollection = $order->getInvoiceCollection();
        $this->assertEquals(1, $invoicesCollection->count());
        $this->assertEquals(\Magento\Sales\Model\Order\Invoice::STATE_PAID, $invoice->getState());

        // Order checks
        $this->assertEquals(21.66, $order->getBaseGrandTotal());
        $this->assertEquals(21.66, $order->getGrandTotal());
        $this->assertEquals(21.66, $order->getTotalInvoiced());
        $this->assertEquals(21.66, $order->getTotalPaid());
        $this->assertEquals(0, $order->getTotalDue());
        $this->assertEquals(21.66, $order->getTotalRefunded());
        $this->assertEquals(0, $order->getTotalCanceled());
        $this->assertFalse($order->canCreditmemo());
        $this->assertEquals("complete", $order->getState());
        $this->assertEquals("closed", $order->getStatus());

        // Stripe checks
        $charges = $stripe->charges->all(['limit' => 10, 'customer' => $customer->id]);

        $expected = [
            ['amount' => 1083, 'amount_captured' => 1083, 'amount_refunded' => 1083],
            ['amount' => 1083, 'amount_captured' => 1083, 'amount_refunded' => 1083],
        ];

        for ($i = 0; $i < count($charges); $i++)
        {
            $this->assertEquals($expected[$i]['amount'], $charges->data[$i]->amount, "Charge $i");
            $this->assertEquals($expected[$i]['amount_captured'], $charges->data[$i]->amount_captured, "Charge $i");
            $this->assertEquals($expected[$i]['amount_refunded'], $charges->data[$i]->amount_refunded, "Charge $i");
        }
    }
}
