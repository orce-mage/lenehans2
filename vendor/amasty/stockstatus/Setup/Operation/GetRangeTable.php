<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Setup\Operation;

use Amasty\Stockstatus\Api\Data\RangeInterface;
use Amasty\Stockstatus\Api\Data\RuleInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class GetRangeTable
{
    public function execute(SchemaSetupInterface $setup, string $tableName): Table
    {
        return $setup->getConnection()->newTable(
            $setup->getTable($tableName)
        )->addColumn(
            RangeInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id of range entity.'
        )->addColumn(
            RangeInterface::RULE_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'default' => 0, 'nullable' => false],
            'Id of rule entity.'
        )->addColumn(
            RangeInterface::STATUS_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'default' => 0, 'nullable' => false],
            'Id of stock status used.'
        )->addColumn(
            RangeInterface::FROM,
            Table::TYPE_INTEGER,
            null,
            ['default' => 0, 'nullable' => false],
            'Qty - begin of range.'
        )->addColumn(
            RangeInterface::TO,
            Table::TYPE_INTEGER,
            null,
            ['default' => 0, 'nullable' => false],
            'Qty - end of range.'
        )->addIndex(
            $setup->getIdxName(
                $tableName,
                [RangeInterface::RULE_ID],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            [RangeInterface::RULE_ID],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        )->addForeignKey(
            $setup->getFkName(
                $tableName,
                RangeInterface::RULE_ID,
                RuleInterface::MAIN_TABLE,
                RuleInterface::ID
            ),
            RangeInterface::RULE_ID,
            $setup->getTable(RuleInterface::MAIN_TABLE),
            RuleInterface::ID,
            Table::ACTION_CASCADE
        );
    }
}
