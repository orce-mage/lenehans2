<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardExtension\Invoice;

use Amasty\GiftCardAccount\Api\Data\GiftCardInvoiceInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class Invoice extends AbstractExtensibleModel implements GiftCardInvoiceInterface
{
    protected function _construct()
    {
        $this->_init(ResourceModel\Invoice::class);
    }

    public function getEntityId()
    {
        return (int)$this->_getData(GiftCardInvoiceInterface::ENTITY_ID);
    }

    public function setEntityId($entityId)
    {
        return $this->setData(GiftCardInvoiceInterface::ENTITY_ID, (int)$entityId);
    }

    public function getInvoiceId(): int
    {
        return (int)$this->_getData(GiftCardInvoiceInterface::INVOICE_ID);
    }

    public function setInvoiceId(int $invoiceId): GiftCardInvoiceInterface
    {
        return $this->setData(GiftCardInvoiceInterface::INVOICE_ID, $invoiceId);
    }

    public function getGiftAmount(): float
    {
        return (float)$this->_getData(GiftCardInvoiceInterface::GIFT_AMOUNT);
    }

    public function setGiftAmount(float $giftAmount): GiftCardInvoiceInterface
    {
        return $this->setData(GiftCardInvoiceInterface::GIFT_AMOUNT, $giftAmount);
    }

    public function getBaseGiftAmount(): float
    {
        return (float)$this->_getData(GiftCardInvoiceInterface::BASE_GIFT_AMOUNT);
    }

    public function setBaseGiftAmount(float $baseGiftAmount): GiftCardInvoiceInterface
    {
        return $this->setData(GiftCardInvoiceInterface::BASE_GIFT_AMOUNT, $baseGiftAmount);
    }

    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    public function setExtensionAttributes(
        \Amasty\GiftCardAccount\Api\Data\GiftCardInvoiceExtensionInterface $extensionAttributes = null
    ): GiftCardInvoiceInterface {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
