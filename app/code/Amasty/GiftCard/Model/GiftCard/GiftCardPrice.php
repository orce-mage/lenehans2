<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\GiftCard;

use Amasty\GiftCard\Api\Data\GiftCardPriceInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class GiftCardPrice extends AbstractExtensibleModel implements GiftCardPriceInterface
{
    protected function _construct()
    {
        $this->_init(ResourceModel\GiftCardPrice::class);
    }

    public function getPriceId(): int
    {
        return (int)$this->_getData(GiftCardPriceInterface::PRICE_ID);
    }

    public function setPriceId(int $priceId): GiftCardPriceInterface
    {
        return $this->setData(GiftCardPriceInterface::PRICE_ID, $priceId);
    }

    public function getProductId(): int
    {
        return (int)$this->_getData(GiftCardPriceInterface::PRODUCT_ID);
    }

    public function setProductId(int $productId): GiftCardPriceInterface
    {
        return $this->setData(GiftCardPriceInterface::PRODUCT_ID, $productId);
    }

    public function getWebsiteId(): int
    {
        return (int)$this->_getData(GiftCardPriceInterface::WEBSITE_ID);
    }

    public function setWebsiteId(int $websiteId): GiftCardPriceInterface
    {
        return $this->setData(GiftCardPriceInterface::WEBSITE_ID, (int)$websiteId);
    }

    public function getAttributeId(): int
    {
        return (int)$this->_getData(GiftCardPriceInterface::ATTRIBUTE_ID);
    }

    public function setAttributeId(int $attributeId): GiftCardPriceInterface
    {
        return $this->setData(GiftCardPriceInterface::ATTRIBUTE_ID, (int)$attributeId);
    }

    public function getValue(): float
    {
        return (float)$this->_getData(GiftCardPriceInterface::VALUE);
    }

    public function setValue(float $value): GiftCardPriceInterface
    {
        return $this->setData(GiftCardPriceInterface::VALUE, $value);
    }

    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    public function setExtensionAttributes(
        \Amasty\GiftCard\Api\Data\GiftCardPriceExtensionInterface $extensionAttributes = null
    ): GiftCardPriceInterface {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
