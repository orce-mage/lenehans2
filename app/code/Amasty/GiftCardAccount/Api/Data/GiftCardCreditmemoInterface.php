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
interface GiftCardCreditmemoInterface extends ExtensibleDataInterface
{
    const ENTITY_ID = 'entity_id';
    const CREDITMEMO_ID = 'creditmemo_id';
    const GIFT_AMOUNT = 'gift_amount';
    const BASE_GIFT_AMOUNT = 'base_gift_amount';

    /**
     * @return int
     */
    public function getEntityId(): int;

    /**
     * @param int $entityId
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoInterface
     */
    public function setEntityId($entityId): \Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoInterface;

    /**
     * @return int
     */
    public function getCreditmemoId(): int;

    /**
     * @param int $creditmemoId
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoInterface
     */
    public function setCreditmemoId(int $creditmemoId): \Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoInterface;

    /**
     * @return float
     */
    public function getGiftAmount(): float;

    /**
     * @param float $giftAmount
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoInterface
     */
    public function setGiftAmount(float $giftAmount): \Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoInterface;

    /**
     * @return float
     */
    public function getBaseGiftAmount(): float;

    /**
     * @param float $baseGiftAmount
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoInterface
     */
    public function setBaseGiftAmount(
        float $baseGiftAmount
    ): \Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoInterface;

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoExtensionInterface $extensionAttributes
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoInterface
     */
    public function setExtensionAttributes(
        \Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoExtensionInterface $extensionAttributes = null
    ): \Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoInterface;
}
