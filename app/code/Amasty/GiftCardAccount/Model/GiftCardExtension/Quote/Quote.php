<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardExtension\Quote;

use Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class Quote extends AbstractExtensibleModel implements GiftCardQuoteInterface
{
    protected function _construct()
    {
        $this->_init(ResourceModel\Quote::class);
    }

    public function getEntityId()
    {
        return (int)$this->_getData(GiftCardQuoteInterface::ENTITY_ID);
    }

    public function setEntityId($entityId)
    {
        return $this->setData(GiftCardQuoteInterface::ENTITY_ID, (int)$entityId);
    }

    public function getQuoteId(): int
    {
        return (int)$this->_getData(GiftCardQuoteInterface::QUOTE_ID);
    }

    public function setQuoteId(int $quoteId): GiftCardQuoteInterface
    {
        return $this->setData(GiftCardQuoteInterface::QUOTE_ID, (int)$quoteId);
    }

    public function getGiftCards(): array
    {
        return (array)$this->_getData(GiftCardQuoteInterface::GIFT_CARDS);
    }

    public function setGiftCards(array $giftCards): GiftCardQuoteInterface
    {
        return $this->setData(GiftCardQuoteInterface::GIFT_CARDS, $giftCards);
    }

    public function getGiftAmount(): float
    {
        return (float)$this->_getData(GiftCardQuoteInterface::GIFT_AMOUNT);
    }

    public function setGiftAmount(float $giftAmount): GiftCardQuoteInterface
    {
        return $this->setData(GiftCardQuoteInterface::GIFT_AMOUNT, $giftAmount);
    }

    public function getBaseGiftAmount(): float
    {
        return (float)$this->_getData(GiftCardQuoteInterface::BASE_GIFT_AMOUNT);
    }

    public function setBaseGiftAmount(float $baseGiftAmount): GiftCardQuoteInterface
    {
        return $this->setData(GiftCardQuoteInterface::BASE_GIFT_AMOUNT, $baseGiftAmount);
    }

    public function getGiftAmountUsed(): float
    {
        return (float)$this->_getData(GiftCardQuoteInterface::GIFT_AMOUNT_USED);
    }

    public function setGiftAmountUsed(float $giftAmount): GiftCardQuoteInterface
    {
        return $this->setData(GiftCardQuoteInterface::GIFT_AMOUNT_USED, $giftAmount);
    }

    public function getBaseGiftAmountUsed(): float
    {
        return (float)$this->_getData(GiftCardQuoteInterface::BASE_GIFT_AMOUNT_USED);
    }

    public function setBaseGiftAmountUsed(float $baseGiftAmount): GiftCardQuoteInterface
    {
        return $this->setData(GiftCardQuoteInterface::BASE_GIFT_AMOUNT_USED, $baseGiftAmount);
    }

    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    public function setExtensionAttributes(
        \Amasty\GiftCardAccount\Api\Data\GiftCardQuoteExtensionInterface $extensionAttributes = null
    ): GiftCardQuoteInterface {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
