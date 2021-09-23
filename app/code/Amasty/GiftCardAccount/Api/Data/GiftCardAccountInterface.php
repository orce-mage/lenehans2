<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


namespace Amasty\GiftCardAccount\Api\Data;

interface GiftCardAccountInterface
{
    const ACCOUNT_ID = 'account_id';
    const CODE_ID = 'code_id';
    const IMAGE_ID = 'image_id';
    const WEBSITE_ID = 'website_id';
    const ORDER_ITEM_ID = 'order_item_id';
    const STATUS = 'status';
    const INITIAL_VALUE = 'initial_value';
    const CURRENT_VALUE = 'current_value';
    const EXPIRED_DATE = 'expired_date';
    const COMMENT = 'comment';
    const DATE_DELIVERY = 'date_delivery';
    const IS_SENT = 'is_sent';
    const CUSTOMER_CREATED_ID = 'customer_created_id';
    const CODE_MODEL = 'code_model';
    const CODE_POOL = 'code_pool';
    const RECIPIENT_EMAIL = 'recipient_email';

    /**
     * @return int
     */
    public function getAccountId(): int;

    /**
     * @param int $id
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface
     */
    public function setAccountId(int $id): \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;

    /**
     * @return int
     */
    public function getCodeId(): int;

    /**
     * @param int $id
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface
     */
    public function setCodeId(int $id): \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;

    /**
     * @return int
     */
    public function getImageId(): int;

    /**
     * @param int $id
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface
     */
    public function setImageId(int $id): \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;

    /**
     * @return int
     */
    public function getOrderItemId(): int;

    /**
     * @param int|null $id
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface
     */
    public function setOrderItemId($id): \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;

    /**
     * @return int
     */
    public function getWebsiteId(): int;

    /**
     * @param int $id
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface
     */
    public function setWebsiteId(int $id): \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;

    /**
     * @return int
     */
    public function getStatus(): int;

    /**
     * @param int $status
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface
     */
    public function setStatus(int $status): \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;

    /**
     * @return float
     */
    public function getInitialValue(): float;

    /**
     * @param float $value
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface
     */
    public function setInitialValue(float $value): \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;

    /**
     * @return float
     */
    public function getCurrentValue(): float;

    /**
     * @param float $value
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface
     */
    public function setCurrentValue(float $value): \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;

    /**
     * @return string|null
     */
    public function getExpiredDate();

    /**
     * @param string|null $date
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface
     */
    public function setExpiredDate($date): \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;

    /**
     * @return string|null
     */
    public function getComment();

    /**
     * @param string $comment
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface
     */
    public function setComment(string $comment): \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;

    /**
     * @param string $date
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface
     */
    public function setDeliveryDate(string $date): \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;

    /**
     * @return string|null
     */
    public function getDeliveryDate();

    /**
     * @param bool $isSent
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface
     */
    public function setIsSent(bool $isSent): \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;

    /**
     * @return bool
     */
    public function isSent(): bool;

    /**
     * @param int|null $id
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface
     */
    public function setCustomerCreatedId($id): \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;

    /**
     * @return int|null
     */
    public function getCustomerCreatedId();

    /**
     * @param \Amasty\GiftCard\Api\Data\CodeInterface $code
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface
     */
    public function setCodeModel(
        \Amasty\GiftCard\Api\Data\CodeInterface $code
    ): \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;

    /**
     * @return \Amasty\GiftCard\Api\Data\CodeInterface|null
     */
    public function getCodeModel();

    /**
     * @return int|null
     */
    public function getCodePool();

    /**
     * @param int $codePoolId
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface
     */
    public function setCodePool(int $codePoolId): \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;

    /**
     * @return string
     */
    public function getRecipientEmail(): string;

    /**
     * @param string $email
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface
     */
    public function setRecipientEmail(string $email): \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
}
