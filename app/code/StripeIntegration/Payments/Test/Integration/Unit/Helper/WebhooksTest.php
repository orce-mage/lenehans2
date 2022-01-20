<?php

namespace StripeIntegration\Payments\Test\Integration\Unit\Helper;

use PHPUnit\Framework\Constraint\StringContains;

class WebhooksTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->webhooks = $this->objectManager->get(\StripeIntegration\Payments\Helper\Webhooks::class);
    }

    /**
     * @magentoConfigFixture current_store payment/stripe_payments/active 1
     * @magentoConfigFixture current_store payment/stripe_payments_basic/stripe_mode test
     */
    public function testOrderLoad()
    {
        $event = [
            'type' => 'source.chargeable',
            'data' => [
                'object' => [
                    'metadata' => [
                        'Order #' => "does_not_exist"
                    ]
                ]
            ]
        ];

        $start = time();

        $this->expectExceptionMessage("Received source.chargeable webhook with Order #does_not_exist but could not find the order in Magento.");
        $this->webhooks->loadOrderFromEvent($event);

        $end = time();
        $this->assertTrue(($end - $start) < 30, "Load order timeout");
    }
}
