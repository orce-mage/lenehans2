<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\GiftCard;

use Amasty\GiftCard\Api\Data\GiftCardEmailInterface;
use Magento\Framework\DataObject;

class GiftCardEmail extends DataObject implements GiftCardEmailInterface
{
    public function getGiftCode(): string
    {
        return $this->_getData(GiftCardEmailInterface::GIFT_CODE);
    }

    public function setGiftCode(string $code): GiftCardEmailInterface
    {
        return $this->setData(GiftCardEmailInterface::GIFT_CODE, $code);
    }

    public function getRecipientName()
    {
        return $this->_getData(GiftCardEmailInterface::RECIPIENT_NAME);
    }

    public function setRecipientName(string $name): GiftCardEmailInterface
    {
        return $this->setData(GiftCardEmailInterface::RECIPIENT_NAME, $name);
    }

    public function getRecipientEmail(): string
    {
        return $this->_getData(GiftCardEmailInterface::RECIPIENT_EMAIL);
    }

    public function setRecipientEmail(string $email): GiftCardEmailInterface
    {
        return $this->setData(GiftCardEmailInterface::RECIPIENT_EMAIL, $email);
    }

    public function getSenderName()
    {
        return $this->_getData(GiftCardEmailInterface::SENDER_NAME);
    }

    public function setSenderName(string $name): GiftCardEmailInterface
    {
        return $this->setData(GiftCardEmailInterface::SENDER_NAME, $name);
    }

    public function getSenderEmail()
    {
        return $this->_getData(GiftCardEmailInterface::SENDER_EMAIL);
    }

    public function setSenderEmail(string $email): GiftCardEmailInterface
    {
        return $this->setData(GiftCardEmailInterface::SENDER_EMAIL, $email);
    }

    public function getSenderMessage()
    {
        return $this->_getData(GiftCardEmailInterface::SENDER_MESSAGE);
    }

    public function setSenderMessage(string $message): GiftCardEmailInterface
    {
        return $this->setData(GiftCardEmailInterface::SENDER_MESSAGE, $message);
    }

    public function getBalance(): string
    {
        return $this->_getData(GiftCardEmailInterface::BALANCE);
    }

    public function setBalance(string $balance): GiftCardEmailInterface
    {
        return $this->setData(GiftCardEmailInterface::BALANCE, $balance);
    }

    public function getExpiredDate()
    {
        return $this->_getData(GiftCardEmailInterface::EXPIRED_DATE);
    }

    public function setExpiredDate($date): GiftCardEmailInterface
    {
        return $this->setData(GiftCardEmailInterface::EXPIRED_DATE, $date);
    }

    public function getImage()
    {
        return $this->_getData(GiftCardEmailInterface::IMAGE);
    }

    public function setImage(string $image): GiftCardEmailInterface
    {
        return $this->setData(GiftCardEmailInterface::IMAGE, $image);
    }

    public function getExpiryDays(): int
    {
        return (int)$this->_getData(GiftCardEmailInterface::EXPIRY_DAYS);
    }

    public function setExpiryDays(int $days): GiftCardEmailInterface
    {
        return $this->setData(GiftCardEmailInterface::EXPIRY_DAYS, $days);
    }
}
