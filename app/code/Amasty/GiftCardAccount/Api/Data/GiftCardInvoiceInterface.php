<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


namespace Amasty\GiftCardAccount\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Gift Card extension attribute for invoice
 */
interface GiftCardInvoiceInterface extends ExtensibleDataInterface
{
    const ENTITY_ID = 'entity_id';
    const INVOICE_ID = 'invoice_id';
    const GIFT_AMOUNT = 'gift_amount';
    const BASE_GIFT_AMOUNT = 'base_gift_amount';

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param int $entityId
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardInvoiceInterface
     */
    public function setEntityId($entityId);

    /**
     * @return int
     */
    public function getInvoiceId(): int;

    /**
     * @param int $invoiceId
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardInvoiceInterface
     */
    public function setInvoiceId(int $invoiceId): \Amasty\GiftCardAccount\Api\Data\GiftCardInvoiceInterface;

    /**
     * @return float
     */
    public function getGiftAmount(): float;

    /**
     * @param float $giftAmount
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardInvoiceInterface
     */
    public function setGiftAmount(float $giftAmount): \Amasty\GiftCardAccount\Api\Data\GiftCardInvoiceInterface;

    /**
     * @return float
     */
    public function getBaseGiftAmount(): float;

    /**
     * @param float $baseGiftAmount
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardInvoiceInterface
     */
    public function setBaseGiftAmount(float $baseGiftAmount): \Amasty\GiftCardAccount\Api\Data\GiftCardInvoiceInterface;

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardInvoiceExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Amasty\GiftCardAccount\Api\Data\GiftCardInvoiceExtensionInterface $extensionAttributes
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardInvoiceInterface
     */
    public function setExtensionAttributes(
        \Amasty\GiftCardAccount\Api\Data\GiftCardInvoiceExtensionInterface $extensionAttributes = null
    ): \Amasty\GiftCardAccount\Api\Data\GiftCardInvoiceInterface;
}
