<?php

namespace StripeIntegration\Payments\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use StripeIntegration\Payments\Helper\Logger;

class WebhooksConfigurationObserver extends AbstractDataAssignObserver
{
    public function __construct(
        \StripeIntegration\Payments\Model\Config $config,
        \StripeIntegration\Payments\Helper\Generic $helper,
        \StripeIntegration\Payments\Helper\WebhooksSetup $webhooksSetup
    )
    {
        $this->config = $config;
        $this->helper = $helper;
        $this->webhooksSetup = $webhooksSetup;
    }

    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        $this->webhooksSetup->onWebhookCreated($event);
    }
}
