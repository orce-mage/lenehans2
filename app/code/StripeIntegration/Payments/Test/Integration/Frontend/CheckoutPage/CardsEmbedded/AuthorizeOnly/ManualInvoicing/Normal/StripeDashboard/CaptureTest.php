<?php

namespace StripeIntegration\Payments\Test\Integration\Frontend\CheckoutPage\CardsEmbedded\AuthorizeOnly\ManualInvoicing\StripeDashboard;

class CaptureTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->quote = new \StripeIntegration\Payments\Test\Integration\Helper\Quote();
        $this->tests = new \StripeIntegration\Payments\Test\Integration\Helper\Tests($this);

        $this->helper = $this->objectManager->get(\StripeIntegration\Payments\Helper\Generic::class);
    }

    /**
     * @magentoConfigFixture current_store payment/stripe_payments/active 1
     * @magentoConfigFixture current_store payment/stripe_payments_basic/stripe_mode test
     * @magentoConfigFixture current_store payment/stripe_payments/payment_flow 0
     * @magentoConfigFixture current_store payment/stripe_payments/payment_action authorize
     *
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ApiKeys.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Taxes.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Addresses.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Products.php
     */
    public function testPartialCapture()
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

        $currency = $order->getOrderCurrencyCode();
        $amount = $this->helper->convertMagentoAmountToStripeAmount($order->getGrandTotal(), $currency);

        $this->assertEquals("processing", $order->getStatus());
        $this->assertEquals(0, $order->getTotalPaid());
        $this->assertEquals($order->getGrandTotal(), $order->getTotalDue());
        $this->assertTrue($order->canInvoice());

        // Partially capture the charge
        $paymentIntentId = $order->getPayment()->getLastTransId();
        $paymentIntent = $this->tests->stripe()->paymentIntents->capture($paymentIntentId, ["amount_to_capture" => 500]);
        $this->assertEquals(500, $paymentIntent->amount_received);
        $this->tests->event()->trigger("charge.captured", $paymentIntent->charges->data[0], $this);
        $this->tests->event()->trigger("payment_intent.succeeded", $paymentIntent, $this);

        // Refresh the order object
        $order = $this->tests->refreshOrder($order);
        $this->assertFalse($order->canInvoice());
        $this->assertEquals("processing", $order->getStatus());
        $this->assertEquals(5, $order->getTotalPaid());
        $this->assertEquals($order->getGrandTotal() - 5, $order->getTotalDue());

        // Check that an invoice was created
        $invoice = $order->getInvoiceCollection()->getFirstItem();
        $this->assertNotEmpty($invoice);
        $this->assertEquals(\Magento\Sales\Model\Order\Invoice::STATE_PAID, $invoice->getState());
        $this->assertEquals(5, $invoice->getGrandTotal());

        // Refund the invoice from Magento
    }
}
