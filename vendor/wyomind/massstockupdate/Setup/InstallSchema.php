<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Install schema for Simple Google Shopping
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @version 4.0.0
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
    
        $installer = $setup;
        $installer->startSetup();

        $installer->getConnection()->dropTable($installer->getTable('massstockupdate_profiles')); // drop if exists

        $massStockUpdate = $installer->getConnection()
            ->newTable($installer->getTable('massstockupdate_profiles'))
            // usual columns
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                150,
                ['nullable' => false],
                'Name'
            )
            ->addColumn(
                'imported_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Last Import date'
            )
            ->addColumn(
                'mapping',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Columns mapping'
            )
            ->addColumn(
                'cron_settings',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                900,
                [],
                'Cron Schedule'
            )
            // backup
            ->addColumn(
                'backup',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ['nullable' => false, 'default' => 1],
                'Create backup before running the profile ?'
            )
            // sql
            ->addColumn(
                'sql',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ['nullable' => false, 'default' => 1],
                'Create SQL file without updating ?'
            )
            ->addColumn(
                'sql_file',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                150,
                ['nullable' => true],
                'SQL file name'
            )
            ->addColumn(
                'sql_path',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                500,
                ['nullable' => true],
                'SQL file path'
            )
            // import settings
            ->addColumn(
                'identifier_offset',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                2,
                ['nullable' => false, 'default' => 1],
                'Column Where To Find The Product Identifier In The CSV File'
            )
            ->addColumn(
                'identifier',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                150,
                ['nullable' => false],
                'Unique identifier'
            )
            ->addColumn(
                'auto_set_total',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ['nullable' => false, 'default' => 1],
                'Set The Global Stock Automatically'
            )
            ->addColumn(
                'auto_set_instock',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ['nullable' => false, 'default' => 1],
                'Set The Stock Status Automatically'
            )
            // File location
            ->addColumn(
                'file_system_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ['nullable' => false, 'default' => 0],
                'File System Type (local,ftp,url)'
            )
            // FTP File System
            ->addColumn(
                'use_sftp',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ["default" => "0"],
                'Profile Use Sftp ?'
            )
            ->addColumn(
                'ftp_host',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                300,
                [],
                'Profile Ftp Host'
            )
            ->addColumn(
                'ftp_port',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                5,
                [],
                'Profile Ftp Port'
            )
            ->addColumn(
                'ftp_password',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                300,
                [],
                'Profile Ftp Password'
            )
            ->addColumn(
                'ftp_login',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                300,
                [],
                'Profile Ftp Login'
            )
            ->addColumn(
                'ftp_active',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ["default" => "0"],
                'Profile Ftp Active Mode'
            )
            ->addColumn(
                'ftp_dir',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                300,
                [],
                'Profile Ftp Dir'
            )
            // common

            ->addColumn(
                'file_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ['nullable' => false, 'default' => 0],
                'File Type (csv,xml)'
            )
            ->addColumn(
                'file_path',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                900,
                ['nullable' => false],
                'File Path'
            )
            // CSV

            ->addColumn(
                'field_delimiter',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                3,
                [],
                'CSV Field Delimiter'
            )
            ->addColumn(
                'field_enclosure',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                3,
                [],
                'CSV Field enclosure'
            )
            // XML

            ->addColumn(
                'xml_xpath_to_product',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                300,
                ['nullable' => false],
                'XML XPath To The Product'
            )
            // custom rules
            ->addColumn(
                'use_custom_rules',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ['nullable' => false, 'default' => 0],
                'Does The CSV Import Use Custom Rules'
            )
            ->addColumn(
                'custom_rules',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'CSV Custom Rules'
            )
            // last import report
            ->addColumn(
                'last_import_report',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Last import report'
            )
            // indexes
            ->addIndex(
                $installer->getIdxName('massstockupdate_profiles', ['id']),
                ['id']
            )
            ->setComment('MassStockUpdate profiles table');

        $installer->getConnection()->createTable($massStockUpdate);

        $installer->endSetup();
    }
}
