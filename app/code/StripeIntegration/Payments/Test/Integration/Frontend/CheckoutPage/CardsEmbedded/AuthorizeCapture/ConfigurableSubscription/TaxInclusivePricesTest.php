<?php

namespace StripeIntegration\Payments\Test\Integration\Frontend\CheckoutPage\CardsEmbedded\AuthorizeCapture\ConfigurableSubscription;

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
    public function testSubscriptionDetails()
    {
        $calculation = $this->objectManager->get(\Magento\Tax\Model\Calculation::class);
        $this->quote->create()
            ->setCustomer('Guest')
            ->setCart("ConfigurableSubscription")
            ->setShippingAddress("California")
            ->setShippingMethod("FlatRate")
            ->setBillingAddress("California")
            ->setPaymentMethod("SuccessCard");

        $order = $this->quote->mockOrder();
        $orderItem = null;
        foreach ($order->getAllItems() as $item)
        {
            if ($item->getProductType() == "simple")
                $orderItem = $item;
        }
        $this->assertNotEmpty($orderItem);

        $product = $this->helper->loadProductById($orderItem->getProductId());
        $subscriptionProfile = $this->subscriptions->getSubscriptionDetails($product, $order, $orderItem, $isDryRun = true, $trialEnd = null, $useStoreCurrency = true);

        $expectedProfile = [
            "name" => "Simple Monthly Subscription",
            "qty" => 1,
            "interval" => "month",
            "interval_count" => 1,
            "amount_magento" => 10,
            "amount_stripe" => 1000,
            "initial_fee_stripe" => 0,
            "initial_fee_magento" => 0,
            "discount_amount_magento" => 0,
            "discount_amount_stripe" => 0,
            "shipping_magento" => 5,
            "shipping_stripe" => 500,
            "currency" => "usd",
            "tax_percent" => 8.25,
            "tax_amount_item" => 0.76,
            "tax_amount_shipping" => 0.38,
            "tax_amount_initial_fee" => 0,
            "trial_end" => null,
            "trial_days" => 0,
            "coupon_code" => null
        ];

        foreach ($expectedProfile as $key => $value)
        {
            $this->assertEquals($value, $subscriptionProfile[$key], $key);
        }
    }
}
