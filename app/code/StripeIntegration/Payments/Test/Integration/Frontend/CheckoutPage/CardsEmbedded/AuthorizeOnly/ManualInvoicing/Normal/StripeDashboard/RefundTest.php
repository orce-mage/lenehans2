<?php

namespace StripeIntegration\Payments\Test\Integration\Frontend\CheckoutPage\CardsEmbedded\AuthorizeOnly\ManualInvoicing\StripeDashboard;

class RefundTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->tests = new \StripeIntegration\Payments\Test\Integration\Helper\Tests($this);
        $this->quote = new \StripeIntegration\Payments\Test\Integration\Helper\Quote();

        $this->helper = $this->objectManager->get(\StripeIntegration\Payments\Helper\Generic::class);
        $this->stripeConfig = $this->objectManager->get(\StripeIntegration\Payments\Model\Config::class);
    }

    /**
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
    public function testPartialRefund()
    {
        $this->quote->create()
            ->setCustomer('Guest')
            ->setCart("Normal")
            ->setShippingAddress("California")
            ->setShippingMethod("FlatRate")
            ->setBillingAddress("California")
            ->setPaymentMethod("SuccessCard");

        $order = $this->quote->placeOrder();
        $orderIncrementId = $order->getIncrementId();

        $stripe = $this->stripeConfig->getStripeClient();
        $paymentIntentId = $order->getPayment()->getLastTransId();
        $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId);

        // Partially refund the charge
        $refund = $stripe->refunds->create(['charge' => $paymentIntent->charges->data[0], 'amount' => 500]);

        // charge.refunded
        $this->tests->event()->trigger("charge.refunded", $paymentIntent->charges->data[0]->id, $this);

        // Refresh the order object
        $order = $this->tests->reloadOrder($order);
        $this->assertEquals("processing", $order->getStatus());
        $this->assertEquals(5, $order->getTotalRefunded());

        // Refund the remaining amount
        $remainingAmount = ($order->getGrandTotal() - $order->getTotalRefunded()) * 100;
        $refund = $stripe->refunds->create(['charge' => $paymentIntent->charges->data[0], 'amount' => $remainingAmount]);

        // charge.refunded
        $this->tests->event()->trigger("charge.refunded", $paymentIntent->charges->data[0]->id, $this);

        // Refresh the order object
        $order = $this->tests->reloadOrder($order);
        $this->assertEquals($order->getGrandTotal(), $order->getTotalRefunded());
        $this->assertEquals("closed", $order->getStatus());
    }
}
