<?php
namespace Swissup\SoldTogether\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
          * Create table 'swissup_soldtogether_order'
          */
         $table = $installer->getConnection()->newTable(
             $installer->getTable('swissup_soldtogether_order')
         )->addColumn(
             'relation_id',
             \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
             null,
             ['identity' => true, 'nullable' => false, 'primary' => true],
             'Relation ID'
         )->addColumn(
             'product_id',
             \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
             null,
             ['unsigned' => true, 'nullable' => false],
             'Product ID'
         )->addColumn(
             'related_id',
             \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
             null,
             ['unsigned' => true, 'nullable' => false],
             'Related Product ID'
         )->addColumn(
             'store_id',
             \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
             null,
             ['unsigned' => true, 'nullable' => false],
             'Order Store ID'
         )->addColumn(
             'product_name',
             \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
             null,
             ['nullable'  => true, 'default'  => null],
             'Product Name'
         )->addColumn(
             'related_name',
             \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
             null,
             ['nullable' => true, 'default'  => null],
             'Related Product Name'
         )->addColumn(
             'weight',
             \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
             null,
             ['unsigned' => true, 'nullable' => false],
             'Weight'
         )->addColumn(
             'is_admin',
             \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
             null,
             [ 'nullable'  => false, 'default'  => 0],
             'Is Admin'
         )->addIndex(
             $installer->getIdxName('swissup_soldtogether_order_product', ['product_id']),
             ['product_id']
         )->addIndex(
             $installer->getIdxName('swissup_soldtogether_order_related', ['related_id']),
             ['related_id']
         )->addIndex(
             $installer->getIdxName('swissup_soldtogether_order_store', ['store_id']),
             ['store_id']
         )->addForeignKey(
             $installer->getFkName('fk_swissup_soldtogether_order_product', 'product_id', 'catalog_product_entity', 'entity_id'),
             'product_id',
             $installer->getTable('catalog_product_entity'),
             'entity_id',
             \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
         )->addForeignKey(
             $installer->getFkName('fk_swissup_soldtogether_order_related', 'related_id', 'catalog_product_entity', 'entity_id'),
             'related_id',
             $installer->getTable('catalog_product_entity'),
             'entity_id',
             \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
         )->setComment(
             'Swissup SoldTogether Order Table'
         );

        $installer->getConnection()->createTable($table);

         /**
          * Create table 'swissup_soldtogether_customer'
          */
         $table = $installer->getConnection()->newTable(
             $installer->getTable('swissup_soldtogether_customer')
         )->addColumn(
             'relation_id',
             \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
             null,
             ['identity' => true, 'nullable' => false, 'primary' => true],
             'Relation ID'
         )->addColumn(
             'product_id',
             \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
             null,
             ['unsigned' => true, 'nullable' => false],
             'Product ID'
         )->addColumn(
             'related_id',
             \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
             null,
             ['unsigned' => true, 'nullable' => false],
             'Related Product ID'
         )->addColumn(
             'store_id',
             \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
             null,
             ['unsigned' => true, 'nullable' => false],
             'Order Store ID'
         )->addColumn(
             'product_name',
             \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
             null,
             ['nullable' => true, 'default'  => null],
             'Product Name'
         )->addColumn(
             'related_name',
             \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
             null,
             ['nullable'  => true, 'default'  => null],
             'Related Product Name'
         )->addColumn(
             'weight',
             \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
             null,
             ['unsigned' => true, 'nullable' => false],
             'Weight'
         )->addColumn(
             'is_admin',
             \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
             null,
             [ 'nullable'  => false, 'default'  => 0],
             'Is Admin'
         )->addIndex(
             $installer->getIdxName('swissup_soldtogether_customer_product', ['product_id']),
             ['product_id']
         )->addIndex(
             $installer->getIdxName('swissup_soldtogether_customer_related', ['related_id']),
             ['related_id']
         )->addIndex(
             $installer->getIdxName('swissup_soldtogether_customer_store', ['store_id']),
             ['store_id']
         )->addForeignKey(
             $installer->getFkName('fk_swissup_soldtogether_customer_product', 'product_id', 'catalog_product_entity', 'entity_id'),
             'product_id',
             $installer->getTable('catalog_product_entity'),
             'entity_id',
             \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
         )->addForeignKey(
             $installer->getFkName('fk_swissup_soldtogether_customer_related', 'related_id', 'catalog_product_entity', 'entity_id'),
             'related_id',
             $installer->getTable('catalog_product_entity'),
             'entity_id',
             \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
         )->setComment(
             'Swissup SoldTogether Customer Table'
         );

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
