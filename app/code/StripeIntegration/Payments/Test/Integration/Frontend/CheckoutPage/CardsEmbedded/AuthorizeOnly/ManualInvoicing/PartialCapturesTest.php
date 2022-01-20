<?php

namespace StripeIntegration\Payments\Test\Integration\Frontend\CheckoutPage\CardsEmbedded\AuthorizeOnly\ManualInvoicing;

class PartialCapturesTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->tests = new \StripeIntegration\Payments\Test\Integration\Helper\Tests($this);
        $this->quote = new \StripeIntegration\Payments\Test\Integration\Helper\Quote();

        $this->helper = $this->objectManager->get(\StripeIntegration\Payments\Helper\Generic::class);
        $this->stripeConfig = $this->objectManager->get(\StripeIntegration\Payments\Model\Config::class);
        $this->subscriptionFactory = $this->objectManager->get(\StripeIntegration\Payments\Model\SubscriptionFactory::class);
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->invoiceService = $this->objectManager->get(\Magento\Sales\Model\Service\InvoiceService::class);
        $this->orderRepository = $this->objectManager->get(\Magento\Sales\Api\OrderRepositoryInterface::class);
        $this->invoiceRepository = $this->objectManager->get(\Magento\Sales\Api\InvoiceRepositoryInterface::class);
    }

    /**
     * @magentoAppIsolation enabled
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
    public function testPartialCaptures()
    {
        $this->quote->create()
            ->setCustomer('Guest')
            ->setCart('Normal')
            ->setShippingAddress("California")
            ->setShippingMethod("FlatRate")
            ->setBillingAddress("California")
            ->setPaymentMethod("SuccessCard");

        $order = $this->quote->placeOrder();

        // Trigger the webhook events for the ordered products
        $this->tests->event()->triggerPaymentIntentEvents($order->getPayment()->getLastTransId(), $this);

        // Refresh the order object
        $order = $this->helper->loadOrderByIncrementId($order->getIncrementId());
        $transactionId = $order->getPayment()->getLastTransId();
        $this->assertNotEmpty($transactionId);
        $paymentIntent = $this->tests->stripe()->paymentIntents->retrieve($transactionId);
        $this->assertEquals(5330, $paymentIntent->amount);
        $this->assertEquals(5330, $paymentIntent->amount_capturable);
        $this->assertEquals(0, $paymentIntent->amount_received);

        $orderItemQtys = [];
        foreach ($order->getAllVisibleItems() as $orderItem)
        {
            if ($orderItem->getSku() == "simple-product")
            {
                $orderItemQtys[$orderItem->getId()] = 2;
            }
        }

        \Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea('adminhtml');
        $this->tests->invoiceOnline($order, ['simple-product' => 2]);

        // Refresh the order object
        $this->helper->clearCache();
        $order = $this->helper->loadOrderByIncrementId($order->getIncrementId());
        $transactionId = $order->getPayment()->getLastTransId();

        // Order checks
        $this->assertEquals(53.30, $order->getGrandTotal());
        $this->assertEquals(0, $order->getTotalRefunded());
        $this->assertEquals(0, $order->getTotalCanceled());
        $this->assertEquals(21.65, $order->getTotalDue());
        $this->assertEquals(31.65, $order->getTotalInvoiced());
        $this->assertEquals(31.65, $order->getTotalPaid());
        $this->assertEquals("processing", $order->getState());
        $this->assertEquals("processing", $order->getStatus());

        // Trigger webhooks
        $paymentIntent = $this->tests->stripe()->paymentIntents->retrieve($transactionId);
        $this->tests->event()->trigger("charge.captured", $paymentIntent->charges->data[0], $this);
        $this->tests->event()->trigger("payment_intent.succeeded", $paymentIntent, $this);

        // Stripe checks
        $this->assertEquals(5330, $paymentIntent->amount);
        $this->assertEquals(0, $paymentIntent->amount_capturable);
        $this->assertEquals(3165, $paymentIntent->amount_received);

        // Refresh the order object
        $this->helper->clearCache();
        $order = $this->helper->loadOrderByIncrementId($order->getIncrementId());

        // Order checks
        $this->assertEquals(53.30, $order->getGrandTotal());
        $this->assertEquals(0, $order->getTotalRefunded());
        $this->assertEquals(0, $order->getTotalCanceled());
        $this->assertEquals(21.65, $order->getTotalDue());
        $this->assertEquals(31.65, $order->getTotalInvoiced());
        $this->assertEquals(31.65, $order->getTotalPaid());
        $this->assertEquals("processing", $order->getState());
        $this->assertEquals("processing", $order->getStatus());

        // Invoice checks
        $invoicesCollection = $order->getInvoiceCollection();
        $this->assertEquals(1, $invoicesCollection->getSize());
        $invoice = $invoicesCollection->getFirstItem();
        $this->assertEquals(31.65, $invoice->getGrandTotal());
        $this->assertEquals(2, $invoice->getTotalQty());
        $this->assertEquals(\Magento\Sales\Model\Order\Invoice::STATE_PAID, $invoice->getState());

        // Invoice the remaining amount. This should create an offline invoice.
        $this->expectExceptionMessage("Multiple partial payment captures are not supported by Stripe. Please create an offline Credit Memo instead.");
        $this->tests->invoiceOnline($order, ['virtual-product' => 2]);
    }
}
