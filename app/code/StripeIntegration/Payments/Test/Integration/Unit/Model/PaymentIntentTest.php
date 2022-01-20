<?php

namespace StripeIntegration\Payments\Test\Integration\Unit\Model;

use PHPUnit\Framework\Constraint\StringContains;

class PaymentIntentTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->quote = new \StripeIntegration\Payments\Test\Integration\Helper\Quote();
        $this->paymentIntentModel = $this->objectManager->get(\StripeIntegration\Payments\Model\PaymentIntent::class);
        $this->stripeConfig = $this->objectManager->get(\StripeIntegration\Payments\Model\Config::class);
    }

    /**
     * @magentoConfigFixture current_store payment/stripe_payments/active 1
     * @magentoConfigFixture current_store payment/stripe_payments_basic/stripe_mode test
     * @magentoConfigFixture current_store payment/stripe_payments/payment_flow 0
     *
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ApiKeys.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Taxes.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Addresses.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Products.php
     */
    public function testPreloadFromCache()
    {
        $this->quote->create()
            ->setCustomer('Guest')
            ->setCart("Normal")
            ->setShippingAddress("California")
            ->setShippingMethod("FlatRate")
            ->setBillingAddress("California")
            ->setPaymentMethod("InsufficientFundsCard");

        // Expected: Your card has insufficient funds.
        $this->markTestIncomplete("You cannot confirm this PaymentIntent because it's missing a payment method. You can either update the PaymentIntent with a payment method and then confirm it again, or confirm it again directly with a payment method.");

        $order = $this->quote->mockOrder();
        $exceptionMsg = $this->paymentIntentModel->confirmAndAssociateWithOrder($order, $order->getPayment());
        $this->assertEquals('Your card has insufficient funds.', $exceptionMsg);

        $exceptionMsg = $this->paymentIntentModel->confirmAndAssociateWithOrder($order, $order->getPayment());
        $this->assertEquals('Your card has insufficient funds.', $exceptionMsg);

        $exceptionMsg = $this->paymentIntentModel->confirmAndAssociateWithOrder($order, $order->getPayment());
        $this->assertEquals('Your card has insufficient funds.', $exceptionMsg);
    }
}
