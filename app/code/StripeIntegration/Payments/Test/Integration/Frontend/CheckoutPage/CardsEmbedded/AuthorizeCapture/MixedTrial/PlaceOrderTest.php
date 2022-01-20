<?php

namespace StripeIntegration\Payments\Test\Integration\Frontend\CheckoutPage\CardsEmbedded\AuthorizeCapture\MixedTrial;

use PHPUnit\Framework\Constraint\StringContains;

class PlaceOrderTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->tests = new \StripeIntegration\Payments\Test\Integration\Helper\Tests($this);
        $this->quote = new \StripeIntegration\Payments\Test\Integration\Helper\Quote();
    }

    /**
     * @magentoConfigFixture current_store payment/stripe_payments/active 1
     * @magentoConfigFixture current_store payment/stripe_payments_basic/stripe_mode test
     * @magentoConfigFixture current_store payment/stripe_payments/payment_flow 0
     * @magentoConfigFixture current_store payment/stripe_payments/payment_action authorize_capture
     *
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ApiKeys.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Taxes.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Products.php
     */
    public function testPlaceOrder()
    {
        $this->quote->create()
            ->setCustomer('Guest')
            ->setCart("MixedTrial")
            ->setShippingAddress("California")
            ->setShippingMethod("FlatRate")
            ->setBillingAddress("California")
            ->setPaymentMethod("SuccessCard");

        $order = $this->quote->placeOrder();

        $stripe = $this->tests->stripe();

        $customerId = $order->getPayment()->getAdditionalInformation("customer_stripe_id");
        $customer = $stripe->customers->retrieve($customerId);
        $this->assertEquals(1, count($customer->subscriptions->data));
        $subscription = $customer->subscriptions->data[0];
        $this->assertNotEmpty($subscription->latest_invoice);
        $invoiceId = $subscription->latest_invoice;

        // Get the current orders count
        $ordersCount = $this->tests->getOrdersCount();

        // Process the subscription's invoice.payment_succeeded event
        $invoice = $stripe->invoices->retrieve($invoiceId, ['expand' => ['charge']]);
        $this->assertNotEmpty($invoice->subscription);
        $subscriptionId = $invoice->subscription;
        $this->assertEmpty($invoice->charge);
        $this->assertEquals(0, $invoice->amount_due);
        $this->assertEquals(0, $invoice->amount_paid);
        $this->assertEquals(0, $invoice->amount_remaining);
        $this->tests->event()->trigger("invoice.payment_succeeded", $invoice, $this);

        // Process the regular products charge.succeeded event
        $paymentIntentId = $order->getPayment()->getLastTransId();
        $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId);
        $charge =  $paymentIntent->charges->data[0];
        $this->tests->event()->trigger("charge.succeeded", $charge, $this);

        // Ensure that no new order was created
        $newOrdersCount = $this->tests->getOrdersCount();
        $this->assertEquals($ordersCount, $newOrdersCount);

        // Refresh the order object
        $order = $this->tests->refreshOrder($order);
        $this->assertEquals("processing", $order->getState());
        $this->assertEquals("processing", $order->getStatus());

        // Check that an invoice was created
        $invoicesCollection = $order->getInvoiceCollection();
        $this->assertNotEmpty($invoicesCollection);
        $this->assertEquals(1, $invoicesCollection->getSize());

        $invoice = $invoicesCollection->getFirstItem();
        $this->assertCount(2, $invoice->getAllItems());
        $this->assertEquals(\Magento\Sales\Model\Order\Invoice::STATE_PAID, $invoice->getState());
        $this->assertEquals($paymentIntentId, $invoice->getTransactionId());
        $this->assertEquals(15.83, $order->getTotalPaid());
        $this->assertEquals(15.83, $order->getBaseTotalPaid());
        $this->assertEquals(15.83, $order->getTotalDue());
        $this->assertEquals(15.83, $order->getBaseTotalDue());

        // Check that the transaction IDs have been associated with the order
        $transactions = $this->tests->helper()->getOrderTransactions($order);
        $this->assertEquals(1, count($transactions));
        foreach ($transactions as $key => $transaction)
        {
            $this->assertEquals($paymentIntentId, $transaction->getTxnId());
            $this->assertEquals("capture", $transaction->getTxnType());
            $this->assertFalse($transaction->getAdditionalInformation("is_subscription"));
            $this->assertEquals(15.83, $transaction->getAdditionalInformation("amount"));
        }

        // End the trial
        $stripe->subscriptions->update($subscriptionId, ['trial_end' => "now"]);
        $subscription = $stripe->subscriptions->retrieve($subscriptionId, ['expand' => ['latest_invoice']]);

        $ordersCount = $this->tests->getOrdersCount();

        // Trigger webhook events for the trial end
        $this->tests->event()->trigger("charge.succeeded", $subscription->latest_invoice->charge, $this);
        $this->tests->event()->trigger("invoice.payment_succeeded", $subscription->latest_invoice->id, $this);

        // Check that the order invoice was marked as paid
        $order = $this->tests->reloadOrder($order);
        $this->assertEquals(31.66, $order->getTotalPaid());
        $this->assertEquals(0, $order->getTotalDue());
        $invoicesCollection = $order->getInvoiceCollection();
        $invoice = $invoicesCollection->getFirstItem();
        $this->assertEquals(\Magento\Sales\Model\Order\Invoice::STATE_PAID, $invoice->getState());
        $this->assertEquals($paymentIntentId, $invoice->getTransactionId());

        // Check that the transaction IDs have been associated with the order
        $transactions = $this->tests->helper()->getOrderTransactions($order);
        $this->assertEquals(2, count($transactions));
        foreach ($transactions as $key => $transaction)
        {
            if ($transaction->getTxnId() == $subscription->latest_invoice->payment_intent)
            {
                $this->assertEquals("capture", $transaction->getTxnType());
                $this->assertTrue($transaction->getAdditionalInformation("is_subscription"));
                $this->assertEquals(15.83, $transaction->getAdditionalInformation("amount"));
            }
            else
            {
                $this->assertEquals($paymentIntentId, $transaction->getTxnId());
                $this->assertEquals("capture", $transaction->getTxnType());
                $this->assertFalse($transaction->getAdditionalInformation("is_subscription"));
                $this->assertEquals(15.83, $transaction->getAdditionalInformation("amount"));
            }
        }

        // Ensure that a new order was created
        $newOrdersCount = $this->tests->getOrdersCount();
        $this->assertEquals($ordersCount + 1, $newOrdersCount);

        // Check the newly created order
        $newOrder = $this->objectManager->get('Magento\Sales\Model\Order')->getCollection()->setOrder('increment_id','DESC')->getFirstItem();
        $this->assertNotEquals($order->getIncrementId(), $newOrder->getIncrementId());
        $this->assertEquals("processing", $newOrder->getState());
        $this->assertEquals("processing", $newOrder->getStatus());
        $this->assertEquals(15.83, $newOrder->getGrandTotal());
        $this->assertEquals(15.83, $newOrder->getTotalPaid());
        $this->assertEquals(1, $newOrder->getInvoiceCollection()->getSize());
    }
}
