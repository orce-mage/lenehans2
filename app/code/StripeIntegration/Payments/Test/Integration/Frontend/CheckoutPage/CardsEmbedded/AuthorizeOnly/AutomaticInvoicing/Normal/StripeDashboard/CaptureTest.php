<?php

namespace StripeIntegration\Payments\Test\Integration\Frontend\CheckoutPage\CardsEmbedded\AuthorizeOnly\AutomaticInvoicing\Normal\StripeDashboard;

class CaptureTest extends \PHPUnit\Framework\TestCase
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

        // Trigger webhooks
        $paymentIntentId = $order->getPayment()->getLastTransId();
        $this->tests->event()->triggerPaymentIntentEvents($paymentIntentId, $this);

        // Refresh the order object
        $order = $this->tests->reloadOrder($order);

        // Order checks
        $this->assertEquals(0, $order->getTotalPaid());
        $this->assertEquals(0, $order->getTotalRefunded());
        $this->assertEquals($order->getGrandTotal(), $order->getTotalDue());

        $invoicesCollection = $order->getInvoiceCollection();
        $invoice = $invoicesCollection->getFirstItem();
        $this->assertTrue($invoice->canCapture());
        $this->assertEquals(\Magento\Sales\Model\Order\Invoice::STATE_OPEN, $invoice->getState());

        // Capture the invoice via Stripe
        $paymentIntent = $this->tests->stripe()->paymentIntents->retrieve($paymentIntentId);
        $this->compare->object($paymentIntent, [
            "amount_capturable" => 5330,
            "capture_method" => "manual",
            "status" => "requires_capture"
        ]);

        // Capture 1000
        $paymentIntent = $this->tests->stripe()->paymentIntents->capture($paymentIntentId, ["amount_to_capture" => 1000]);
        $this->assertEquals(1000, $paymentIntent->charges->data[0]->amount_captured);
        $this->tests->event()->trigger("charge.captured", $paymentIntent->charges->data[0], $this);
        $this->tests->event()->trigger("payment_intent.succeeded", $paymentIntent, $this);

        // Refresh the order object
        $order = $this->tests->reloadOrder($order);

        $this->assertEquals(10.00, $order->getTotalPaid());
        $this->assertEquals(0, $order->getTotalRefunded());
        $this->assertEquals(43.30, $order->getTotalDue());

        $transactions = $this->helper->getOrderTransactions($order);
        foreach ($transactions as $t)
        {
            $this->compare->object($t->getData(), [
                "txn_id" => $paymentIntentId,
                "txn_type" => "capture",
                "additional_information" => [
                    "is_subscription" => false,
                    "amount" => 10,
                    "currency" => "usd"
                ]
            ]);
        }

        // Check the invoice
        $invoice = $order->getInvoiceCollection()->getFirstItem();
        $this->assertEquals(\Magento\Sales\Model\Order\Invoice::STATE_PAID, $invoice->getState());
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
    public function testFullCapture()
    {
        $this->quote->create()
            ->setCustomer('Guest')
            ->setCart("Normal")
            ->setShippingAddress("California")
            ->setShippingMethod("FlatRate")
            ->setBillingAddress("California")
            ->setPaymentMethod("SuccessCard");

        $order = $this->quote->placeOrder();

        // Trigger webhooks
        $paymentIntentId = $order->getPayment()->getLastTransId();
        $this->tests->event()->triggerPaymentIntentEvents($paymentIntentId, $this);

        // Refresh the order object
        $order = $this->tests->reloadOrder($order);

        // Order checks
        $this->assertEquals(0, $order->getTotalPaid());
        $this->assertEquals(0, $order->getTotalRefunded());
        $this->assertEquals($order->getGrandTotal(), $order->getTotalDue());

        $invoicesCollection = $order->getInvoiceCollection();
        $invoice = $invoicesCollection->getFirstItem();
        $this->assertTrue($invoice->canCapture());
        $this->assertEquals(\Magento\Sales\Model\Order\Invoice::STATE_OPEN, $invoice->getState());

        // Capture the invoice via Stripe
        $paymentIntent = $this->tests->stripe()->paymentIntents->retrieve($paymentIntentId);
        $this->compare->object($paymentIntent, [
            "amount_capturable" => 5330,
            "capture_method" => "manual",
            "status" => "requires_capture"
        ]);

        // Full capture
        $paymentIntent = $this->tests->stripe()->paymentIntents->capture($paymentIntentId);
        $this->assertEquals(5330, $paymentIntent->charges->data[0]->amount_captured);
        $this->tests->event()->trigger("charge.captured", $paymentIntent->charges->data[0], $this);
        $this->tests->event()->trigger("payment_intent.succeeded", $paymentIntent, $this);

        // Refresh the order object
        $order = $this->tests->reloadOrder($order);

        $this->assertEquals(53.30, $order->getTotalPaid());
        $this->assertEquals(0, $order->getTotalRefunded());
        $this->assertEquals(0, $order->getTotalDue());

        $transactions = $this->helper->getOrderTransactions($order);
        foreach ($transactions as $t)
        {
            $this->compare->object($t->getData(), [
                "txn_id" => $paymentIntentId,
                "txn_type" => "capture",
                "additional_information" => [
                    "is_subscription" => false,
                    "amount" => 53.30,
                    "currency" => "usd"
                ]
            ]);
        }

        // Check the invoice
        $invoice = $order->getInvoiceCollection()->getFirstItem();
        $this->assertEquals(\Magento\Sales\Model\Order\Invoice::STATE_PAID, $invoice->getState());

        // Create an online credit memo from Magento
        $this->tests->refundOnline($invoice, ["simple-product" => 2, "virtual-product" => 2], $shippingAmount = 10);

        // Refresh the order object
        $this->helper->clearCache();
        $order = $this->helper->loadOrderByIncrementId($order->getIncrementId());
        $this->assertEquals($order->getGrandTotal(), $order->getTotalRefunded());
    }
}
