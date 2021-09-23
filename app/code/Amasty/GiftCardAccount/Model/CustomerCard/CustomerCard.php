<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\CustomerCard;

use Amasty\GiftCardAccount\Api\Data\CustomerCardInterface;
use Magento\Framework\Model\AbstractModel;

class CustomerCard extends AbstractModel implements CustomerCardInterface
{
    protected function _construct()
    {
        $this->_init(\Amasty\GiftCardAccount\Model\CustomerCard\ResourceModel\CustomerCard::class);
        $this->setIdFieldName(CustomerCardInterface::CUSTOMER_CARD_ID);
    }

    public function getCustomerCardId(): int
    {
        return (int)$this->_getData(CustomerCardInterface::CUSTOMER_CARD_ID);
    }

    public function setCustomerCardId(int $customerCardId): CustomerCardInterface
    {
        return $this->setData(CustomerCardInterface::CUSTOMER_CARD_ID, (int)$customerCardId);
    }

    public function getAccountId(): int
    {
        return (int)$this->_getData(CustomerCardInterface::ACCOUNT_ID);
    }

    public function setAccountId(int $accountId): CustomerCardInterface
    {
        return $this->setData(CustomerCardInterface::ACCOUNT_ID, (int)$accountId);
    }

    public function getCustomerId(): int
    {
        return (int)$this->_getData(CustomerCardInterface::CUSTOMER_ID);
    }

    public function setCustomerId(int $customerId): CustomerCardInterface
    {
        return $this->setData(CustomerCardInterface::CUSTOMER_ID, (int)$customerId);
    }
}
