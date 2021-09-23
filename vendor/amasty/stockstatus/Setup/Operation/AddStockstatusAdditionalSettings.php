<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Setup\Operation;

use Amasty\Stockstatus\Api\Data\StockstatusSettingsInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class AddStockstatusAdditionalSettings
{
    public function execute(SchemaSetupInterface $setup): void
    {
        $connection = $setup->getConnection();
        $table = $this->createTable(
            $connection,
            $setup->getTable(StockstatusSettingsInterface::MAIN_TABLE)
        );
        $this->addForeignKeys($table, $setup);
        $this->addIndexes($table, $setup);
        $connection->createTable($table);
    }

    private function createTable(AdapterInterface $connection, string $tableName): Table
    {
        $table = $connection->newTable($tableName);
        $table->addColumn(
            StockstatusSettingsInterface::ID,
            Table::TYPE_SMALLINT,
            null,
            [
                Table::OPTION_IDENTITY => true,
                Table::OPTION_UNSIGNED => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_PRIMARY => true
            ],
            'Id of settings entity'
        );
        $table->addColumn(
            StockstatusSettingsInterface::OPTION_ID,
            Table::TYPE_INTEGER,
            null,
            [Table::OPTION_UNSIGNED => true, Table::OPTION_NULLABLE => false],
            'Id of option custom stock status attribute.'
        );
        $table->addColumn(
            StockstatusSettingsInterface::STORE_ID,
            Table::TYPE_SMALLINT,
            null,
            [Table::OPTION_UNSIGNED => true, Table::OPTION_NULLABLE => false],
            'Store id'
        );
        $table->addColumn(
            StockstatusSettingsInterface::IMAGE_PATH,
            Table::TYPE_TEXT,
            255,
            [Table::OPTION_NULLABLE => true],
            'Relative image path on filesystem'
        );
        $table->addColumn(
            StockstatusSettingsInterface::TOOLTIP_TEXT,
            Table::TYPE_TEXT,
            null,
            [Table::OPTION_NULLABLE => true],
            'Tooltip text'
        );

        return $table;
    }

    private function addForeignKeys(Table $table, SchemaSetupInterface $setup): void
    {
        $table->addForeignKey(
            $setup->getFkName(
                $table->getName(),
                StockstatusSettingsInterface::STORE_ID,
                $setup->getTable('store'),
                'store_id'
            ),
            StockstatusSettingsInterface::STORE_ID,
            $setup->getTable('store'),
            'store_id',
            Table::ACTION_CASCADE
        );
        $table->addForeignKey(
            $setup->getFkName(
                $table->getName(),
                StockstatusSettingsInterface::OPTION_ID,
                $setup->getTable('eav_attribute_option'),
                'option_id'
            ),
            StockstatusSettingsInterface::OPTION_ID,
            $setup->getTable('eav_attribute_option'),
            'option_id',
            Table::ACTION_CASCADE
        );
    }

    private function addIndexes(Table $table, SchemaSetupInterface $setup): void
    {
        $table->addIndex(
            $setup->getIdxName(
                $table->getName(),
                [
                    StockstatusSettingsInterface::STORE_ID,
                    StockstatusSettingsInterface::OPTION_ID
                ],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            [
                StockstatusSettingsInterface::STORE_ID,
                StockstatusSettingsInterface::OPTION_ID
            ],
            [Table::OPTION_TYPE => AdapterInterface::INDEX_TYPE_UNIQUE]
        );
    }
}
