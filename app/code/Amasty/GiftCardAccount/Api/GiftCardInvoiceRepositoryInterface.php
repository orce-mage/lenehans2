<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


namespace Amasty\GiftCardAccount\Api;

interface GiftCardInvoiceRepositoryInterface
{
    /**
     * @param int $entityId
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardInvoiceInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $entityId): \Amasty\GiftCardAccount\Api\Data\GiftCardInvoiceInterface;

    /**
     * @param int $invoiceId
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardInvoiceInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByInvoiceId(int $invoiceId): \Amasty\GiftCardAccount\Api\Data\GiftCardInvoiceInterface;

    /**
     * @param \Amasty\GiftCardAccount\Api\Data\GiftCardInvoiceInterface $invoice
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardInvoiceInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(
        \Amasty\GiftCardAccount\Api\Data\GiftCardInvoiceInterface $invoice
    ): \Amasty\GiftCardAccount\Api\Data\GiftCardInvoiceInterface;

    /**
     * @param \Amasty\GiftCardAccount\Api\Data\GiftCardInvoiceInterface $invoice
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\GiftCardAccount\Api\Data\GiftCardInvoiceInterface $invoice): bool;
}
