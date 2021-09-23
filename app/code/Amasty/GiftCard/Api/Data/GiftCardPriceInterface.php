<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface GiftCardPriceInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const PRICE_ID = 'price_id';
    const PRODUCT_ID = 'product_id';
    const WEBSITE_ID = 'website_id';
    const ATTRIBUTE_ID = 'attribute_id';
    const VALUE = 'value';
    /**#@-*/

    /**
     * @return int
     */
    public function getPriceId(): int;

    /**
     * @param int $priceId
     *
     * @return \Amasty\GiftCard\Api\Data\GiftCardPriceInterface
     */
    public function setPriceId(int $priceId): \Amasty\GiftCard\Api\Data\GiftCardPriceInterface;

    /**
     * @return int
     */
    public function getProductId(): int;

    /**
     * @param int $productId
     *
     * @return \Amasty\GiftCard\Api\Data\GiftCardPriceInterface
     */
    public function setProductId(int $productId): \Amasty\GiftCard\Api\Data\GiftCardPriceInterface;

    /**
     * @return int
     */
    public function getWebsiteId(): int;

    /**
     * @param int $websiteId
     *
     * @return \Amasty\GiftCard\Api\Data\GiftCardPriceInterface
     */
    public function setWebsiteId(int $websiteId): \Amasty\GiftCard\Api\Data\GiftCardPriceInterface;

    /**
     * @return int
     */
    public function getAttributeId(): int;

    /**
     * @param int $attributeId
     *
     * @return \Amasty\GiftCard\Api\Data\GiftCardPriceInterface
     */
    public function setAttributeId(int $attributeId): \Amasty\GiftCard\Api\Data\GiftCardPriceInterface;

    /**
     * @return float
     */
    public function getValue(): float;

    /**
     * @param float $value
     *
     * @return \Amasty\GiftCard\Api\Data\GiftCardPriceInterface
     */
    public function setValue(float $value): \Amasty\GiftCard\Api\Data\GiftCardPriceInterface;

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Amasty\GiftCard\Api\Data\GiftCardPriceExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Amasty\GiftCard\Api\Data\GiftCardPriceExtensionInterface $extensionAttributes
     * @return \Amasty\GiftCard\Api\Data\GiftCardPriceInterface
     */
    public function setExtensionAttributes(
        \Amasty\GiftCard\Api\Data\GiftCardPriceExtensionInterface $extensionAttributes = null
    ): \Amasty\GiftCard\Api\Data\GiftCardPriceInterface;
}
