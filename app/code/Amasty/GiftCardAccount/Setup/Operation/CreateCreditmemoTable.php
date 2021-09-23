<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Setup\Operation;

use Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoInterface;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Creditmemo\ResourceModel\Creditmemo;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class CreateCreditmemoTable
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
        $mainTable = $setup->getTable(Creditmemo::TABLE_NAME);
        $memoTable = $setup->getTable('sales_creditmemo');

        return $setup->getConnection()
            ->newTable(
                $mainTable
            )->setComment(
                'Amasty Gift Card Account Credit Memo Table'
            )->addColumn(
                GiftCardCreditmemoInterface::ENTITY_ID,
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
                GiftCardCreditmemoInterface::CREDITMEMO_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Credit Memo ID'
            )->addColumn(
                GiftCardCreditmemoInterface::GIFT_AMOUNT,
                Table::TYPE_DECIMAL,
                '12,4',
                [
                    'nullable' => false,
                    'default' => '0.0000'
                ],
                'Gift Cards Amount'
            )->addColumn(
                GiftCardCreditmemoInterface::BASE_GIFT_AMOUNT,
                Table::TYPE_DECIMAL,
                '12,4',
                [
                    'nullable' => false,
                    'default' => '0.0000'
                ],
                'Base Gift Cards Amount'
            )->addForeignKey(
                $setup->getFkName(
                    $mainTable,
                    GiftCardCreditmemoInterface::CREDITMEMO_ID,
                    $memoTable,
                    'entity_id'
                ),
                GiftCardCreditmemoInterface::CREDITMEMO_ID,
                $memoTable,
                'entity_id',
                Table::ACTION_CASCADE
            );
    }
}
