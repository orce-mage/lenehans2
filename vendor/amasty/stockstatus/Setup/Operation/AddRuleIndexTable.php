<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Setup\Operation;

use Amasty\Stockstatus\Api\Data\RuleInterface;
use Amasty\Stockstatus\Model\ResourceModel\RuleIndex;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Store\Model\Store;
use Zend_Db_Exception;

class AddRuleIndexTable
{
    /**
     * @param SchemaSetupInterface $setup
     * @throws Zend_Db_Exception
     */
    public function execute(SchemaSetupInterface $setup): void
    {
        $this->createIndexTable($setup, RuleIndex::MAIN_TABLE);
        $this->createIndexTable($setup, RuleIndex::REPLICA_TABLE);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param string $tableName
     * @throws Zend_Db_Exception
     */
    private function createIndexTable(SchemaSetupInterface $setup, string $tableName): void
    {
        $table = $setup->getConnection()->newTable(
            $setup->getTable($tableName)
        )->addColumn(
            RuleIndex::RULE_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Id of rule entity.'
        )->addColumn(
            RuleIndex::STORE_ID,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Id of store entity.'
        )->addColumn(
            RuleIndex::PRODUCT_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Id of product entity.'
        )->addIndex(
            $setup->getIdxName(
                $tableName,
                [
                    RuleIndex::PRODUCT_ID,
                    RuleIndex::STORE_ID
                ],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            [
                RuleIndex::PRODUCT_ID,
                RuleIndex::STORE_ID
            ],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addForeignKey(
            $setup->getFkName(
                $tableName,
                RuleIndex::RULE_ID,
                RuleInterface::MAIN_TABLE,
                RuleInterface::ID
            ),
            RuleIndex::RULE_ID,
            $setup->getTable(RuleInterface::MAIN_TABLE),
            RuleInterface::ID,
            Table::ACTION_CASCADE
        )->addForeignKey(
            $setup->getFkName(
                $tableName,
                RuleIndex::STORE_ID,
                'store',
                Store::STORE_ID
            ),
            RuleIndex::STORE_ID,
            $setup->getTable('store'),
            Store::STORE_ID,
            Table::ACTION_CASCADE
        )->addForeignKey(
            $setup->getFkName(
                $tableName,
                RuleIndex::PRODUCT_ID,
                'catalog_product_entity',
                'entity_id'
            ),
            RuleIndex::PRODUCT_ID,
            $setup->getTable('catalog_product_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        );

        $setup->getConnection()->createTable($table);
    }
}
