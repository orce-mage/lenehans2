<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

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
    public $framework;

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
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
    
        $this->framework->create()->update(__CLASS__, $context);

        $installer = $setup;
        $installer->startSetup();
        // $context->getVersion() = version du module actuelle
        // 4.0.0 = version en cours d'installation
        if (version_compare($context->getVersion(), '5.0.2') < 0) {
            $tableName = $setup->getTable('massstockupdate_profiles');

            if ($setup->getConnection()->isTableExists($tableName) == true) {
                // webservice
                $setup->getConnection()->addColumn(
                    $tableName,
                    'webservice_params',
                    ['type' => Table::TYPE_TEXT, 'length' => 900, 'nullable' => true, "comment" => 'Webservice params']
                );
                $setup->getConnection()->addColumn(
                    $tableName,
                    'webservice_login',
                    ['type' => Table::TYPE_TEXT, 'length' => 300, 'nullable' => true, "comment" => 'Webservice login']
                );
                $setup->getConnection()->addColumn(
                    $tableName,
                    'webservice_password',
                    ['type' => Table::TYPE_TEXT, 'length' => 300, 'nullable' => true, "comment" => 'Webservice password']
                );

                $setup->getConnection()->addColumn(
                    $tableName,
                    'default_values',
                    ['type' => Table::TYPE_TEXT, 'length' => 900, 'nullable' => true, "comment" => 'Default Values']
                );
                $setup->getConnection()->dropColumn($tableName, "auto_set_total");
            }

            $installer->endSetup();
        }
        if (version_compare($context->getVersion(), '6.0.0') < 0) {
            $installer = $setup;
            $installer->startSetup();


            $tableName = $installer->getTable('massstockupdate_profiles');
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
        if (version_compare($context->getVersion(), '6.1.0.1') < 0) {
            $installer = $setup;
            $installer->startSetup();
            $tableName = $installer->getTable('massstockupdate_profiles');
            $setup->getConnection()->addColumn(
                $tableName,
                'line_filter',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'length' => 300, 'nullable' => false, "comment" => 'Line filter']
            );
            $setup->getConnection()->addColumn(
                $tableName,
                'has_header',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, "default" => 0, "comment" => 'Has header']
            );
            $setup->getConnection()->addColumn(
                $tableName,
                'profile_method',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, "default" => 1, "comment" => 'Profile method']
            );
            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '7.0.0') < 0) {
            $installer = $setup;
            $installer->startSetup();

            $tableName = $installer->getTable('massstockupdate_profiles');
            $setup->getConnection()->addColumn(
                $tableName,
                'dropbox_token',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'length' => 300, 'nullable' => false, "comment" => 'Dropbox token']
            );

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
            $setup->getConnection()->addColumn(
                $tableName,
                'identifier_script',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'length' => 900, 'nullable' => true, "comment" => 'Script for the identifier']
            );


            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '7.4.1') < 0) {
            $installer = $setup;
            $installer->startSetup();

            $tableName = $installer->getTable('massstockupdate_profiles');

            $setup->getConnection()->addColumn(
                $tableName,
                'post_process_indexers',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, 'default' => 1, "comment" => 'Run indexes after import']
            );


            $installer->endSetup();
        }
        if (version_compare($context->getVersion(), '8.0.2') < 0) {
            $installer = $setup;
            $installer->startSetup();
            $tableName = $installer->getTable('massstockupdate_profiles');

            $setup->getConnection()->dropColumn(
                $tableName,
                'auto_set_total'
            );

            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '8.4.0') < 0) {
            $installer = $setup;
            $installer->startSetup();
            $tableName = $installer->getTable('massstockupdate_profiles');


            $setup->getConnection()->addColumn(
                $tableName,
                'is_magento_export',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, 'default' => 2, "comment" => 'Magento export file']
            );

            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '9.3.0') < 0) {
            $installer = $setup;
            $installer->startSetup();

            $tableName = $installer->getTable('massstockupdate_profiles');

            $setup->getConnection()->addColumn(
                $tableName,
                'post_process_indexers_selection',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'length' => 900, 'nullable' => false, "comment" => 'List of  indexes to run after import']
            );


            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '9.7.0') < 0) {
            $installer = $setup;
            $installer->startSetup();

            $tableName = $installer->getTable('massstockupdate_profiles');

            $setup->getConnection()->addColumn(
                $tableName,
                'relative_stock_update',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, "default" => 0, "comment" => 'Enable the relative stock update']
            );


            $installer->endSetup();
        }
        if (version_compare($context->getVersion(), '9.8.0') < 0) {
            $installer = $setup;
            $installer->startSetup();

            $tableName = $installer->getTable('massstockupdate_profiles');

            $setup->getConnection()->addColumn(
                $tableName,
                'enabled',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN, 'length' => 1, 'nullable' => false, "default" => 0, "comment" => 'Enable the profile']
            );


            $installer->endSetup();
        }
        if (version_compare($context->getVersion(), '11.0.0') < 0) {
            // Version history table - a line is added each time a feed is updated
            $this->historyHelper->createVersionHistoryTable($installer, "massstockupdate_profiles");
            $this->historyHelper->createActionHistoryTable($installer, "massstockupdate_profiles");
        }

    }
}
