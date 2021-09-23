<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


namespace Amasty\GiftCardAccount\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Gift Card extension attribute for order
 * also contains data of invoiced and returned orders with Gift Cards
 */
interface GiftCardOrderInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ENTITY_ID = 'entity_id';
    const ORDER_ID = 'order_id';
    const GIFT_CARDS = 'gift_cards';
    const GIFT_AMOUNT = 'gift_amount';
    const BASE_GIFT_AMOUNT = 'base_gift_amount';
    const INVOICE_GIFT_AMOUNT = 'invoice_gift_amount';
    const BASE_INVOICE_GIFT_AMOUNT = 'base_invoice_gift_amount';
    const REFUND_GIFT_AMOUNT = 'refund_gift_amount';
    const BASE_REFUND_GIFT_AMOUNT = 'base_refund_gift_amount';
    /**#@-*/

    const APPLIED_ACCOUNTS = 'order_applied_accounts';

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param int $entityId
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface
     */
    public function setEntityId($entityId);

    /**
     * @return int
     */
    public function getOrderId(): int;

    /**
     * @param int $orderId
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface
     */
    public function setOrderId(int $orderId): \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface;

    /**
     * @return \Amasty\GiftCardAccount\Api\Data\OrderGiftCardInterface[]
     */
    public function getGiftCards(): array;

    /**
     * @param mixed[] $giftCards
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface
     */
    public function setGiftCards(array $giftCards): \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface;

    /**
     * @return float
     */
    public function getGiftAmount(): float;

    /**
     * @param float $giftAmount
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface
     */
    public function setGiftAmount(float $giftAmount): \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface;

    /**
     * @return float
     */
    public function getBaseGiftAmount(): float;

    /**
     * @param float $baseGiftAmount
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface
     */
    public function setBaseGiftAmount(float $baseGiftAmount): \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface;

    /**
     * @return float
     */
    public function getInvoiceGiftAmount(): float;

    /**
     * @param float $giftAmount
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface
     */
    public function setInvoiceGiftAmount(float $giftAmount): \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface;

    /**
     * @return float
     */
    public function getBaseInvoiceGiftAmount(): float;

    /**
     * @param float $baseGiftAmount
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface
     */
    public function setBaseInvoiceGiftAmount(
        float $baseGiftAmount
    ): \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface;

    /**
     * @return float
     */
    public function getRefundGiftAmount(): float;

    /**
     * @param float $giftAmount
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface
     */
    public function setRefundGiftAmount(float $giftAmount): \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface;

    /**
     * @return float
     */
    public function getBaseRefundGiftAmount(): float;

    /**
     * @param float $baseGiftAmount
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface
     */
    public function setBaseRefundGiftAmount(
        float $baseGiftAmount
    ): \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface;

    /**
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface[]
     */
    public function getAppliedAccounts(): array;

    /**
     * Set applied gift card accounts during order placement to save them after checkout succeed
     *
     * @param \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface[] $accounts
     * @return GiftCardOrderInterface
     */
    public function setAppliedAccounts(array $accounts): \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface;

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardOrderExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Amasty\GiftCardAccount\Api\Data\GiftCardOrderExtensionInterface $extensionAttributes
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface
     */
    public function setExtensionAttributes(
        \Amasty\GiftCardAccount\Api\Data\GiftCardOrderExtensionInterface $extensionAttributes = null
    ): \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface;
}
