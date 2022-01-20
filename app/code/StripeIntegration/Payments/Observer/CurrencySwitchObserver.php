<?php

namespace StripeIntegration\Payments\Observer;

use Magento\Framework\Event\ObserverInterface;
use StripeIntegration\Payments\Helper\Logger;
use StripeIntegration\Payments\Exception\WebhookException;

class CurrencySwitchObserver implements ObserverInterface
{
    public function __construct(
        \StripeIntegration\Payments\Helper\InitialFee $helper,
        \StripeIntegration\Payments\Helper\Generic $paymentsHelper,
        \StripeIntegration\Payments\Model\Config $config,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \StripeIntegration\Payments\Helper\Serializer $serializer
    )
    {
        $this->helper = $helper;
        $this->paymentsHelper = $paymentsHelper;
        $this->config = $config;
        $this->_eventManager = $eventManager;
        $this->serializer = $serializer;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->config->isSubscriptionsEnabled())
            return;

        if (!$this->config->getConfigData("additional_info", "subscriptions"))
            return;

        $items = $this->paymentsHelper->getSessionQuote()->getAllItems();
        foreach ($items as $item)
        {
            if (!empty($item->getQtyOptions()))
                $additionalOptions = $this->helper->getAdditionalOptionsForChildrenOf($item);
            else
                $additionalOptions = $this->helper->getAdditionalOptionsForProductId($item->getProductId(), $item->getQty());

            $data = $this->serializer->serialize($additionalOptions);

            if ($data)
            {
                $item->addOption(array(
                    'product_id' => $item->getProductId(),
                    'code' => 'additional_options',
                    'value' => $data
                ));

                $item->save();
            }
        }
    }
}
