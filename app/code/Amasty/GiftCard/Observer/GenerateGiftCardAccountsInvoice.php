<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Observer;

use Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
use Amasty\GiftCard\Model\ConfigProvider;
use Amasty\GiftCard\Model\GiftCard\Product\Type\GiftCard;
use Amasty\GiftCard\Utils\AccountGenerator;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Item;

class GenerateGiftCardAccountsInvoice implements ObserverInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var AccountGenerator
     */
    private $accountGenerator;

    public function __construct(
        ConfigProvider $configProvider,
        AccountGenerator $accountGenerator
    ) {
        $this->configProvider = $configProvider;
        $this->accountGenerator = $accountGenerator;
    }

    public function execute(Observer $observer)
    {
        $storeId = $observer->getInvoice()->getStoreId();

        if (!$this->configProvider->isEnabled($storeId)) {
            return;
        }
        $orderPaid = false;
        $invoice = $observer->getEvent()->getInvoice();
        $order = $invoice->getOrder();

        if ((abs((float)$order->getBaseGrandTotal() - (float)$invoice->getBaseGrandTotal()) < 0.0001)) {
            $orderPaid = true;
        }

        /** @var Item $orderItem */
        foreach ($order->getAllItems() as $orderItem) {
            if ($orderItem->getProductType() !== GiftCard::TYPE_AMGIFTCARD) {
                continue;
            }

            $productOptions = $orderItem->getProductOptions();
            $generatedCodes = $productOptions[GiftCardOptionInterface::GIFTCARD_CREATED_CODES] ?? [];

            if (!empty($generatedCodes) && (count($generatedCodes) == $orderItem->getQtyOrdered())) {
                continue;
            }

            $productOptions[GiftCardOptionInterface::SENDER_EMAIL] = $order->getCustomerEmail();
            $orderItem->setProductOptions($productOptions);

            if ($orderPaid) {
                $qty = (int)$orderItem->getQtyInvoiced();
            } else {
                $qty = $this->getInvoicedQty($orderItem, $invoice);
            }

            if ($qty > 0) {
                $this->accountGenerator->generateFromOrderItem($orderItem, $qty);
            }
        }
    }

    /**
     * @param OrderItemInterface $orderItem
     * @param InvoiceInterface $invoice
     *
     * @return int
     */
    private function getInvoicedQty(OrderItemInterface $orderItem, InvoiceInterface $invoice): int
    {
        $qty = 0;

        foreach ($invoice->getItems() as $invoiceItem) {
            if ($invoiceItem->getOrderItemId() === $orderItem->getItemId()
                && $invoice->getState() == Invoice::STATE_PAID
            ) {
                $qty = $invoiceItem->getQty();
            }
        }

        return (int)$qty;
    }
}
