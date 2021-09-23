<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Amasty\Orderexport\Setup\Operation;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallAttributesSchema
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function execute(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $table = $installer->getConnection()->newTable(
            $installer->getTable('amasty_amorderexport_attribute')
        )->addColumn(
            'entity_id',
            Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity ID'
        )->addColumn(
            'attribute_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => true, 'default' => '0'],
            'Attribute Id'
        )->addColumn(
            'attribute_code',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true, 'default' => null],
            'Attribute Code'
        )->addColumn(
            'frontend_label',
            Table::TYPE_TEXT,
            255,
            [],
            'Frontend Label'
        )->addIndex(
            $installer->getIdxName(
                'amasty_amorderexport_attribute',
                ['attribute_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['attribute_id'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addForeignKey(
            $installer->getFkName('amasty_amorderexport_attribute', 'attribute_id', 'eav_attribute', 'attribute_id'),
            'attribute_id',
            $installer->getTable('eav_attribute'),
            'attribute_id',
            Table::ACTION_SET_NULL
        )->setComment(
            'Amasty Order Export Attribute'
        );

        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('amasty_amorderexport_attribute_index')
        )->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            10,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity ID'
        )->addColumn(
            'order_item_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Order Item Id'
        )->addIndex(
            $installer->getIdxName(
                'amasty_amorderexport_attribute_index',
                ['order_item_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['order_item_id'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addForeignKey(
            $installer->getFkName('amasty_amorderexport_attribute_index', 'order_item_id', 'sales_order_item', 'item_id'),
            'order_item_id',
            $installer->getTable('sales_order_item'),
            'item_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Amasty Order Export Attribute Index'
        );

        $installer->getConnection()->createTable($table);
    }
}
