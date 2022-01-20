<?php

namespace StripeIntegration\Payments\Test\Integration\Unit\Radar;

class UnholdOrderTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->cartManagement = $this->objectManager->get(\Magento\Quote\Api\CartManagementInterface::class);
        $this->webhooks = $this->objectManager->get(\StripeIntegration\Payments\Helper\Webhooks::class);
        $this->request = $this->objectManager->get(\Magento\Framework\App\Request\Http::class);
        $this->helper = $this->objectManager->get(\StripeIntegration\Payments\Helper\Generic::class);
    }

    /**
     * @magentoConfigFixture current_store payment/stripe_payments/active 1
     * @magentoConfigFixture current_store payment/stripe_payments_basic/stripe_mode test
     * @magentoConfigFixture current_store payment/stripe_payments/payment_flow 0
     *
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/ApiKeys.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Data/Products.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Quotes/USGuestQuote.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Carts/NormalCart.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Quotes/ShippingAddress/NewYorkAddress.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Quotes/ShippingMethod/FlatRateShippingMethod.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Quotes/BillingAddress/NewYorkAddress.php
     * @magentoDataFixture ../../../../app/code/StripeIntegration/Payments/Test/Integration/_files/Quotes/PaymentMethod/ElevatedRiskCard.php
     */
    public function testUnholdOrder()
    {
        // Tests MAGENTO-57
        $quote = $this->objectManager->create(\Magento\Quote\Model\Quote::class);
        $quote->load('test_quote', 'reserved_order_id');

        $order = $this->cartManagement->submit($quote);

        $this->assertEquals("holded", $order->getStatus());

        $paymentIntentId = $order->getPayment()->getLastTransId();
        $this->request->setMethod("POST");
        $this->request->setContent('{
  "id": "evt_xxx_'.time().'",
  "object": "event",
  "api_version": "2020-03-02",
  "created": 1626429207,
  "data": {
    "object": {
      "id": "prv_1JDnB8HLyfDWKHBq36KwlmhZ",
      "object": "review",
      "billing_zip": null,
      "charge": null,
      "closed_reason": "approved",
      "created": 1626427074,
      "ip_address": null,
      "ip_address_location": null,
      "livemode": false,
      "open": false,
      "opened_reason": "rule",
      "payment_intent": "'.$paymentIntentId.'",
      "reason": "approved",
      "session": null
    }
  },
  "livemode": false,
  "pending_webhooks": 1,
  "request": {
    "id": "req_jAt7m1LnugPvrn",
    "idempotency_key": null
  },
  "type": "review.closed"
}');

        $this->webhooks->dispatchEvent();

        // Refresh the order object
        $order = $this->helper->loadOrderByIncrementId("test_quote");
        $this->assertEquals("processing", $order->getStatus());
    }
}
