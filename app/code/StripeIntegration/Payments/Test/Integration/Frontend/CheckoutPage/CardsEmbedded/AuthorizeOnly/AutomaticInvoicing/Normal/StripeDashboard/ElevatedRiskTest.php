<?php

namespace StripeIntegration\Payments\Test\Integration\Frontend\CheckoutPage\CardsEmbedded\AuthorizeOnly\AutomaticInvoicing\Normal\StripeDashboard;

class ElevatedRiskTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->tests = new \StripeIntegration\Payments\Test\Integration\Helper\Tests($this);
        $this->compare = new \StripeIntegration\Payments\Test\Integration\Helper\Compare($this);
        $this->quote = new \StripeIntegration\Payments\Test\Integration\Helper\Quote();

        $this->helper = $this->objectManager->get(\StripeIntegration\Payments\Helper\Generic::class);
    }

    /**
     * @magentoConfigFixture current_store payment/stripe_payments/active 1
     * @magentoConfigFixture current_store payment/stripe_payments_basic/stripe_mode test
     * @magentoConfigFixture current_store payment/stripe_payments/payment_flow 0
     * @magentoConfigFixture current_store payment/stripe_payments/payment_action authorize
     * @magentoConfigFixture current_store payment/stripe_payments/automatic_invoicing 1
     *
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ApiKeys.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Taxes.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Addresses.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Products.php
     */
    public function testCancelation()
    {
        $this->quote->create()
            ->setCustomer('Guest')
            ->setCart("Normal")
            ->setShippingAddress("California")
            ->setShippingMethod("FlatRate")
            ->setBillingAddress("California")
            ->setPaymentMethod("ElevatedRiskCard");

        $order = $this->quote->placeOrder();

        // Trigger webhooks
        $paymentIntentId = $order->getPayment()->getLastTransId();
        $this->tests->event()->triggerPaymentIntentEvents($paymentIntentId, $this);

        // Refresh the order object
        $order = $this->helper->loadOrderByIncrementId($order->getIncrementId());

        // Order checks
        $this->assertEquals(0, $order->getTotalPaid());
        $this->assertEquals(0, $order->getTotalRefunded());
        $this->assertEquals($order->getGrandTotal(), $order->getTotalDue());

        // Invoice checks
        $invoicesCollection = $order->getInvoiceCollection();
        $invoice = $invoicesCollection->getFirstItem();
        $this->assertTrue($invoice->canCapture());
        $this->assertEquals(\Magento\Sales\Model\Order\Invoice::STATE_OPEN, $invoice->getState());

        // Cancel the payment in Stripe
        $paymentIntent = $this->tests->stripe()->paymentIntents->retrieve($paymentIntentId);
        $this->assertNotEmpty($paymentIntent->review);
        $this->assertStringContainsString("prv_", $paymentIntent->review);
        $this->compare->object($paymentIntent, [
            "amount_capturable" => 5330,
            "capture_method" => "manual",
            "status" => "requires_capture"
        ]);

        $paymentIntent = $this->tests->stripe()->paymentIntents->cancel($paymentIntentId, ["cancellation_reason" => "fraudulent"]);
        $this->tests->event()->trigger("charge.refunded", $paymentIntent->charges->data[0], $this);
        $this->tests->event()->trigger("review.closed", $paymentIntent->review, $this);

        // Refresh the order object
        $this->helper->clearCache();
        $order = $this->helper->loadOrderByIncrementId($order->getIncrementId());
        $this->assertEquals("canceled", $order->getState());
        $this->assertEquals("canceled", $order->getStatus());
        $this->assertEquals(0, $order->getTotalPaid());
        $this->assertEquals(0, $order->getTotalRefunded());
        $this->assertEquals(53.30, $order->getTotalCanceled());
        $this->assertEquals(53.30, $order->getTotalDue());

        // Check the invoice
        $invoice = $order->getInvoiceCollection()->getFirstItem();
        $this->assertEquals(\Magento\Sales\Model\Order\Invoice::STATE_CANCELED, $invoice->getState());
    }
}
