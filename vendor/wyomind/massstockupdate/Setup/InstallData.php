<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Install Data needed for Simple Google Shopping
 */
class InstallData implements InstallDataInterface
{
    /**
     * @version 2.0.0
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $sample =
            [
                "enabled"=>1,
                "name" => "xml_inventory_update",
                "imported_at" => "2018-11-08 08:45:23",
                "mapping" => "[{\"id\":\"Msi/quantity/default\",\"label\":\"Warehouse France [default] | Quantity\",\"index\":\"2\",\"color\":\"rgba(255, 255, 255, 0.5)\",\"tag\":\"QTY\",\"source\":\"qty\",\"default\":\"\",\"scripting\":\"\",\"configurable\":\"0\",\"importupdate\":\"2\",\"storeviews\":[\"0\"],\"enabled\":true}]",
                "cron_settings" => "{\"days\":[],\"hours\":[]}",
                "backup" => "1",
                "sql" => "0",
                "sql_file" => "XML_sample.sql",
                "sql_path" => "pub/sample",
                "identifier_offset" => "0",
                "identifier" => "sku",
                "auto_set_instock" => "1",
                "file_system_type" => "3",
                "profile_method" => "1",
                "default_values" => "[]",
                "use_sftp" => "0",
                "ftp_host" => "",
                "ftp_port" => "",
                "ftp_password" => "",
                "ftp_login" => "",
                "ftp_active" => "0",
                "ftp_dir" => "",
                "file_type" => "2",
                "file_path" => "http://sample.wyomind.com/massstockupdate/xml_inventory_update.xml",
                "field_delimiter" => "",
                "field_enclosure" => "",
                "xml_xpath_to_product" => "/products/product",
                "use_custom_rules" => "0",
                "custom_rules" => "",
                "last_import_report" => "",
                "webservice_params" => "",
                "webservice_login" => "",
                "webservice_password" => "",
                "xml_column_mapping" => "",
                "preserve_xml_column_mapping" => "0",
                "dropbox_token" => "",
                "line_filter" => "",
                "has_header" => "0",

            ];


        $installer->getConnection()->insert($installer->getTable("massstockupdate_profiles"), $sample);
        $installer->endSetup();
    }
}
