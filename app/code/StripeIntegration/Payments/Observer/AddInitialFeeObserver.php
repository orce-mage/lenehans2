<?php

namespace StripeIntegration\Payments\Observer;

use Magento\Framework\Event\ObserverInterface;
use StripeIntegration\Payments\Helper\Logger;
use StripeIntegration\Payments\Exception\WebhookException;

class AddInitialFeeObserver implements ObserverInterface
{
    public function __construct(
        \StripeIntegration\Payments\Helper\InitialFee $helper,
        \StripeIntegration\Payments\Helper\Generic $paymentsHelper,
        \StripeIntegration\Payments\Model\Config $config,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $dbTransaction,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \StripeIntegration\Payments\Helper\Serializer $serializer
    )
    {
        $this->helper = $helper;
        $this->paymentsHelper = $paymentsHelper;
        $this->config = $config;
        $this->_stripeCustomer = $paymentsHelper->getCustomerModel();
        $this->_eventManager = $eventManager;
        $this->invoiceService = $invoiceService;
        $this->dbTransaction = $dbTransaction;
        $this->serializer = $serializer;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // $item = $observer->getEvent()->getData('quote_item');
        // $item = ( $item->getParentItem() ? $item->getParentItem() : $item );
        // $price = 100; //set your price here
        // $item->setCustomPrice($price);
        // $item->setOriginalCustomPrice($price);
        // $item->getProduct()->setIsSuperMode(true);

        if (!$this->config->isSubscriptionsEnabled())
            return;

        if (!$this->config->getConfigData("additional_info", "subscriptions"))
            return;

        $item = $observer->getQuoteItem();

        if (!$item)
            return;

        if (!empty($item->getQtyOptions()))
            $additionalOptions = $this->helper->getAdditionalOptionsForChildrenOf($item);
        else
            $additionalOptions = $this->helper->getAdditionalOptionsForProductId($item->getProductId(), $item->getQtyToAdd());

        $data = $this->serializer->serialize($additionalOptions);

        if ($data)
        {
            $item->addOption(array(
                'product_id' => $item->getProductId(),
                'code' => 'additional_options',
                'value' => $data
            ));
        }
    }
}
