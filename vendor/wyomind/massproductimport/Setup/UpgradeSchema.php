<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade schema for Simple Google Shopping
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * @var \Wyomind\Framework\Helper\History
     */
    protected $historyHelper;
    /**
     * @var \Wyomind\Framework\Helper\ModuleFactory
     */
    public $license;

    /**
     * UpgradeSchema constructor.
     * @param \Wyomind\Framework\Helper\License\UpdateFactory $license
     * @param \Wyomind\Framework\Helper\History $historyHelper
     */
    public function __construct(
        \Wyomind\Framework\Helper\License\UpdateFactory $license,
        \Wyomind\Framework\Helper\History $historyHelper
    ) {
    
        $this->framework = $license;
        $this->historyHelper = $historyHelper;
    }


    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
    
        $this->framework->create()->update(__CLASS__, $context);


        if (version_compare($context->getVersion(), '1.3.0') < 0) {
            $installer = $setup;
            $installer->startSetup();


            $tableName = $installer->getTable('massproductimport_profiles');
            // webservice
            $setup->getConnection()->addColumn(
                $tableName,
                'product_removal',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, "default" => 0, "comment" => 'Product removal option']
            );
            $setup->getConnection()->addColumn(
                $tableName,
                'create_configurable_onthefly',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, "default" => 0, "comment" => 'Create Configurable Product on the fly']
            );


            $installer->endSetup();
        }
        if (version_compare($context->getVersion(), '1.6.0') < 0) {
            $installer = $setup;
            $installer->startSetup();


            $tableName = $installer->getTable('massproductimport_profiles');
            // webservice
            $setup->getConnection()->addColumn(
                $tableName,
                'xml_column_mapping',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, "comment" => 'Xml columns order']
            );
            $setup->getConnection()->addColumn(
                $tableName,
                'preserve_xml_column_mapping',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, "default" => 0, "comment" => 'Preserve the xml column order']
            );

            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.7.0') < 0) {
            $installer = $setup;
            $installer->startSetup();


            $tableName = $installer->getTable('massproductimport_profiles');

            $setup->getConnection()->dropColumn(
                $tableName,
                'category_mapping'
            );
            $setup->getConnection()->addColumn(
                $tableName,
                'create_category_onthefly',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, "default" => 0, "comment" => 'Allow categories to be created on the fly']
            );
            $setup->getConnection()->addColumn(
                $tableName,
                'category_is_active',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, "default" => 0, "comment" => 'Default value  for is_active']
            );
            $setup->getConnection()->addColumn(
                $tableName,
                'category_include_in_menu',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, "default" => 0, "comment" => 'Default value for include in menu']
            );
            $setup->getConnection()->addColumn(
                $tableName,
                'category_parent_id',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 11, 'nullable' => false, "default" => 0, "comment" => 'Default parent category']
            );

            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '2.2.0') < 0) {
            $installer = $setup;
            $installer->startSetup();
            $tableName = $installer->getTable('massproductimport_profiles');
            $setup->getConnection()->addColumn(
                $tableName,
                'dropbox_token',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'length' => 300, 'nullable' => false, "comment" => 'Dropbox token']
            );
            $setup->getConnection()->addColumn(
                $tableName,
                'line_filter',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'length' => 300, 'nullable' => false, "comment" => 'Line filter']
            );
            $setup->getConnection()->addColumn(
                $tableName,
                'has_header',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, "default" => 1, "comment" => 'Has header']
            );
            $setup->getConnection()->addColumn(
                $tableName,
                'tree_detection',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, "default" => 0, "comment" => 'Tree detection']
            );
            $setup->getConnection()->addColumn(
                $tableName,
                'product_target',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, "default" => 0, "comment" => 'Action type when product removal is enabled']
            );

            $tableName = $setup->getTable('catalog_product_entity');
            $setup->getConnection()->addColumn(
                $tableName,
                'created_by',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 11, 'nullable' => true, "default" => null, "comment" => 'Created by Mass Product Import profiles id']
            );
            $setup->getConnection()->addColumn(
                $tableName,
                'updated_by',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 11, 'nullable' => true, "default" => null, "comment" => 'Created by Mass Product Import profiles id']
            );
            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '2.3.0') < 0) {
            $installer = $setup;
            $installer->startSetup();
            $tableName = $installer->getTable('massproductimport_profiles');
            $setup->getConnection()->addColumn(
                $tableName,
                'post_process_action',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, 'default' => 0, "comment" => 'Post process action']
            );
            $setup->getConnection()->addColumn(
                $tableName,
                'post_process_move_folder',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'length' => 300, 'nullable' => true, "comment" => 'Post process: move folder']
            );

            $installer->endSetup();
        }
        if (version_compare($context->getVersion(), '3.2.2') < 0) {
            $installer = $setup;
            $installer->startSetup();

            $tableName = $installer->getTable('massproductimport_profiles');

            $setup->getConnection()->addColumn(
                $tableName,
                'post_process_indexers',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, 'default' => 1, "comment" => 'Run indexes after import']
            );


            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '3.2.2.1') < 0) {
            $installer = $setup;
            $installer->startSetup();
            $tableName = $installer->getTable('massproductimport_profiles');

            $setup->getConnection()->addColumn(
                $tableName,
                'identifier_script',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'length' => 900, 'nullable' => true, "comment" => 'Script for the identifier']
            );

            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '4.0.0') < 0) {
            $installer = $setup;
            $installer->startSetup();
            $tableName = $installer->getTable('massproductimport_profiles');

            $setup->getConnection()->dropColumn(
                $tableName,
                'auto_set_total'
            );

            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '4.3.0') < 0) {
            $installer = $setup;
            $installer->startSetup();
            $tableName = $installer->getTable('massproductimport_profiles');


            $setup->getConnection()->addColumn(
                $tableName,
                'is_magento_export',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, 'default' => 2, "comment" => 'Magento export file']
            );

            $installer->endSetup();
        }
        if (version_compare($context->getVersion(), '4.3.0') < 0) {
            $installer = $setup;
            $installer->startSetup();
            $tableName = $installer->getTable('massproductimport_profiles');


            $setup->getConnection()->addColumn(
                $tableName,
                'is_magento_export',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, 'default' => 2, "comment" => 'Magento export file']
            );

            $installer->endSetup();
        }


        if (version_compare($context->getVersion(), '5.1.0') < 0) {
            $installer = $setup;
            $installer->startSetup();

            $tableName = $installer->getTable('massproductimport_profiles');

            $setup->getConnection()->addColumn(
                $tableName,
                'post_process_indexers_selection',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'length' => 900, 'nullable' => false, "comment" => 'List of  indexes to run after import']
            );


            $installer->endSetup();
        }


        if (version_compare($context->getVersion(), '6.0.0') < 0) {
            $installer = $setup;
            $installer->startSetup();

            $massStockUpdate = $installer->getConnection()
                ->newTable($installer->getTable('massproductimport_rules'))
                // usual columns
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )
                ->addColumn(
                    'name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    300,
                    ['nullable' => false],
                    'Name of the mapping'
                )
                ->addColumn(
                    'use_regexp',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    1,
                    ['nullable' => false, "default" => 0],
                    'Name of the mapping'
                )
                ->setComment('MassProductImport mapping table');


            $installer->getConnection()->createTable($massStockUpdate);


            $massStockUpdate = $installer->getConnection()
                ->newTable($installer->getTable('massproductimport_replacement'))
                // usual columns
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )
                ->addColumn(
                    'rule_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    ['unsigned' => true, 'nullable' => false],
                    'Mapping id '
                )
                ->addColumn(
                    'input',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    300,
                    ['nullable' => false],
                    'Input value (regular expression)'
                )
                ->addColumn(
                    'output',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    300,
                    ['nullable' => false],
                    'Output value (including replacements)'
                )
                ->addColumn(
                    'position',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    3,
                    ['nullable' => false, "default" => 0],
                    'Output value (including replacements)'
                )
                ->addForeignKey("massproductimport_replacement_rule_id", "rule_id", $installer->getTable('massproductimport_rules'), "id", true)
                ->setComment('MassProductImport mapping-values table');


            $installer->getConnection()->createTable($massStockUpdate);

            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '6.4.0') < 0) {
            $installer = $setup;
            $installer->startSetup();

            $tableName = $installer->getTable('eav_attribute_option_value');

            $setup->getConnection()->addIndex(
                $tableName,
                $setup->getIdxName($tableName, ['option_id', 'store_id']),
                ['option_id', 'store_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            );

            $installer->endSetup();
        }
        if (version_compare($context->getVersion(), '6.5.0') < 0) {
            $installer = $setup;
            $installer->startSetup();

            $tableName = $installer->getTable('massproductimport_profiles');

            $setup->getConnection()->addColumn(
                $tableName,
                'source_target',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'length' => 900, 'nullable' => false, "comment" => 'List of  sources set as out of stock for product removal ']
            );
            $setup->getConnection()->addColumn(
                $tableName,
                'relative_stock_update',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, "default" => 0, "comment" => 'Enable the relative stock update']
            );

            $installer->endSetup();
        }


        if (version_compare($context->getVersion(), '6.7.2') < 0) {
            $installer = $setup;
            $installer->startSetup();

            $tableName = $installer->getTable('massproductimport_profiles');

            $setup->getConnection()->addColumn(
                $tableName,
                'enabled',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN, 'length' => 1, 'nullable' => false, "default" => 0, "comment" => 'Enable the profile']
            );


            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '6.8.0') < 0) {
            $installer = $setup;
            $installer->startSetup();

            $tableName = $installer->getTable('massproductimport_profiles');

            $setup->getConnection()->addColumn(
                $tableName,
                'pos_target',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'length' => 900, 'nullable' => false, "comment" => 'List of pos/wh (Advanced Inventory) set as out of stock for product removal ']
            );

            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '8.0.0') < 0) {
            $installer = $setup;
            // Version history table - a line is added each time a feed is updated
            $this->historyHelper->createVersionHistoryTable($installer, "massproductimport_profiles");
            $this->historyHelper->createActionHistoryTable($installer, "massproductimport_profiles");
        }

        if (version_compare($context->getVersion(), '8.2.1') < 0) {
            $installer = $setup;
            $installer->startSetup();

            // Increase the size of table fields
            $tableName = $installer->getTable('massproductimport_profiles');
            $setup->getConnection()->modifyColumn(
                $setup->getTable($tableName),
                'mapping',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => \Magento\Framework\DB\Ddl\Table::MAX_TEXT_SIZE
                ]
            );

            $tableName = $installer->getTable('massproductimport_profiles_action_history');
            $setup->getConnection()->modifyColumn(
                $setup->getTable($tableName),
                'details',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => \Magento\Framework\DB\Ddl\Table::MAX_TEXT_SIZE
                ]
            );

            $tableName = $installer->getTable('massproductimport_profiles_version_history');
            $setup->getConnection()->modifyColumn(
                $setup->getTable($tableName),
                'content',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => \Magento\Framework\DB\Ddl\Table::MAX_TEXT_SIZE
                ]
            );

            $installer->endSetup();
        }

    }
}
