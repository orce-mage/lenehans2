<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel;

use Amasty\GiftCard\Api\Data\CodeInterface;
use Amasty\GiftCard\Model\Code\ResourceModel\Code;
use Amasty\GiftCardAccount\Api\Data\CustomerCardInterface;
use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Model\CustomerCard\ResourceModel\CustomerCard;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Amasty\GiftCardAccount\Model\GiftCardAccount\Account::class,
            \Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\Account::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * @return Collection
     */
    public function addCodeTable(): Collection
    {
        $this->getSelect()->joinLeft(
            ['g_code' => $this->getTable(Code::TABLE_NAME)],
            'main_table.' . GiftCardAccountInterface::CODE_ID . ' = g_code.' . CodeInterface::CODE_ID,
            ['code' => 'g_code.' . CodeInterface::CODE]
        );

        return $this;
    }

    /**
     * @return Collection
     */
    public function addOrderTable(): Collection
    {
        $this->getSelect()->joinLeft(
            ['order_item' => $this->getTable('sales_order_item')],
            'main_table.' . GiftCardAccountInterface::ORDER_ITEM_ID . ' = order_item.item_id',
            []
        )->joinLeft(
            ['order' => $this->getTable('sales_order')],
            'order_item.order_id = order.entity_id',
            ['order_number' => 'order.increment_id']
        )->joinLeft(
            /*
             * During placing order on admin side, remote_ip column set to NULL
             */
            ['customer_order' => $this->getTable('sales_order')],
            'order_item.order_id = customer_order.entity_id AND customer_order.remote_ip IS NOT NULL',
            ['sender_email' =>'customer_order.customer_email']
        );

        return $this;
    }

    /**
     * @param int $customerId
     *
     * @return Collection
     */
    public function filterCustomerCards(int $customerId): Collection
    {
        $this->getSelect()->joinInner(
            ['c_card' => $this->getTable(CustomerCard::TABLE_NAME)],
            'main_table.' . GiftCardAccountInterface::ACCOUNT_ID . ' = c_card.' . CustomerCardInterface::ACCOUNT_ID,
            []
        )->where(CustomerCardInterface::CUSTOMER_ID . ' = ?', $customerId);

        return $this;
    }
}
