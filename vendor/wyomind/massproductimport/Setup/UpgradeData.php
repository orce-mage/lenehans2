<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Upgrade data for SImple Google Shopping
 */
class UpgradeData implements UpgradeDataInterface
{

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {


        if (version_compare($context->getVersion(), '4.0.0') < 0) {
            $tableMpui = $setup->getTable('massproductimport_profiles');
            $tableMsi = $setup->getTable('massstockupdate_profiles');
            $select = $setup->getConnection()->select()->from($tableMsi)->reset(\Zend_Db_Select::COLUMNS)->columns([

                "sql",
                "sql_path",
                "sql_file",
                "name",
                "file_path",
                "field_delimiter",
                "field_enclosure",
                "auto_set_instock",
                "mapping",
                "cron_settings",
                "imported_at",
                "identifier_offset",
                "use_custom_rules",
                "custom_rules",
                "identifier",
                "file_system_type",
                "use_sftp",
                "ftp_host",
                "ftp_login",
                "ftp_password",
                "ftp_active",
                "ftp_dir",
                "file_type",
                "xml_xpath_to_product",
                "default_values",
            ]);
            $setup->getConnection()->query($setup->getConnection()->insertFromSelect($select, $tableMpui, [
                "sql",
                "sql_path",
                "sql_file",
                "name",
                "file_path",
                "field_delimiter",
                "field_enclosure",
                "auto_set_instock",
                "mapping",
                "cron_settings",
                "imported_at",
                "identifier_offset",
                "use_custom_rules",
                "custom_rules",
                "identifier",
                "file_system_type",
                "use_sftp",
                "ftp_host",
                "ftp_login",
                "ftp_password",
                "ftp_active",
                "ftp_dir",
                "file_type",
                "xml_xpath_to_product",
                "default_values",
            ]));

            $setup->endSetup();
        }
    }
}
