<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Number
 */


namespace Amasty\Number\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchemaTo200
{
    const COUNTER_DATA_TABLE_NAME = 'amasty_number_counter_data';

    /**
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $installer = $setup;
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::COUNTER_DATA_TABLE_NAME))
            ->addColumn(
                'counter_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Counter ID'
            )->addColumn(
                'scope_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Counter Scope ID'
            )->addColumn(
                'scope_type_id',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Counter Scope Type'
            )->addColumn(
                'entity_type_id',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Counter Entity Type'
            )->addColumn(
                'current_value',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Counter Current Value'
            )->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Counter Updated At'
            )->setComment('Amasty Number Counter Data');
        $installer->getConnection()->createTable($table);
    }
}
