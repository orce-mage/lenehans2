<?php

namespace StripeIntegration\Payments\Test\Integration\Frontend\AutomaticPaymentMethods\AuthorizeOnly\ManualInvoicing\Mixed;

class AvailabilityTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->tests = new \StripeIntegration\Payments\Test\Integration\Helper\Tests($this);
        $this->quote = new \StripeIntegration\Payments\Test\Integration\Helper\Quote();
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store payment/stripe_payments/active 1
     * @magentoConfigFixture current_store payment/stripe_payments_basic/stripe_mode test
     * @magentoConfigFixture current_store payment/stripe_payments/payment_flow 1
     * @magentoConfigFixture current_store payment/stripe_payments_checkout/payment_action authorize
     *
     * @magentoConfigFixture current_store currency/options/base USD
     * @magentoConfigFixture current_store currency/options/allow EUR,USD
     * @magentoConfigFixture current_store currency/options/default EUR
     *
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ApiKeys.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Taxes.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Addresses.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ExchangeRates.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Products.php
     * @dataProvider EUMethodProvider
     */
    public function testEUMethodAvailability($cartType, $billingAddress, $shippingAddress, $supportedMethods)
    {
        $this->quote->create()
            ->setCustomer('Guest')
            ->setCart($cartType)
            ->setShippingMethod("FlatRate")
            ->setBillingAddress($billingAddress)
            ->setShippingAddress($shippingAddress)
            ->setPaymentMethod("StripeCheckout");

        $methods = $this->quote->getAvailablePaymentMethods();

        foreach ($supportedMethods as $method)
        {
            $this->assertContains($method, $methods, "$method is not available");
        }

        $this->assertCount(count($supportedMethods), $methods);
    }

    public function EUMethodProvider()
    {
        return [
            [
                "cartType" => "Mixed",
                "billingAddress" => "Berlin",
                "shippingAddress" => "Berlin",
                "supportedMethods" => [ "card", "sepa_debit" ]
            ]
        ];
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store payment/stripe_payments/active 1
     * @magentoConfigFixture current_store payment/stripe_payments_basic/stripe_mode test
     * @magentoConfigFixture current_store payment/stripe_payments/payment_flow 1
     * @magentoConfigFixture current_store payment/stripe_payments_checkout/payment_action authorize
     *
     * @magentoConfigFixture current_store currency/options/base USD
     * @magentoConfigFixture current_store currency/options/allow GBP,USD
     * @magentoConfigFixture current_store currency/options/default GBP
     *
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ApiKeysUK.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Taxes.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Addresses.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ExchangeRates.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Products.php
     * @dataProvider UKMethodProvider
     */
    public function testUKMethodAvailability($cartType, $billingAddress, $shippingAddress, $supportedMethods)
    {
        $this->quote->create()
            ->setCustomer('Guest')
            ->setCart($cartType)
            ->setShippingMethod("FlatRate")
            ->setBillingAddress($billingAddress)
            ->setShippingAddress($shippingAddress)
            ->setPaymentMethod("StripeCheckout");

        $methods = $this->quote->getAvailablePaymentMethods();

        foreach ($supportedMethods as $method)
        {
            $this->assertContains($method, $methods, "$method is not available");
        }

        $this->assertCount(count($supportedMethods), $methods);
    }
    public function UKMethodProvider()
    {
        return [
            [
                "cartType" => "Mixed",
                "billingAddress" => "London",
                "shippingAddress" => "London",
                "supportedMethods" => [ "card", "bacs_debit" ]
            ]
        ];
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store payment/stripe_payments/active 1
     * @magentoConfigFixture current_store payment/stripe_payments_basic/stripe_mode test
     * @magentoConfigFixture current_store payment/stripe_payments/payment_flow 1
     * @magentoConfigFixture current_store payment/stripe_payments_checkout/payment_action authorize
     *
     * @magentoConfigFixture current_store currency/options/base USD
     * @magentoConfigFixture current_store currency/options/allow MYR,USD
     * @magentoConfigFixture current_store currency/options/default MYR
     *
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ApiKeysMY.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Taxes.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Addresses.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ExchangeRates.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Products.php
     * @dataProvider MYMethodProvider
     */
    public function testMYMethodAvailability($cartType, $billingAddress, $shippingAddress, $supportedMethods)
    {
        $this->quote->create()
            ->setCustomer('Guest')
            ->setCart($cartType)
            ->setShippingMethod("FlatRate")
            ->setBillingAddress($billingAddress)
            ->setShippingAddress($shippingAddress)
            ->setPaymentMethod("StripeCheckout");

        $methods = $this->quote->getAvailablePaymentMethods();

        foreach ($supportedMethods as $method)
        {
            $this->assertContains($method, $methods, "$method is not available");
        }

        $this->assertCount(count($supportedMethods), $methods);
    }
    public function MYMethodProvider()
    {
        return [
            [
                "cartType" => "Mixed",
                "billingAddress" => "Malaysia",
                "shippingAddress" => "Malaysia",
                "supportedMethods" => [ "card" ]
            ]
        ];
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store payment/stripe_payments/active 1
     * @magentoConfigFixture current_store payment/stripe_payments_basic/stripe_mode test
     * @magentoConfigFixture current_store payment/stripe_payments/payment_flow 1
     * @magentoConfigFixture current_store payment/stripe_payments_checkout/payment_action authorize
     *
     * @magentoConfigFixture current_store currency/options/base USD
     * @magentoConfigFixture current_store currency/options/allow MXN,USD
     * @magentoConfigFixture current_store currency/options/default MXN
     *
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ApiKeysMX.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Taxes.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Addresses.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ExchangeRates.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Products.php
     * @dataProvider MXMethodProvider
     */
    public function testMXMethodAvailability($cartType, $billingAddress, $shippingAddress, $supportedMethods)
    {
        $this->quote->create()
            ->setCustomer('Guest')
            ->setCart($cartType)
            ->setShippingMethod("FlatRate")
            ->setBillingAddress($billingAddress)
            ->setShippingAddress($shippingAddress)
            ->setPaymentMethod("StripeCheckout");

        $methods = $this->quote->getAvailablePaymentMethods();

        foreach ($supportedMethods as $method)
        {
            $this->assertContains($method, $methods, "$method is not available");
        }

        $this->assertCount(count($supportedMethods), $methods);
    }
    public function MXMethodProvider()
    {
        return [
            [
                "cartType" => "Mixed",
                "billingAddress" => "Mexico",
                "shippingAddress" => "Mexico",
                "supportedMethods" => [ "card" ]
            ]
        ];
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store payment/stripe_payments/active 1
     * @magentoConfigFixture current_store payment/stripe_payments_basic/stripe_mode test
     * @magentoConfigFixture current_store payment/stripe_payments/payment_flow 1
     * @magentoConfigFixture current_store payment/stripe_payments_checkout/payment_action authorize
     *
     * @magentoConfigFixture current_store currency/options/base USD
     * @magentoConfigFixture current_store currency/options/allow BRL,USD
     * @magentoConfigFixture current_store currency/options/default BRL
     *
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ApiKeysBR.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Taxes.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Addresses.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ExchangeRates.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Products.php
     * @dataProvider BRMethodProvider
     */
    public function testBRMethodAvailability($cartType, $billingAddress, $shippingAddress, $supportedMethods)
    {
        $this->quote->create()
            ->setCustomer('Guest')
            ->setCart($cartType)
            ->setShippingMethod("FlatRate")
            ->setBillingAddress($billingAddress)
            ->setShippingAddress($shippingAddress)
            ->setPaymentMethod("StripeCheckout");

        $methods = $this->quote->getAvailablePaymentMethods();

        foreach ($supportedMethods as $method)
        {
            $this->assertContains($method, $methods, "$method is not available");
        }

        $this->assertCount(count($supportedMethods), $methods);
    }
    public function BRMethodProvider()
    {
        return [
            [
                "cartType" => "Mixed",
                "billingAddress" => "Brazil",
                "shippingAddress" => "Brazil",
                "supportedMethods" => [ "card" ]
            ]
        ];
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store payment/stripe_payments/active 1
     * @magentoConfigFixture current_store payment/stripe_payments_basic/stripe_mode test
     * @magentoConfigFixture current_store payment/stripe_payments/payment_flow 1
     * @magentoConfigFixture current_store payment/stripe_payments_checkout/payment_action authorize
     *
     * @magentoConfigFixture current_store currency/options/base USD
     * @magentoConfigFixture current_store currency/options/allow CAD,USD
     * @magentoConfigFixture current_store currency/options/default CAD
     *
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ApiKeysCA.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Taxes.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Addresses.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ExchangeRates.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Products.php
     * @dataProvider CAMethodProvider
     */
    public function testCAMethodAvailability($cartType, $billingAddress, $shippingAddress, $supportedMethods)
    {
        $this->quote->create()
            ->setCustomer('Guest')
            ->setCart($cartType)
            ->setShippingMethod("FlatRate")
            ->setBillingAddress($billingAddress)
            ->setShippingAddress($shippingAddress)
            ->setPaymentMethod("StripeCheckout");

        $methods = $this->quote->getAvailablePaymentMethods();

        foreach ($supportedMethods as $method)
        {
            $this->assertContains($method, $methods, "$method is not available");
        }

        $this->assertCount(count($supportedMethods), $methods);
    }

    public function CAMethodProvider()
    {
        return [
            [
                "cartType" => "Mixed",
                "billingAddress" => "Canada",
                "shippingAddress" => "Canada",
                "supportedMethods" => [ "card" ]
            ]
        ];
    }
}
