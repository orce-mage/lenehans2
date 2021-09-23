<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardExtension\Invoice\Handlers;

use Amasty\GiftCardAccount\Model\GiftCardExtension\Invoice\Repository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\InvoiceExtensionFactory;
use Magento\Sales\Api\Data\InvoiceInterface;

class ReadHandler
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var InvoiceExtensionFactory
     */
    private $invoiceExtensionFactory;

    public function __construct(
        Repository $repository,
        InvoiceExtensionFactory $invoiceExtensionFactory
    ) {
        $this->repository = $repository;
        $this->invoiceExtensionFactory = $invoiceExtensionFactory;
    }

    /**
     * @param InvoiceInterface $invoice
     *
     * @return InvoiceInterface
     */
    public function loadAttributes(InvoiceInterface $invoice): InvoiceInterface
    {
        $extension = $invoice->getExtensionAttributes();

        if ($extension === null) {
            $extension = $this->invoiceExtensionFactory->create();
        } elseif ($invoice->getExtensionAttributes()->getAmGiftcardInvoice() !== null) {
            return $invoice;
        }
        $invoiceId = (int)$invoice->getId();

        try {
            $giftCardInvoice = $this->repository->getByInvoiceId($invoiceId);
        } catch (NoSuchEntityException $e) {
            $giftCardInvoice = $this->repository->getEmptyInvoiceModel();
            $giftCardInvoice->setInvoiceId((int)$invoiceId);
        }
        $extension->setAmGiftcardInvoice($giftCardInvoice);
        $invoice->setExtensionAttributes($extension);

        return $invoice;
    }
}
