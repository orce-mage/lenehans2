<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Observer;

use Amasty\GiftCardAccount\Model\GiftCardExtension\Invoice\InvoiceExtensionRegistry;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Handlers\SaveHandler as OrderSaveHandler;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\InvoiceInterface;

class InvoicePay implements ObserverInterface
{
    /**
     * @var OrderSaveHandler
     */
    private $orderSaveHandler;

    /**
     * @var InvoiceExtensionRegistry
     */
    private $invoiceExtensionRegistry;

    public function __construct(
        OrderSaveHandler $orderSaveHandler,
        InvoiceExtensionRegistry $invoiceExtensionRegistry
    ) {
        $this->orderSaveHandler = $orderSaveHandler;
        $this->invoiceExtensionRegistry = $invoiceExtensionRegistry;
    }

    /**
     * First save of new invoice doesn't use repository
     * register invoice extension object to save it after we have invoice id
     *
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        /** @var InvoiceInterface $invoice */
        $invoice = $observer->getEvent()->getInvoice();

        if (!$invoice) {
            return $this;
        }
        $order = $invoice->getOrder();

        if ($order->getEntityId()) {
            $this->orderSaveHandler->saveAttributes($order);
        }

        if ($invoice->getExtensionAttributes() && $invoice->getExtensionAttributes()->getAmGiftcardInvoice()) {
            $this->invoiceExtensionRegistry->setCurrentGiftCardInvoice(
                $invoice->getExtensionAttributes()->getAmGiftcardInvoice()
            );
        }

        return $this;
    }
}
