<?php

namespace StripeIntegration\Payments\Test\Integration\Frontend\CheckoutPage\CardsEmbedded\AuthorizeCapture;

class TaxInclusivePricesTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->cartManagement = $this->objectManager->get(\Magento\Quote\Api\CartManagementInterface::class);
        $this->webhooks = $this->objectManager->get(\StripeIntegration\Payments\Helper\Webhooks::class);
        $this->request = $this->objectManager->get(\Magento\Framework\App\Request\Http::class);
        $this->helper = $this->objectManager->get(\StripeIntegration\Payments\Helper\Generic::class);
        $this->stripeConfig = $this->objectManager->get(\StripeIntegration\Payments\Model\Config::class);
        $this->subscriptions = $this->objectManager->get(\StripeIntegration\Payments\Helper\Subscriptions::class);
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->quote = new \StripeIntegration\Payments\Test\Integration\Helper\Quote();
    }

    /**
     * @ticket MAGENTO-73
     *
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store payment/stripe_payments/active 1
     * @magentoConfigFixture current_store payment/stripe_payments_basic/stripe_mode test
     * @magentoConfigFixture current_store payment/stripe_payments/payment_flow 0
     * @magentoConfigFixture current_store payment/stripe_payments/payment_action authorize_capture
     *
     * @magentoConfigFixture current_store customer/create_account/default_group 1
     * @magentoConfigFixture current_store customer/create_account/auto_group_assign 1
     * @magentoConfigFixture current_store tax/classes/shipping_tax_class 2
     * @magentoConfigFixture current_store tax/calculation/price_includes_tax 1
     * @magentoConfigFixture current_store tax/calculation/shipping_includes_tax 1
     * @magentoConfigFixture current_store tax/calculation/discount_tax 1
     *
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ApiKeys.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Taxes.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Addresses.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Products.php
     */
    public function testMixedCart()
    {
        $calculation = $this->objectManager->get(\Magento\Tax\Model\Calculation::class);
        $this->quote->create()
            ->setCustomer('Guest')
            ->setCart("Mixed")
            ->setShippingAddress("California")
            ->setShippingMethod("FlatRate")
            ->setBillingAddress("California")
            ->setPaymentMethod("SuccessCard");

        $order = $this->quote->mockOrder();
        $orderItem = null;
        foreach ($order->getAllItems() as $item)
        {
            if ($item->getSku() == "simple-monthly-subscription-initial-fee-product")
                $orderItem = $item;
        }
        $this->assertNotEmpty($orderItem);

        $product = $this->helper->loadProductById($orderItem->getProductId());
        $subscriptionProfile = $this->subscriptions->getSubscriptionDetails($product, $order, $orderItem, $isDryRun = true, $trialEnd = null, $useStoreCurrency = true);

        $expectedProfile = [
            "name" => "Simple Monthly Subscription + Initial Fee",
            "qty" => 2,
            "interval" => "month",
            "interval_count" => 1,
            "amount_magento" => 10,
            "amount_stripe" => 1000,
            "initial_fee_stripe" => 300,
            "initial_fee_magento" => 3,
            "discount_amount_magento" => 0,
            "discount_amount_stripe" => 0,
            "shipping_magento" => 10,
            "shipping_stripe" => 1000,
            "currency" => "usd",
            "tax_percent" => 8.25,
            "tax_amount_item" => 1.53,
            "tax_amount_shipping" => 0.76,
            "tax_amount_initial_fee" => 0.46,
            "trial_end" => null,
            "trial_days" => 0,
            "coupon_code" => null
        ];

        foreach ($expectedProfile as $key => $value)
        {
            $this->assertEquals($value, $subscriptionProfile[$key], $key);
        }
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store payment/stripe_payments/active 1
     * @magentoConfigFixture current_store payment/stripe_payments_basic/stripe_mode test
     * @magentoConfigFixture current_store payment/stripe_payments/payment_flow 0
     * @magentoConfigFixture current_store payment/stripe_payments/payment_action authorize_capture
     *
     * @magentoConfigFixture current_store customer/create_account/default_group 1
     * @magentoConfigFixture current_store customer/create_account/auto_group_assign 1
     * @magentoConfigFixture current_store tax/classes/shipping_tax_class 2
     * @magentoConfigFixture current_store tax/calculation/price_includes_tax 1
     * @magentoConfigFixture current_store tax/calculation/shipping_includes_tax 1
     * @magentoConfigFixture current_store tax/calculation/discount_tax 1
     *
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ApiKeys.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Taxes.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Addresses.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Products.php
     */
    public function testTrialCart()
    {
        $this->quote->create()
            ->setCustomer('Guest')
            ->setCart("Trial")
            ->setShippingAddress("California")
            ->setShippingMethod("FlatRate")
            ->setBillingAddress("California")
            ->setPaymentMethod("SuccessCard");

        $quote = $this->quote->getQuote();

        foreach ($quote->getAllItems() as $quoteItem)
        {
            $product = $this->helper->loadProductById($quoteItem->getProductId());
            $profile = $this->subscriptions->getSubscriptionDetails($product, $quote, $quoteItem, true, null, true);
            if ($quoteItem->getSku() == "virtual-trial-monthly-subscription-product")
            {
                $this->assertEquals("Virtual Trial Monthly Subscription", $profile["name"]);
                $this->assertEquals(1, $profile["qty"]);
                $this->assertEquals("month", $profile["interval"]);
                $this->assertEquals(1, $profile["interval_count"]);
                $this->assertEquals(10, $profile["amount_magento"]);
                $this->assertEquals(1000, $profile["amount_stripe"]);
                $this->assertEquals(0, $profile["initial_fee_stripe"]);
                $this->assertEquals(0, $profile["initial_fee_magento"]);
                $this->assertEquals(0, $profile["discount_amount_magento"]);
                $this->assertEquals(0, $profile["discount_amount_stripe"]);
                $this->assertEquals(0, $profile["shipping_magento"]);
                $this->assertEquals(0, $profile["shipping_stripe"]);
                $this->assertEquals(8.25, $profile["tax_percent"]);
                $this->assertEquals(0.76, $profile["tax_amount_item"]);
                $this->assertEquals(0, $profile["tax_amount_shipping"]);
                $this->assertEquals(0, $profile["tax_amount_initial_fee"]);
                $this->assertEmpty($profile["trial_end"]);
                $this->assertEquals(14, $profile["trial_days"]);
                $this->assertEmpty($profile["coupon_code"]);
            }
            else if ($quoteItem->getSku() == "simple-trial-monthly-subscription-product")
            {
                $this->assertEquals("Simple Trial Monthly Subscription", $profile["name"]);
                $this->assertEquals(1, $profile["qty"]);
                $this->assertEquals("month", $profile["interval"]);
                $this->assertEquals(1, $profile["interval_count"]);
                $this->assertEquals(10, $profile["amount_magento"]);
                $this->assertEquals(1000, $profile["amount_stripe"]);
                $this->assertEquals(0, $profile["initial_fee_stripe"]);
                $this->assertEquals(0, $profile["initial_fee_magento"]);
                $this->assertEquals(0, $profile["discount_amount_magento"]);
                $this->assertEquals(0, $profile["discount_amount_stripe"]);
                $this->assertEquals(5, $profile["shipping_magento"]);
                $this->assertEquals(500, $profile["shipping_stripe"]);
                $this->assertEquals(8.25, $profile["tax_percent"]);
                $this->assertEquals(0.76, $profile["tax_amount_item"]);
                $this->assertEquals(0.39, $profile["tax_amount_shipping"]);
                $this->assertEquals(0, $profile["tax_amount_initial_fee"]);
                $this->assertEmpty($profile["trial_end"]);
                $this->assertEquals(14, $profile["trial_days"]);
                $this->assertEmpty($profile["coupon_code"]);
            }
        }

        $order = $this->quote->mockOrder();

        foreach ($order->getAllItems() as $orderItem)
        {
            $product = $this->helper->loadProductById($orderItem->getProductId());
            $profile = $this->subscriptions->getSubscriptionDetails($product, $order, $orderItem, true, null, true);
            if ($orderItem->getSku() == "virtual-trial-monthly-subscription-product")
            {
                $this->assertEquals("Virtual Trial Monthly Subscription", $profile["name"]);
                $this->assertEquals(1, $profile["qty"]);
                $this->assertEquals("month", $profile["interval"]);
                $this->assertEquals(1, $profile["interval_count"]);
                $this->assertEquals(10, $profile["amount_magento"]);
                $this->assertEquals(1000, $profile["amount_stripe"]);
                $this->assertEquals(0, $profile["initial_fee_stripe"]);
                $this->assertEquals(0, $profile["initial_fee_magento"]);
                $this->assertEquals(0, $profile["discount_amount_magento"]);
                $this->assertEquals(0, $profile["discount_amount_stripe"]);
                $this->assertEquals(0, $profile["shipping_magento"]);
                $this->assertEquals(0, $profile["shipping_stripe"]);
                $this->assertEquals(8.25, $profile["tax_percent"]);
                $this->assertEquals(0.76, $profile["tax_amount_item"]);
                $this->assertEquals(0, $profile["tax_amount_shipping"]);
                $this->assertEquals(0, $profile["tax_amount_initial_fee"]);
                $this->assertEmpty($profile["trial_end"]);
                $this->assertEquals(14, $profile["trial_days"]);
                $this->assertEmpty($profile["coupon_code"]);
            }
            else if ($orderItem->getSku() == "simple-trial-monthly-subscription-product")
            {
                $this->assertEquals("Simple Trial Monthly Subscription", $profile["name"]);
                $this->assertEquals(1, $profile["qty"]);
                $this->assertEquals("month", $profile["interval"]);
                $this->assertEquals(1, $profile["interval_count"]);
                $this->assertEquals(10, $profile["amount_magento"]);
                $this->assertEquals(1000, $profile["amount_stripe"]);
                $this->assertEquals(0, $profile["initial_fee_stripe"]);
                $this->assertEquals(0, $profile["initial_fee_magento"]);
                $this->assertEquals(0, $profile["discount_amount_magento"]);
                $this->assertEquals(0, $profile["discount_amount_stripe"]);
                $this->assertEquals(5, $profile["shipping_magento"]);
                $this->assertEquals(500, $profile["shipping_stripe"]);
                $this->assertEquals(8.25, $profile["tax_percent"]);
                $this->assertEquals(0.76, $profile["tax_amount_item"]);
                $this->assertEquals(0.39, $profile["tax_amount_shipping"]);
                $this->assertEquals(0, $profile["tax_amount_initial_fee"]);
                $this->assertEmpty($profile["trial_end"]);
                $this->assertEquals(14, $profile["trial_days"]);
                $this->assertEmpty($profile["coupon_code"]);
            }
        }

        $uiConfigProvider = $this->objectManager->get(\StripeIntegration\Payments\Model\Ui\ConfigProvider::class);
        $uiConfig = $uiConfigProvider->getConfig();
        $this->assertNotEmpty($uiConfig["payment"]["stripe_payments"]["trialingSubscriptions"]);
        $trialSubscriptionsConfig = $uiConfig["payment"]["stripe_payments"]["trialingSubscriptions"];

        $this->assertEquals($order->getSubtotalInclTax(), $trialSubscriptionsConfig["subscriptions_total"], "Subtotal");
        $this->assertEquals($order->getBaseSubtotalInclTax(), $trialSubscriptionsConfig["base_subscriptions_total"], "Base Subtotal");

        $this->assertEquals($order->getShippingInclTax(), $trialSubscriptionsConfig["shipping_total"], "Shipping");
        $this->assertEquals($order->getBaseShippingInclTax(), $trialSubscriptionsConfig["base_shipping_total"], "Base Shipping");

        $this->assertEquals($order->getDiscountAmount(), $trialSubscriptionsConfig["discount_total"], "Discount");
        $this->assertEquals($order->getBaseDiscountAmount(), $trialSubscriptionsConfig["base_discount_total"], "Base Discount");

        $this->assertEquals($order->getTaxAmount(), $trialSubscriptionsConfig["tax_total"], "Tax");
        $this->assertEquals($order->getBaseTaxAmount(), $trialSubscriptionsConfig["tax_total"], "Base Tax");
    }
}
