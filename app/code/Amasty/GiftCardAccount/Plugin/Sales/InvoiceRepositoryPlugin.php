<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Plugin\Sales;

use Amasty\GiftCardAccount\Model\GiftCardExtension\Invoice\Handlers\ReadHandler as InvoiceReadHandler;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Invoice\Handlers\SaveHandler as InvoiceSaveHandler;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Handlers\ReadHandler as OrderReadHandler;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Handlers\SaveHandler as OrderSaveHandler;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;

class InvoiceRepositoryPlugin
{
    /**
     * @var OrderReadHandler
     */
    private $orderReadHandler;

    /**
     * @var OrderSaveHandler
     */
    private $orderSaveHandler;

    /**
     * @var InvoiceReadHandler
     */
    private $invoiceReadHandler;

    /**
     * @var InvoiceSaveHandler
     */
    private $invoiceSaveHandler;

    /**
     * @var \Amasty\GiftCardAccount\Api\Data\GiftCardInvoiceInterface|null
     */
    private $invoiceExtension = null;

    public function __construct(
        OrderReadHandler $orderReadHandler,
        OrderSaveHandler $orderSaveHandler,
        InvoiceReadHandler $invoiceReadHandler,
        InvoiceSaveHandler $invoiceSaveHandler
    ) {
        $this->orderReadHandler = $orderReadHandler;
        $this->orderSaveHandler = $orderSaveHandler;
        $this->invoiceReadHandler = $invoiceReadHandler;
        $this->invoiceSaveHandler = $invoiceSaveHandler;
    }

    /**
     * @param InvoiceRepositoryInterface $subject
     * @param InvoiceInterface $invoice
     *
     * @return InvoiceInterface
     */
    public function afterGet(InvoiceRepositoryInterface $subject, InvoiceInterface $invoice): InvoiceInterface
    {
        if ($order = $invoice->getOrder()) {
            $this->orderReadHandler->loadAttributes($order);
        }
        $this->invoiceReadHandler->loadAttributes($invoice);

        return $invoice;
    }

    /**
     * @param InvoiceRepositoryInterface $subject
     * @param SearchResultsInterface $searchResult
     *
     * @return SearchResultsInterface
     */
    public function afterGetList(
        InvoiceRepositoryInterface $subject,
        SearchResultsInterface $searchResult
    ): SearchResultsInterface {
        foreach ($searchResult->getItems() as $invoice) {
            $invoices[] = $this->afterGet($subject, $invoice);
        }

        return $searchResult;
    }

    /**
     * @param InvoiceRepositoryInterface $subject
     * @param InvoiceInterface $invoice
     *
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function beforeSave(
        InvoiceRepositoryInterface $subject,
        InvoiceInterface $invoice
    ) {
        if ($order = $invoice->getOrder()) {
            $this->orderSaveHandler->saveAttributes($order);
        }

        if ($invoice->getExtensionAttributes() && $invoice->getExtensionAttributes()->getAmGiftcardInvoice()) {
            $this->invoiceExtension = $invoice->getExtensionAttributes()->getAmGiftcardInvoice();
        }
    }

    /**
     * @param InvoiceRepositoryInterface $subject
     * @param InvoiceInterface $invoice
     *
     * @return InvoiceInterface
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function afterSave(
        InvoiceRepositoryInterface $subject,
        InvoiceInterface $invoice
    ): InvoiceInterface {
        if ($gCardInvoice = $this->invoiceExtension) {
            $extension = $invoice->getExtensionAttributes();
            $gCardInvoice->setInvoiceId((int)$invoice->getId());
            $extension->setAmGiftcardInvoice($gCardInvoice);
            $invoice->setExtensionAttributes($extension);
            $this->invoiceSaveHandler->saveAttributes($invoice);
        }

        return $invoice;
    }
}
