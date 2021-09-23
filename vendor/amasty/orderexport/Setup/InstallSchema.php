<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Amasty\Orderexport\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
        $table = $installer
            ->getConnection()
            ->newTable($installer->getTable('amasty_amorderexport_profiles'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                NULL,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Profile Id'
            )
            ->addColumn(
                'enabled',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                NULL,
                ['default' => 1, 'nullable' => false]
            )
            ->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => NULL, 'nullable' => false],
                'Name'
            )
            ->addColumn(
                'store_ids',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                NULL,
                ['default' => NULL, 'nullable' => false],
                'Store Ids'
            )
            ->addColumn(
                'filename',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => NULL, 'nullable' => false],
                'Exported filename'
            )
            ->addColumn(
                'export_add_timestamp',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                NULL,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'path',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => NULL, 'nullable' => false],
                'Exported file path'
            )
            ->addColumn(
                'ftp_use',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                NULL,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'ftp_host',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                NULL,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'ftp_login',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'ftp_password',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'ftp_is_passive',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                NULL,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'ftp_path',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                NULL,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'ftp_delete_local',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                NULL,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'email_use',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                NULL,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'email_address',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'email_subject',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'email_compress',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                NULL,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'format',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                NULL,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'csv_delim',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                12,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'csv_enclose',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                12,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'export_include_fieldnames',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                NULL,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'export_allfields',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                NULL,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'field_mapping',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                NULL,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'export_custom_options',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                NULL,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'export_attributes_info',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                NULL,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'filter_number_enabled',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                NULL,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'filter_number_from',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'filter_number_to',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'filter_number_from_skip',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                NULL,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'filter_shipment_enabled',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                NULL,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'filter_shipment_from',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'filter_shipment_to',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'filter_invoice_enabled',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                NULL,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'filter_invoice_from',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'filter_invoice_to',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'filter_invoice_from_skip',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                NULL,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'filter_status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                NULL,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'filter_customergroup',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                5,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'filter_customergroup_ids',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                NULL,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'filter_date_enabled',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                NULL,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'filter_date_from',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                NULL,
                ['default' => null, 'nullable' => true]
            )
            ->addColumn(
                'filter_date_to',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                NULL,
                ['default' => null, 'nullable' => true]
            )
            ->addColumn(
                'filter_skip_zero_price',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                NULL,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'filter_sku',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                NULL,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'filter_sku_onlylines',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                NULL,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                NULL,
                ['default' => null, 'nullable' => true]
            )
            ->addColumn(
                'lastrun_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                NULL,
                ['default' => null, 'nullable' => true]
            )
            ->addColumn(
                'last_increment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'last_invoice_increment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'increment_auto',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                NULL,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'invoice_increment_auto',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                NULL,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'post_date_format',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'post_status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'run_after_order_creation',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                NULL,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'type',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                NULL,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'mapping_count',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                NULL,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'strategy',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                NULL,
                ['default' => 0, 'nullable' => false]
            )->addColumn(
                'static_fields',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                NULL,
                ['nullable' => false]
            );
        $installer->getConnection()->createTable($table);


        /*   -------    */


        $table = $installer
            ->getConnection()
            ->newTable($installer->getTable('amasty_amorderexport_fields'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                NULL,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Profile Field Id'
            )
            ->addColumn(
                'profile_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                NULL,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'field_table',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'field_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'mapped_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'handler',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'sorting_order',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                5,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'mapping',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                NULL,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'field_mapping',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                NULL,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'merge_fields',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                NULL,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'delimiters',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                NULL,
                ['default' => NULL, 'nullable' => false]
            );
        $installer->getConnection()->createTable($table);


        /*   -------    */


        $table = $installer
            ->getConnection()
            ->newTable($installer->getTable('amasty_amorderexport_history'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                NULL,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Profile Field Id'
            )
            ->addColumn(
                'profile_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                NULL,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'run_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                NULL,
                ['default' => null, 'nullable' => true]
            )
            ->addColumn(
                'last_increment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'last_invoice_increment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'file_path',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                455,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'file_size',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                NULL,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'run_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                NULL,
                ['default' => NULL, 'nullable' => true]
            )
            ->addColumn(
                'run_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                NULL,
                ['default' => 0, 'nullable' => false]
            )
            ->addColumn(
                'run_records',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                5,
                ['default' => 0, 'nullable' => false]
            );
        $installer->getConnection()->createTable($table);


        /*   -------    */


        $table = $installer
            ->getConnection()
            ->newTable($installer->getTable('amasty_amorderexport_thirdparty'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                NULL,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                '3rdParty Mapping Id'
            )
            ->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'table_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'join_field_base',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'join_field_reference',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => NULL, 'nullable' => false]
            )
            ->addColumn(
                'mapping',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                NULL,
                ['default' => NULL, 'nullable' => false]
            );
        $installer->getConnection()->createTable($table);


        /*
         * end setup
        */
        $installer->endSetup();
    }
}
