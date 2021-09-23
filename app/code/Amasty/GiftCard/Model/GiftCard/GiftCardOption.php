<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\GiftCard;

use Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class GiftCardOption extends AbstractExtensibleModel implements GiftCardOptionInterface
{
    public function getAmGiftcardAmount(): float
    {
        return (float)$this->_getData(GiftCardOptionInterface::GIFTCARD_AMOUNT);
    }

    public function setAmGiftcardAmount($cardAmount): GiftCardOptionInterface
    {
        return $this->setData(GiftCardOptionInterface::GIFTCARD_AMOUNT, $cardAmount);
    }

    public function getAmGiftcardAmountCustom(): float
    {
        return (float)$this->_getData(GiftCardOptionInterface::CUSTOM_GIFTCARD_AMOUNT);
    }

    public function setAmGiftcardAmountCustom($cardAmount): GiftCardOptionInterface
    {
        return $this->setData(GiftCardOptionInterface::CUSTOM_GIFTCARD_AMOUNT, $cardAmount);
    }

    public function setAmGiftcardType($type): GiftCardOptionInterface
    {
        return $this->setData(GiftCardOptionInterface::GIFTCARD_TYPE, $type);
    }

    public function getAmGiftcardType(): int
    {
        return (int)$this->_getData(GiftCardOptionInterface::GIFTCARD_TYPE);
    }

    public function getAmGiftcardSenderName()
    {
        return $this->_getData(GiftCardOptionInterface::SENDER_NAME);
    }

    public function setAmGiftcardSenderName(string $senderName): GiftCardOptionInterface
    {
        return $this->setData(GiftCardOptionInterface::SENDER_NAME, $senderName);
    }

    public function getAmGiftcardRecipientName()
    {
        return $this->_getData(GiftCardOptionInterface::RECIPIENT_NAME);
    }

    public function setAmGiftcardRecipientName($recipientName): GiftCardOptionInterface
    {
        return $this->setData(GiftCardOptionInterface::RECIPIENT_NAME, $recipientName);
    }

    public function getAmGiftcardRecipientEmail()
    {
        return $this->_getData(GiftCardOptionInterface::RECIPIENT_EMAIL);
    }

    public function setAmGiftcardRecipientEmail($recipientEmail): GiftCardOptionInterface
    {
        return $this->setData(GiftCardOptionInterface::RECIPIENT_EMAIL, $recipientEmail);
    }

    public function getAmGiftcardDateDelivery()
    {
        return $this->_getData(GiftCardOptionInterface::DELIVERY_DATE);
    }

    public function setAmGiftcardDateDelivery($deliveryDate): GiftCardOptionInterface
    {
        return $this->setData(GiftCardOptionInterface::DELIVERY_DATE, $deliveryDate);
    }

    public function getAmGiftcardDateDeliveryTimezone()
    {
        return $this->_getData(GiftCardOptionInterface::DELIVERY_TIMEZONE);
    }

    public function setAmGiftcardDateDeliveryTimezone($deliveryTimezone): GiftCardOptionInterface
    {
        return $this->setData(GiftCardOptionInterface::DELIVERY_TIMEZONE, $deliveryTimezone);
    }

    public function getAmGiftcardMessage()
    {
        return $this->_getData(GiftCardOptionInterface::MESSAGE);
    }

    public function setAmGiftcardMessage($message): GiftCardOptionInterface
    {
        return $this->setData(GiftCardOptionInterface::MESSAGE, $message);
    }

    public function getAmGiftcardImage()
    {
        return $this->_getData(GiftCardOptionInterface::IMAGE);
    }

    public function setAmGiftcardImage($image): GiftCardOptionInterface
    {
        return $this->setData(GiftCardOptionInterface::IMAGE, $image);
    }

    public function getAmGiftcardCustomImage()
    {
        return $this->_getData(GiftCardOptionInterface::CUSTOM_IMAGE);
    }

    public function setAmGiftcardCustomImage($image): GiftCardOptionInterface
    {
        return $this->setData(GiftCardOptionInterface::CUSTOM_IMAGE, $image);
    }

    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    public function setExtensionAttributes(
        \Amasty\GiftCard\Api\Data\GiftCardOptionExtensionInterface $extensionAttributes
    ): GiftCardOptionInterface {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
