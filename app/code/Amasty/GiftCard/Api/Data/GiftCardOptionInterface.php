<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 */
interface GiftCardOptionInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constant defined for data array
     */
    const GIFTCARD_AMOUNT = 'am_giftcard_amount';
    const CUSTOM_GIFTCARD_AMOUNT = 'am_giftcard_amount_custom';
    const GIFTCARD_TYPE = 'am_giftcard_type';
    const SENDER_NAME = 'am_giftcard_sender_name';
    const SENDER_EMAIL = 'am_giftcard_sender_email';
    const RECIPIENT_NAME = 'am_giftcard_recipient_name';
    const RECIPIENT_EMAIL = 'am_giftcard_recipient_email';
    const IS_DATE_DELIVERY = 'is_date_delivery';
    const DELIVERY_DATE = 'am_giftcard_date_delivery';
    const DELIVERY_TIMEZONE = 'am_giftcard_date_delivery_timezone';
    const MESSAGE = 'am_giftcard_message';
    const IMAGE = 'am_giftcard_image';
    const CUSTOM_IMAGE = 'am_giftcard_custom_image';
    /**#@-*/

    /**
     * Created accounts code order item option key
     */
    const GIFTCARD_CREATED_CODES = 'am_giftcard_created_codes';

    /**
     * @return float
     */
    public function getAmGiftcardAmount(): float;

    /**
     * @param float $cardAmount
     *
     * @return \Amasty\GiftCard\Api\Data\GiftCardOptionInterface
     */
    public function setAmGiftcardAmount($cardAmount): \Amasty\GiftCard\Api\Data\GiftCardOptionInterface;

    /**
     * @return float
     */
    public function getAmGiftcardAmountCustom(): float;

    /**
     * @param float|null $cardAmount
     *
     * @return \Amasty\GiftCard\Api\Data\GiftCardOptionInterface
     */
    public function setAmGiftcardAmountCustom($cardAmount): \Amasty\GiftCard\Api\Data\GiftCardOptionInterface;

    /**
     * @return int
     */
    public function getAmGiftcardType(): int;

    /**
     * @param int $type
     *
     * @return \Amasty\GiftCard\Api\Data\GiftCardOptionInterface
     */
    public function setAmGiftcardType($type): \Amasty\GiftCard\Api\Data\GiftCardOptionInterface;

    /**
     * @return string|null
     */
    public function getAmGiftcardSenderName();

    /**
     * @param string $senderName
     *
     * @return \Amasty\GiftCard\Api\Data\GiftCardOptionInterface
     */
    public function setAmGiftcardSenderName(string $senderName): \Amasty\GiftCard\Api\Data\GiftCardOptionInterface;

    /**
     * @return string|null
     */
    public function getAmGiftcardRecipientName();

    /**
     * @param string|null $recipientName
     *
     * @return \Amasty\GiftCard\Api\Data\GiftCardOptionInterface
     */
    public function setAmGiftcardRecipientName($recipientName): \Amasty\GiftCard\Api\Data\GiftCardOptionInterface;

    /**
     * @return string|null
     */
    public function getAmGiftcardRecipientEmail();

    /**
     * @param string|null $recipientEmail
     *
     * @return \Amasty\GiftCard\Api\Data\GiftCardOptionInterface
     */
    public function setAmGiftcardRecipientEmail($recipientEmail): \Amasty\GiftCard\Api\Data\GiftCardOptionInterface;

    /**
     * @return string|null
     */
    public function getAmGiftcardDateDelivery();

    /**
     * @param string|null $deliveryDate
     *
     * @return \Amasty\GiftCard\Api\Data\GiftCardOptionInterface
     */
    public function setAmGiftcardDateDelivery($deliveryDate): \Amasty\GiftCard\Api\Data\GiftCardOptionInterface;

    /**
     * @return string|null
     */
    public function getAmGiftcardDateDeliveryTimezone();

    /**
     * @param string|null $deliveryTimezone
     *
     * @return \Amasty\GiftCard\Api\Data\GiftCardOptionInterface
     */
    public function setAmGiftcardDateDeliveryTimezone(
        $deliveryTimezone
    ): \Amasty\GiftCard\Api\Data\GiftCardOptionInterface;

    /**
     * @return string|null
     */
    public function getAmGiftcardMessage();

    /**
     * @param string|null $message
     *
     * @return \Amasty\GiftCard\Api\Data\GiftCardOptionInterface
     */
    public function setAmGiftcardMessage($message): \Amasty\GiftCard\Api\Data\GiftCardOptionInterface;

    /**
     * @return string|null
     */
    public function getAmGiftcardImage();

    /**
     * @param string|null $image
     *
     * @return \Amasty\GiftCard\Api\Data\GiftCardOptionInterface
     */
    public function setAmGiftcardImage($image): \Amasty\GiftCard\Api\Data\GiftCardOptionInterface;

    /**
     * @return string|null
     */
    public function getAmGiftcardCustomImage();

    /**
     * @param string|null $image
     *
     * @return \Amasty\GiftCard\Api\Data\GiftCardOptionInterface
     */
    public function setAmGiftcardCustomImage($image): GiftCardOptionInterface;

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Amasty\GiftCard\Api\Data\GiftCardOptionExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Amasty\GiftCard\Api\Data\GiftCardOptionExtensionInterface $extensionAttributes
     *
     * @return \Amasty\GiftCard\Api\Data\GiftCardOptionInterface
     */
    public function setExtensionAttributes(
        \Amasty\GiftCard\Api\Data\GiftCardOptionExtensionInterface $extensionAttributes
    ): \Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
}
