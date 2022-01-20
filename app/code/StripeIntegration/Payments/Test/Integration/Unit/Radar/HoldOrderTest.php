<?php

namespace StripeIntegration\Payments\Test\Integration\Unit\Radar;

class HoldOrderTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->helper = $this->objectManager->get(\StripeIntegration\Payments\Helper\Generic::class);
        $this->paymentIntentFactory = $this->objectManager->get(\StripeIntegration\Payments\Model\PaymentIntentFactory::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testHoldOrder()
    {
        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class)
            ->loadByIncrementId('100000001');

        $order->getPayment()->setLastTransId("pi_test");

        $this->helper->holdOrder($order);

        $this->assertEquals("holded", $order->getStatus());

        $paymentIntent = $this->paymentIntentFactory->create();
        $paymentIntent->load("pi_test", "pi_id");
        $this->assertEquals("100000001", $paymentIntent->getOrderIncrementId());
    }
}
