<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Setup\Operation;

use Amasty\Stockstatus\Api\Data\RuleInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

class AddRuleTable
{
    /**
     * @param SchemaSetupInterface $setup
     * @throws Zend_Db_Exception
     */
    public function execute(SchemaSetupInterface $setup): void
    {
        $table = $setup->getConnection()->newTable(
            $setup->getTable(RuleInterface::MAIN_TABLE)
        )->addColumn(
            RuleInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id of rule entity.'
        )->addColumn(
            RuleInterface::STATUS,
            Table::TYPE_BOOLEAN,
            null,
            ['default' => 0, 'nullable' => false],
            'Status of rule entity.'
        )->addColumn(
            RuleInterface::NAME,
            Table::TYPE_TEXT,
            100,
            ['default' => false, 'nullable' => false],
            'Name of rule entity.'
        )->addColumn(
            RuleInterface::PRIORITY,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => 0, 'nullable' => false],
            'Priority of rule entity.'
        )->addColumn(
            RuleInterface::STORES,
            Table::TYPE_TEXT,
            100,
            ['default' => false, 'nullable' => false],
            'Stores of rule entity.'
        )->addColumn(
            RuleInterface::CUSTOMER_GROUPS,
            Table::TYPE_TEXT,
            100,
            ['default' => false, 'nullable' => false],
            'Customer groups of rule entity.'
        )->addColumn(
            RuleInterface::STOCK_STATUS,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'default' => null, 'nullable' => true],
            'Main Stock Status of rule entity.'
        )->addColumn(
            RuleInterface::CONDITIONS_SERIALIZED,
            Table::TYPE_TEXT,
            '2M',
            [],
            'Conditions for stock status displaying.'
        )->addColumn(
            RuleInterface::ACTIVATE_QTY_RANGES,
            Table::TYPE_BOOLEAN,
            null,
            ['default' => 0, 'nullable' => false],
            'Flag for qty ranges of rule entity.'
        );

        $setup->getConnection()->createTable($table);
    }
}
