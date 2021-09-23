<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardExtension\Creditmemo;

use Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class Creditmemo extends AbstractExtensibleModel implements GiftCardCreditmemoInterface
{
    protected function _construct()
    {
        $this->_init(ResourceModel\Creditmemo::class);
    }

    public function getEntityId(): int
    {
        return (int)$this->_getData(GiftCardCreditmemoInterface::ENTITY_ID);
    }

    public function setEntityId($entityId): GiftCardCreditmemoInterface
    {
        return $this->setData(GiftCardCreditmemoInterface::ENTITY_ID, (int)$entityId);
    }

    public function getCreditmemoId(): int
    {
        return (int)$this->_getData(GiftCardCreditmemoInterface::CREDITMEMO_ID);
    }

    public function setCreditmemoId(int $creditmemoId): GiftCardCreditmemoInterface
    {
        return $this->setData(GiftCardCreditmemoInterface::CREDITMEMO_ID, $creditmemoId);
    }

    public function getGiftAmount(): float
    {
        return (float)$this->_getData(GiftCardCreditmemoInterface::GIFT_AMOUNT);
    }

    public function setGiftAmount(float $giftAmount): GiftCardCreditmemoInterface
    {
        return $this->setData(GiftCardCreditmemoInterface::GIFT_AMOUNT, $giftAmount);
    }

    public function getBaseGiftAmount(): float
    {
        return (float)$this->_getData(GiftCardCreditmemoInterface::BASE_GIFT_AMOUNT);
    }

    public function setBaseGiftAmount(float $baseGiftAmount): GiftCardCreditmemoInterface
    {
        return $this->setData(GiftCardCreditmemoInterface::BASE_GIFT_AMOUNT, $baseGiftAmount);
    }

    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    public function setExtensionAttributes(
        \Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoExtensionInterface $extensionAttributes = null
    ): \Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoInterface {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
