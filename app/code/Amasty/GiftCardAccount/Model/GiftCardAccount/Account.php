<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardAccount;

use Amasty\GiftCard\Api\Data\CodeInterface;
use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Magento\Framework\Model\AbstractModel;

class Account extends AbstractModel implements GiftCardAccountInterface
{
    const DATA_PERSISTOR_KEY = 'amgcard_account';

    public function _construct()
    {
        $this->_init(ResourceModel\Account::class);
        $this->setIdFieldName(GiftCardAccountInterface::ACCOUNT_ID);
    }

    public function getAccountId(): int
    {
        return (int)$this->_getData(GiftCardAccountInterface::ACCOUNT_ID);
    }

    public function setAccountId(int $id): GiftCardAccountInterface
    {
        return $this->setData(GiftCardAccountInterface::ACCOUNT_ID, $id);
    }

    public function getCodeId(): int
    {
        return (int)$this->_getData(GiftCardAccountInterface::CODE_ID);
    }

    public function setCodeId(int $id): GiftCardAccountInterface
    {
        return $this->setData(GiftCardAccountInterface::CODE_ID, $id);
    }

    public function getImageId(): int
    {
        return (int)$this->_getData(GiftCardAccountInterface::IMAGE_ID);
    }

    public function setImageId(int $id): GiftCardAccountInterface
    {
        return $this->setData(GiftCardAccountInterface::IMAGE_ID, $id);
    }

    public function getOrderItemId(): int
    {
        return (int)$this->_getData(GiftCardAccountInterface::ORDER_ITEM_ID);
    }

    public function setOrderItemId($id): GiftCardAccountInterface
    {
        return $this->setData(GiftCardAccountInterface::ORDER_ITEM_ID, $id);
    }

    public function getWebsiteId(): int
    {
        return (int)$this->_getData(GiftCardAccountInterface::WEBSITE_ID);
    }

    public function setWebsiteId(int $id): GiftCardAccountInterface
    {
        return $this->setData(GiftCardAccountInterface::WEBSITE_ID, $id);
    }

    public function getStatus(): int
    {
        return (int)$this->_getData(GiftCardAccountInterface::STATUS);
    }

    public function setStatus(int $status): GiftCardAccountInterface
    {
        return $this->setData(GiftCardAccountInterface::STATUS, $status);
    }

    public function getInitialValue(): float
    {
        return (float)$this->_getData(GiftCardAccountInterface::INITIAL_VALUE);
    }

    public function setInitialValue(float $value): GiftCardAccountInterface
    {
        return $this->setData(GiftCardAccountInterface::INITIAL_VALUE, $value);
    }

    public function getCurrentValue(): float
    {
        return (float)$this->_getData(GiftCardAccountInterface::CURRENT_VALUE);
    }

    public function setCurrentValue(float $value): GiftCardAccountInterface
    {
        return $this->setData(GiftCardAccountInterface::CURRENT_VALUE, $value);
    }

    public function getExpiredDate()
    {
        return $this->_getData(GiftCardAccountInterface::EXPIRED_DATE);
    }

    public function setExpiredDate($date): GiftCardAccountInterface
    {
        return $this->setData(GiftCardAccountInterface::EXPIRED_DATE, $date);
    }

    public function getComment()
    {
        return $this->_getData(GiftCardAccountInterface::COMMENT);
    }

    public function setComment(string $comment): GiftCardAccountInterface
    {
        return $this->setData(GiftCardAccountInterface::COMMENT, $comment);
    }

    public function setDeliveryDate(string $date): GiftCardAccountInterface
    {
        return $this->setData(GiftCardAccountInterface::DATE_DELIVERY, $date);
    }

    public function getDeliveryDate()
    {
        return $this->_getData(GiftCardAccountInterface::DATE_DELIVERY);
    }

    public function setIsSent(bool $isSent): GiftCardAccountInterface
    {
        return $this->setData(GiftCardAccountInterface::IS_SENT, $isSent);
    }

    public function isSent(): bool
    {
        return (bool)$this->_getData(GiftCardAccountInterface::IS_SENT);
    }

    public function setCustomerCreatedId($id): GiftCardAccountInterface
    {
        return $this->setData(GiftCardAccountInterface::CUSTOMER_CREATED_ID, $id);
    }

    public function getCustomerCreatedId()
    {
        return (int)$this->_getData(GiftCardAccountInterface::CUSTOMER_CREATED_ID);
    }

    public function setCodeModel(CodeInterface $code): GiftCardAccountInterface
    {
        return $this->setData(GiftCardAccountInterface::CODE_MODEL, $code);
    }

    public function getCodeModel()
    {
        return $this->_getData(GiftCardAccountInterface::CODE_MODEL);
    }

    public function getCodePool()
    {
        return (int)$this->_getData(GiftCardAccountInterface::CODE_POOL);
    }

    public function setCodePool(int $codePoolId): GiftCardAccountInterface
    {
        return $this->setData(GiftCardAccountInterface::CODE_POOL, $codePoolId);
    }

    public function getRecipientEmail(): string
    {
        return (string)$this->_getData(GiftCardAccountInterface::RECIPIENT_EMAIL);
    }

    public function setRecipientEmail(string $email): \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface
    {
        return $this->setData(GiftCardAccountInterface::RECIPIENT_EMAIL, $email);
    }
}
