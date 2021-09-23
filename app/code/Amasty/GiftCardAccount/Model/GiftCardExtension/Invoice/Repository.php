<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardExtension\Invoice;

use Amasty\GiftCardAccount\Api\Data\GiftCardInvoiceInterface;
use Amasty\GiftCardAccount\Api\Data\GiftCardInvoiceInterfaceFactory;
use Amasty\GiftCardAccount\Api\GiftCardInvoiceRepositoryInterface;
use Exception;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class Repository implements GiftCardInvoiceRepositoryInterface
{
    /**
     * @var GiftCardInvoiceInterfaceFactory
     */
    private $invoiceFactory;

    /**
     * @var ResourceModel\Invoice
     */
    private $resource;

    /**
     * @var ResourceModel\CollectionFactory
     */
    private $invoiceCollectionFactory;

    /**
     * @var array
     */
    private $invoices;

    public function __construct(
        GiftCardInvoiceInterfaceFactory $invoiceFactory,
        ResourceModel\Invoice $resource,
        ResourceModel\CollectionFactory $invoiceCollectionFactory
    ) {
        $this->invoiceFactory = $invoiceFactory;
        $this->resource = $resource;
        $this->invoiceCollectionFactory = $invoiceCollectionFactory;
    }

    public function getById(int $entityId): GiftCardInvoiceInterface
    {
        if (!isset($this->orders[$entityId])) {
            /** @var GiftCardInvoiceInterface $invoice */
            $invoice = $this->invoiceFactory->create();
            $this->resource->load($invoice, $entityId);

            if (!$invoice->getEntityId()) {
                throw new NoSuchEntityException(__('Gift Card Invoice with specified ID "%1" not found.', $entityId));
            }
            $this->invoices[$entityId] = $invoice;
        }

        return $this->invoices[$entityId];
    }

    public function getByInvoiceId(int $invoiceId): GiftCardInvoiceInterface
    {
        $collection = $this->invoiceCollectionFactory->create()
            ->addFieldToFilter(GiftCardInvoiceInterface::INVOICE_ID, $invoiceId);
        $invoice = $collection->getFirstItem();

        if (!$invoice->getId()) {
            throw new NoSuchEntityException(__('Gift Card Invoice not found.'));
        }

        return $this->getById((int)$invoice->getEntityId());
    }

    public function save(GiftCardInvoiceInterface $invoice): GiftCardInvoiceInterface
    {
        try {
            $this->resource->save($invoice);
        } catch (Exception $e) {
            if ($invoice->getEntityId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save Gift Card Invoice with ID %1. Error: %2',
                        [$invoice->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new Gift Card Invoice. Error: %1', $e->getMessage()));
        }

        return $invoice;
    }

    public function delete(GiftCardInvoiceInterface $invoice): bool
    {
        try {
            $this->resource->delete($invoice);
            unset($this->invoices[$invoice->getEntityId()]);
        } catch (Exception $e) {
            if ($invoice->getEntityId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove Gift Card Invoice with ID %1. Error: %2',
                        [$invoice->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove Gift Card Invoice. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @return GiftCardInvoiceInterface
     */
    public function getEmptyInvoiceModel(): GiftCardInvoiceInterface
    {
        return $this->invoiceFactory->create();
    }
}
