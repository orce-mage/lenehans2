<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardExtension\Order;

use Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class Order extends AbstractExtensibleModel implements GiftCardOrderInterface
{
    protected function _construct()
    {
        $this->_init(ResourceModel\Order::class);
    }

    public function getEntityId()
    {
        return (int)$this->_getData(GiftCardOrderInterface::ENTITY_ID);
    }

    public function setEntityId($entityId)
    {
        return $this->setData(GiftCardOrderInterface::ENTITY_ID, (int)$entityId);
    }

    public function getOrderId(): int
    {
        return (int)$this->_getData(GiftCardOrderInterface::ORDER_ID);
    }

    public function setOrderId(int $orderId): GiftCardOrderInterface
    {
        return $this->setData(GiftCardOrderInterface::ORDER_ID, $orderId);
    }

    public function getGiftCards(): array
    {
        return (array)$this->_getData(GiftCardOrderInterface::GIFT_CARDS);
    }

    public function setGiftCards(array $giftCards): GiftCardOrderInterface
    {
        return $this->setData(GiftCardOrderInterface::GIFT_CARDS, $giftCards);
    }

    public function getGiftAmount(): float
    {
        return (float)$this->_getData(GiftCardOrderInterface::GIFT_AMOUNT);
    }

    public function setGiftAmount(float $giftAmount): GiftCardOrderInterface
    {
        return $this->setData(GiftCardOrderInterface::GIFT_AMOUNT, $giftAmount);
    }

    public function getBaseGiftAmount(): float
    {
        return (float)$this->_getData(GiftCardOrderInterface::BASE_GIFT_AMOUNT);
    }

    public function setBaseGiftAmount(float $baseGiftAmount): GiftCardOrderInterface
    {
        return $this->setData(GiftCardOrderInterface::BASE_GIFT_AMOUNT, $baseGiftAmount);
    }

    public function getInvoiceGiftAmount(): float
    {
        return (float)$this->_getData(GiftCardOrderInterface::INVOICE_GIFT_AMOUNT);
    }

    public function setInvoiceGiftAmount(float $giftAmount): GiftCardOrderInterface
    {
        return $this->setData(GiftCardOrderInterface::INVOICE_GIFT_AMOUNT, $giftAmount);
    }

    public function getBaseInvoiceGiftAmount(): float
    {
        return (float)$this->_getData(GiftCardOrderInterface::BASE_INVOICE_GIFT_AMOUNT);
    }

    public function setBaseInvoiceGiftAmount(float $baseGiftAmount): GiftCardOrderInterface
    {
        return $this->setData(GiftCardOrderInterface::BASE_INVOICE_GIFT_AMOUNT, $baseGiftAmount);
    }

    public function getRefundGiftAmount(): float
    {
        return (float)$this->_getData(GiftCardOrderInterface::REFUND_GIFT_AMOUNT);
    }

    public function setRefundGiftAmount(float $giftAmount): GiftCardOrderInterface
    {
        return $this->setData(GiftCardOrderInterface::REFUND_GIFT_AMOUNT, $giftAmount);
    }

    public function getBaseRefundGiftAmount(): float
    {
        return (float)$this->_getData(GiftCardOrderInterface::BASE_REFUND_GIFT_AMOUNT);
    }

    public function setBaseRefundGiftAmount(float $baseGiftAmount): GiftCardOrderInterface
    {
        return $this->setData(GiftCardOrderInterface::BASE_REFUND_GIFT_AMOUNT, $baseGiftAmount);
    }

    public function getAppliedAccounts(): array
    {
        return $this->_getData(GiftCardOrderInterface::APPLIED_ACCOUNTS) ?? [];
    }

    public function setAppliedAccounts(array $accounts): GiftCardOrderInterface
    {
        return $this->setData(GiftCardOrderInterface::APPLIED_ACCOUNTS, $accounts);
    }

    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    public function setExtensionAttributes(
        \Amasty\GiftCardAccount\Api\Data\GiftCardOrderExtensionInterface $extensionAttributes = null
    ): GiftCardOrderInterface {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
