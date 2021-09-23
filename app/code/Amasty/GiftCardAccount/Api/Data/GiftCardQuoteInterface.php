<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


namespace Amasty\GiftCardAccount\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Gift Card extension attribute for quote
 */
interface GiftCardQuoteInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ENTITY_ID = 'entity_id';
    const QUOTE_ID = 'quote_id';
    const GIFT_CARDS = 'gift_cards';
    const GIFT_AMOUNT = 'gift_amount';
    const BASE_GIFT_AMOUNT = 'base_gift_amount';
    const GIFT_AMOUNT_USED = 'gift_amount_used';
    const BASE_GIFT_AMOUNT_USED = 'base_gift_amount_used';
    /**#@-*/

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param int $entityId
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterface
     */
    public function setEntityId($entityId);

    /**
     * @return int
     */
    public function getQuoteId(): int;

    /**
     * @param int $quoteId
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterface
     */
    public function setQuoteId(int $quoteId): \Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterface;

    /**
     * @return \Amasty\GiftCardAccount\Api\Data\OrderGiftCardInterface[]
     */
    public function getGiftCards(): array;

    /**
     * @param \Amasty\GiftCardAccount\Api\Data\OrderGiftCardInterface[] $giftCards
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterface
     */
    public function setGiftCards(array $giftCards): \Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterface;

    /**
     * @return float
     */
    public function getGiftAmount(): float;

    /**
     * @param float $giftAmount
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterface
     */
    public function setGiftAmount(float $giftAmount): \Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterface;

    /**
     * @return float
     */
    public function getBaseGiftAmount(): float;

    /**
     * @param float $baseGiftAmount
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterface
     */
    public function setBaseGiftAmount(float $baseGiftAmount): \Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterface;

    /**
     * @return float
     */
    public function getGiftAmountUsed(): float;

    /**
     * @param float $giftAmount
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterface
     */
    public function setGiftAmountUsed(float $giftAmount): \Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterface;

    /**
     * @return float
     */
    public function getBaseGiftAmountUsed(): float;

    /**
     * @param float $baseGiftAmount
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterface
     */
    public function setBaseGiftAmountUsed(
        float $baseGiftAmount
    ): \Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterface;

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardQuoteExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Amasty\GiftCardAccount\Api\Data\GiftCardQuoteExtensionInterface $extensionAttributes
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterface
     */
    public function setExtensionAttributes(
        \Amasty\GiftCardAccount\Api\Data\GiftCardQuoteExtensionInterface $extensionAttributes = null
    ): \Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterface;
}
