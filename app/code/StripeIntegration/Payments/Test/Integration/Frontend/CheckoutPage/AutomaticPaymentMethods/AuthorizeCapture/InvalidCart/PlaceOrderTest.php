<?php

namespace StripeIntegration\Payments\Test\Integration\Frontend\AutomaticPaymentMethods\AuthorizeCapture\InvalidCart;

class PlaceOrderTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->tests = new \StripeIntegration\Payments\Test\Integration\Helper\Tests($this);
        $this->quote = new \StripeIntegration\Payments\Test\Integration\Helper\Quote();
        $this->method = $this->objectManager->get(\StripeIntegration\Payments\Model\Method\Checkout::class);
    }

    /**
     * @magentoConfigFixture current_store payment/stripe_payments/active 1
     * @magentoConfigFixture current_store payment/stripe_payments_basic/stripe_mode test
     * @magentoConfigFixture current_store payment/stripe_payments/payment_flow 1
     * @magentoConfigFixture current_store payment/stripe_payments_checkout/payment_action authorize_capture
     * @magentoConfigFixture current_store payment/stripe_payments_checkout/save_payment_method 0
     *
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ApiKeys.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Taxes.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Addresses.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ExchangeRates.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Products.php
     */
    public function testVariableIntervals()
    {
        $this->quote->create()
            ->setCustomer('Guest')
            ->addProduct('simple-monthly-subscription-product', 1)
            ->addProduct('simple-quarterly-subscription-product', 1)
            ->setShippingAddress("California")
            ->setShippingMethod("FlatRate")
            ->setBillingAddress("California")
            ->setPaymentMethod("StripeCheckout");

        $this->expectExceptionMessage("Subscriptions that do not renew together must be bought separately.");
        $this->markTestIncomplete("We need a better way of testing error messages of order placements");
        $order = $this->quote->placeOrder();
    }

    public function testTrialAndRegularSubscription()
    {
        $this->quote->create()
            ->setCustomer('Guest')
            ->addProduct('simple-monthly-subscription-product', 1)
            ->addProduct('simple-trial-monthly-subscription-product', 1)
            ->setShippingAddress("California")
            ->setShippingMethod("FlatRate")
            ->setBillingAddress("California")
            ->setPaymentMethod("StripeCheckout");

        $this->expectExceptionMessage("Subscriptions that do not renew together must be bought separately.");
        $this->markTestIncomplete("We need a better way of testing error messages of order placements");
        $order = $this->quote->placeOrder();
    }
}
