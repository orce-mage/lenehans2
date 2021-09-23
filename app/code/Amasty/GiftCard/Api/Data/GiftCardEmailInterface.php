<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Api\Data;

interface GiftCardEmailInterface
{
    const GIFT_CODE = 'gift_code';
    const RECIPIENT_NAME = 'recipient_name';
    const RECIPIENT_EMAIL = 'recipient_email';
    const SENDER_NAME = 'sender_name';
    const SENDER_EMAIL = 'sender_email';
    const SENDER_MESSAGE = 'sender_message';
    const BALANCE = 'balance';
    const EXPIRED_DATE = 'expired_date';
    const IMAGE = 'image';
    const EXPIRY_DAYS = 'expiry_days';

    /**
     * @return string
     */
    public function getGiftCode(): string;

    /**
     * @param string $code
     *
     * @return \Amasty\GiftCard\Api\Data\GiftCardEmailInterface
     */
    public function setGiftCode(string $code): \Amasty\GiftCard\Api\Data\GiftCardEmailInterface;

    /**
     * @return string|null
     */
    public function getRecipientName();

    /**
     * @param string $name
     *
     * @return \Amasty\GiftCard\Api\Data\GiftCardEmailInterface
     */
    public function setRecipientName(string $name): \Amasty\GiftCard\Api\Data\GiftCardEmailInterface;

    /**
     * @return string
     */
    public function getRecipientEmail(): string;

    /**
     * @param string $email
     *
     * @return \Amasty\GiftCard\Api\Data\GiftCardEmailInterface
     */
    public function setRecipientEmail(string $email): \Amasty\GiftCard\Api\Data\GiftCardEmailInterface;

    /**
     * @return string|null
     */
    public function getSenderName();

    /**
     * @param string $name
     *
     * @return \Amasty\GiftCard\Api\Data\GiftCardEmailInterface
     */
    public function setSenderName(string $name): \Amasty\GiftCard\Api\Data\GiftCardEmailInterface;

    /**
     * @return string|null
     */
    public function getSenderEmail();

    /**
     * @param string $email
     *
     * @return \Amasty\GiftCard\Api\Data\GiftCardEmailInterface
     */
    public function setSenderEmail(string $email): \Amasty\GiftCard\Api\Data\GiftCardEmailInterface;

    /**
     * @return string|null
     */
    public function getSenderMessage();

    /**
     * @param string $message
     *
     * @return \Amasty\GiftCard\Api\Data\GiftCardEmailInterface
     */
    public function setSenderMessage(string $message): \Amasty\GiftCard\Api\Data\GiftCardEmailInterface;

    /**
     * @return string
     */
    public function getBalance(): string;

    /**
     * @param string $balance
     *
     * @return \Amasty\GiftCard\Api\Data\GiftCardEmailInterface
     */
    public function setBalance(string $balance): \Amasty\GiftCard\Api\Data\GiftCardEmailInterface;

    /**
     * @return string|null
     */
    public function getExpiredDate();

    /**
     * @param string|null $date
     *
     * @return \Amasty\GiftCard\Api\Data\GiftCardEmailInterface
     */
    public function setExpiredDate($date): \Amasty\GiftCard\Api\Data\GiftCardEmailInterface;

    /**
     * @return string|null
     */
    public function getImage();

    /**
     * @param string $image
     *
     * @return \Amasty\GiftCard\Api\Data\GiftCardEmailInterface
     */
    public function setImage(string $image): \Amasty\GiftCard\Api\Data\GiftCardEmailInterface;

    /**
     * @return int
     */
    public function getExpiryDays(): int;

    /**
     * @param int $days
     *
     * @return \Amasty\GiftCard\Api\Data\GiftCardEmailInterface
     */
    public function setExpiryDays(int $days): \Amasty\GiftCard\Api\Data\GiftCardEmailInterface;
}
