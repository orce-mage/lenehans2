<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Observer;

use Amasty\GiftCardAccount\Model\GiftCardExtension\Invoice\Handlers\SaveHandler as InvoiceSaveHandler;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Invoice\InvoiceExtensionRegistry;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class InvoiceExtensionSave implements ObserverInterface
{
    /**
     * @var InvoiceSaveHandler
     */
    private $invoiceSaveHandler;

    /**
     * @var InvoiceExtensionRegistry
     */
    private $invoiceExtensionRegistry;

    public function __construct(
        InvoiceExtensionRegistry $invoiceExtensionRegistry,
        InvoiceSaveHandler $invoiceSaveHandler
    ) {
        $this->invoiceSaveHandler = $invoiceSaveHandler;
        $this->invoiceExtensionRegistry = $invoiceExtensionRegistry;
    }

    public function execute(Observer $observer)
    {
        if (!($gCardInvoice = $this->invoiceExtensionRegistry->getCurrentGiftCardInvoice())) {
            return;
        }
        $invoice = $observer->getEvent()->getInvoice();
        $gCardInvoice->setInvoiceId((int)$invoice->getId());
        $extension = $invoice->getExtensionAttributes();
        $extension->setAmGiftcardInvoice($gCardInvoice);
        $invoice->setExtensionAttributes($extension);
        $this->invoiceSaveHandler->saveAttributes($invoice);
    }
}
