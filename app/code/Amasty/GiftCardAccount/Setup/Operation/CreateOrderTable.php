<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Setup\Operation;

use Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Order\ResourceModel\Order;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class CreateOrderTable
{
    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->createTable(
            $this->createTable($setup)
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @return Table
     * @throws \Zend_Db_Exception
     */
    private function createTable(SchemaSetupInterface $setup)
    {
        $mainTable = $setup->getTable(Order::TABLE_NAME);
        $orderTable = $setup->getTable('sales_order');

        return $setup->getConnection()
            ->newTable(
                $mainTable
            )->setComment(
                'Amasty Gift Card Account Order Table'
            )->addColumn(
                GiftCardOrderInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Entity ID'
            )->addColumn(
                GiftCardOrderInterface::ORDER_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Order ID'
            )->addColumn(
                GiftCardOrderInterface::GIFT_CARDS,
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true
                ],
                'Gift Cards Applied To Order'
            )->addColumn(
                GiftCardOrderInterface::GIFT_AMOUNT,
                Table::TYPE_DECIMAL,
                '12,4',
                [
                    'nullable' => false,
                    'default' => '0.0000'
                ],
                'Gift Cards Amount'
            )->addColumn(
                GiftCardOrderInterface::BASE_GIFT_AMOUNT,
                Table::TYPE_DECIMAL,
                '12,4',
                [
                    'nullable' => false,
                    'default' => '0.0000'
                ],
                'Base Gift Cards Amount'
            )->addColumn(
                GiftCardOrderInterface::INVOICE_GIFT_AMOUNT,
                Table::TYPE_DECIMAL,
                '12,4',
                [
                    'nullable' => false,
                    'default' => '0.0000'
                ],
                'Invoice Gift Cards Amount'
            )->addColumn(
                GiftCardOrderInterface::BASE_INVOICE_GIFT_AMOUNT,
                Table::TYPE_DECIMAL,
                '12,4',
                [
                    'nullable' => false,
                    'default' => '0.0000'
                ],
                'Base Invoiced Gift Cards Amount'
            )->addColumn(
                GiftCardOrderInterface::REFUND_GIFT_AMOUNT,
                Table::TYPE_DECIMAL,
                '12,4',
                [
                    'nullable' => false,
                    'default' => '0.0000'
                ],
                'Refunded Gift Cards Amount'
            )->addColumn(
                GiftCardOrderInterface::BASE_REFUND_GIFT_AMOUNT,
                Table::TYPE_DECIMAL,
                '12,4',
                [
                    'nullable' => false,
                    'default' => '0.0000'
                ],
                'Base Refunded Gift Cards Amount'
            )->addForeignKey(
                $setup->getFkName(
                    $mainTable,
                    GiftCardOrderInterface::ORDER_ID,
                    $orderTable,
                    'entity_id'
                ),
                GiftCardOrderInterface::ORDER_ID,
                $orderTable,
                'entity_id',
                Table::ACTION_CASCADE
            );
    }
}
