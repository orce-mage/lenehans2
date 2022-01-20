<?php

namespace StripeIntegration\Payments\Test\Integration\Frontend\CheckoutPage\CardsEmbedded\AuthorizeOnly\ManualInvoicing;

class RefundsTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->tests = new \StripeIntegration\Payments\Test\Integration\Helper\Tests($this);
        $this->compare = new \StripeIntegration\Payments\Test\Integration\Helper\Compare($this);
        $this->quote = new \StripeIntegration\Payments\Test\Integration\Helper\Quote();

        $this->cartManagement = $this->objectManager->get(\Magento\Quote\Api\CartManagementInterface::class);
        $this->webhooks = $this->objectManager->get(\StripeIntegration\Payments\Helper\Webhooks::class);
        $this->helper = $this->objectManager->get(\StripeIntegration\Payments\Helper\Generic::class);
        $this->stripeConfig = $this->objectManager->get(\StripeIntegration\Payments\Model\Config::class);
        $this->subscriptionFactory = $this->objectManager->get(\StripeIntegration\Payments\Model\SubscriptionFactory::class);
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
    }

    /**
     * @magentoConfigFixture current_store payment/stripe_payments/active 1
     * @magentoConfigFixture current_store payment/stripe_payments_basic/stripe_mode test
     * @magentoConfigFixture current_store payment/stripe_payments/payment_flow 0
     * @magentoConfigFixture current_store payment/stripe_payments/payment_action authorize
     * @magentoConfigFixture current_store payment/stripe_payments/automatic_invoicing 0
     *
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ApiKeys.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Taxes.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Addresses.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Products.php
     */
    public function testRefunds()
    {
        $this->quote->create()
            ->setCustomer('Guest')
            ->addProduct('simple-product', 1)
            ->addProduct('virtual-monthly-subscription-product', 1)
            ->addProduct('simple-monthly-subscription-product', 1)
            ->addProduct('simple-trial-monthly-subscription-product', 1)
            ->setShippingAddress("California")
            ->setShippingMethod("FlatRate")
            ->setBillingAddress("California")
            ->setPaymentMethod("SuccessCard");

        $order = $this->quote->placeOrder();

        $invoicesCollection = $order->getInvoiceCollection();

        $this->assertEquals("processing", $order->getState());
        $this->assertEquals("processing", $order->getStatus());
        $this->assertEquals(0, $invoicesCollection->count());

        $this->assertTrue($order->canInvoice());
        $this->assertTrue($order->canCancel());
        $this->assertEquals(0, $order->getTotalPaid());
        $this->assertEquals(0, $order->getBaseTotalPaid());
        $this->assertEquals(58.32, $order->getTotalDue());
        $this->assertEquals(58.32, $order->getBaseTotalDue());

        $stripe = $this->stripeConfig->getStripeClient();
        $customerId = $order->getPayment()->getAdditionalInformation("customer_stripe_id");
        $customer = $stripe->customers->retrieve($customerId);
        $this->assertEquals(3, count($customer->subscriptions->data));

        $subscriptions = array_reverse($customer->subscriptions->data);
        foreach ($subscriptions as $subscription)
            $this->tests->event()->triggerSubscriptionEvents($subscription, $this);

        $this->tests->event()->triggerPaymentIntentEvents($order->getPayment()->getLastTransId(), $this);

        // Refresh the order object
        $order = $this->tests->reloadOrder($order);

        $invoicesCollection = $order->getInvoiceCollection();

        $this->assertEquals("processing", $order->getState());
        $this->assertEquals("processing", $order->getStatus());
        $this->assertEquals(0, $invoicesCollection->count());

        $this->assertTrue($order->canInvoice());
        $this->assertTrue($order->canCancel());
        $this->assertEquals(26.66, $order->getTotalPaid());
        $this->assertEquals(26.66, $order->getBaseTotalPaid());
        $this->assertEquals(31.66, $order->getTotalDue());
        $this->assertEquals(31.66, $order->getBaseTotalDue());

        // Stripe checks
        $charges = $stripe->charges->all(['limit' => 10, 'customer' => $customer->id]);

        $expected = [
            ['amount' => 1583, 'amount_captured' => 0, 'amount_refunded' => 0, "captured" => 0],
            ['amount' => 1583, 'amount_captured' => 1583, 'amount_refunded' => 0, "captured" => 1],
            ['amount' => 1083, 'amount_captured' => 1083, 'amount_refunded' => 0, "captured" => 1],
        ];

        for ($i = 0; $i < count($charges); $i++)
        {
            $this->compare->object($charges->data[$i], $expected[$i]);
        }

        // Cancel the order
        $order->cancel();

        // Refresh the order object
        $order = $this->tests->reloadOrder($order);

        $this->assertFalse($order->canInvoice());
        $this->assertFalse($order->canCancel());
        $this->assertEquals("canceled", $order->getState());
        $this->assertEquals("canceled", $order->getStatus());
        $this->assertEquals(26.66, $order->getTotalPaid());
        $this->assertEquals(26.66, $order->getBaseTotalPaid());
        $this->assertEquals(26.66, $order->getTotalRefunded());
        $this->assertEquals(26.66, $order->getBaseTotalRefunded());
        $this->assertEquals(31.66, $order->getTotalCanceled());
        $this->assertEquals(31.66, $order->getBaseTotalCanceled());

        // Stripe checks
        $charges = $stripe->charges->all(['limit' => 10, 'customer' => $customer->id]);

        $expected = [
            ['amount' => 1583, 'amount_captured' => 0, 'amount_refunded' => 1583, "captured" => 0],
            ['amount' => 1583, 'amount_captured' => 1583, 'amount_refunded' => 1583, "captured" => 1],
            ['amount' => 1083, 'amount_captured' => 1083, 'amount_refunded' => 1083, "captured" => 1],
        ];

        for ($i = 0; $i < count($charges); $i++)
        {
            $this->compare->object($charges->data[$i], $expected[$i]);
        }
    }
}
