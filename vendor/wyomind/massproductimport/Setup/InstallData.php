<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Install Data needed for Simple Google Shopping
 */
class InstallData implements InstallDataInterface
{
    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @version 2.0.0
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
    
        $installer = $setup;
        $installer->startSetup();

        $sample = [
            "enabled"=>1,
            "name" => "xml_product_update",
            "imported_at" => "2018-11-08 08:45:23",
            "mapping" => "[{\"id\":\"Attribute/varchar/73\",\"label\":\"Product Name\",\"index\":\"1\",\"color\":\"rgba(255, 255, 255, 0.8)\",\"tag\":\"\",\"source\":\"name\",\"default\":\"\",\"scripting\":\"\",\"configurable\":\"0\",\"storeviews\":[\"0\"],\"enabled\":true},{\"id\":\"Attribute/text/75\",\"label\":\"Description\",\"index\":\"2\",\"color\":\"rgba(255, 255, 255, 0.8)\",\"tag\":\"\",\"source\":\"description\",\"default\":\"\",\"scripting\":\"\",\"configurable\":\"0\",\"storeviews\":[\"0\"],\"enabled\":true},{\"id\":\"Price/decimal/77\",\"label\":\"Price\",\"index\":\"3\",\"color\":\"rgba(255, 255, 255, 0.8)\",\"tag\":\"\",\"source\":\"price\",\"default\":\"\",\"scripting\":\"\",\"configurable\":\"0\",\"storeviews\":[\"0\"],\"enabled\":true}]",
            "cron_settings" => "{\"days\":[],\"hours\":[]}",
            "backup" => "1",
            "sql" => "0",
            "sql_file" => "xml_inventory.sql",
            "sql_path" => "var/sample",
            "identifier_offset" => "0",
            "identifier" => "sku",
            "auto_set_instock" => "0",
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
            "file_path" => "http://sample.wyomind.com/massproductimport/xml_product_update.xml",
            "field_delimiter" => "",
            "field_enclosure" => "",
            "xml_xpath_to_product" => "/products/item",
            "use_custom_rules" => "0",
            "custom_rules" => "",
            "last_import_report" => "",
            "webservice_params" => "",
            "webservice_login" => "",
            "webservice_password" => "",
            "images_system_type" => "0",
            "images_use_sftp" => "0",
            "images_ftp_host" => "",
            "images_ftp_port" => "",
            "images_ftp_login" => "",
            "images_ftp_password" => "",
            "images_ftp_active" => "1",
            "images_ftp_dir" => "",
            "product_removal" => "0",
            "create_configurable_onthefly" => "0",
            "xml_column_mapping" => "",
            "preserve_xml_column_mapping" => "0",
            "create_category_onthefly" => "0",
            "category_is_active" => "0",
            "category_include_in_menu" => "0",
            "category_parent_id" => "0",
            "dropbox_token" => "",
            "line_filter" => "",
            "has_header" => "0",
            "tree_detection" => "0",
            "product_target" => "0",
            "post_process_action" => "0",
            "post_process_move_folder" => "",
            "post_process_indexers" => "1",

        ];
        $installer->getConnection()->insert($installer->getTable("massproductimport_profiles"), $sample);
        $sample = [

            "enabled"=>1,
            "name" => "Configurable Products On The Fly",
            "imported_at" => "2018-11-23 11:09:14",
            "mapping" => '[{"id":"System/attribute_set_id","label":"Attribute set","index":"1","color":"rgba(0, 136, 255, 0.5)","tag":"MANDATORY ATTRIBUTES","source":"attribute_set_code","default":"","scripting":"","configurable":"2","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"System/type_id","label":"Type","index":"2","color":"rgba(0, 136, 255, 0.5)","tag":"","source":"product_type","default":"","scripting":"","configurable":"0","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"System/website","label":"Website","index":"4","color":"rgba(0, 136, 255, 0.5)","tag":"","source":"product_websites","default":"","scripting":"","configurable":"2","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Attribute/int/99/visibility","label":"Visibility","index":"8","color":"rgba(0, 136, 255, 0.5)","tag":"","source":"visibility","default":"","scripting":"","configurable":"0","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Attribute/int/99/visibility","label":"Visibility","index":"","color":"rgba(0, 136, 255, 0.5)","tag":"","source":"","default":"Not Visible Individually","scripting":"","configurable":"1","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Attribute/int/97/status","label":"Status","index":"","color":"rgba(0, 136, 255, 0.5)","tag":"","source":"","default":"Disabled","scripting":"","configurable":"2","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Category/mapping","label":"Replace all categories with","index":"3","color":"rgba(34, 255, 0, 0.5)","tag":"CATEGORIES","source":"categories","default":"","scripting":"<?php__LINE_BREAK__ /* Your custom script */__LINE_BREAK__ return \"Default Category/\".$self;__LINE_BREAK__","configurable":"2","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Price/decimal/77","label":"Price","index":"9","color":"rgba(255, 234, 0, 0.5)","tag":"PRICE ATTRIBUTES","source":"price","default":"","scripting":"","configurable":"0","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Price/decimal/78","label":"Special Price","index":"10","color":"rgba(255, 234, 0, 0.5)","tag":"","source":"special_price","default":"","scripting":"","configurable":"0","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Attribute/int/127/tax_class_id","label":"Tax Class","index":"","color":"rgba(255, 234, 0, 0.5)","tag":"","source":"","default":"None","scripting":"","configurable":"2","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Stock/qty","label":"Warehouse France [default] | Quantity","index":"11","color":"rgba(187, 255, 0, 0.5)","tag":"STOCK ATTRIBUTES","source":"qty","default":"","scripting":"","configurable":"0","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Stock/is_in_stock","label":"Warehouse France [default] | Stock Status","index":"","color":"rgba(187, 255, 0, 0.5)","tag":"","source":"","default":"Disabled","scripting":"","configurable":"2","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Attribute/varchar/73","label":"Product Name","index":"5","color":"rgba(255, 81, 0, 0.5)","tag":"PRODUCT ATTRIBUTES","source":"name","default":"","scripting":"","configurable":"2","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Attribute/text/75","label":"Description","index":"6","color":"rgba(255, 81, 0, 0.5)","tag":"","source":"description","default":"","scripting":"<?php__LINE_BREAK__ /* Your custom script */__LINE_BREAK__ return $cell[\"name\"].\" - \".$self;__LINE_BREAK__","configurable":"2","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Attribute/text/76","label":"Short Description","index":"7","color":"rgba(255, 81, 0, 0.5)","tag":"","source":"short_description","default":"","scripting":"<?php__LINE_BREAK__ /* Your custom script */__LINE_BREAK__ return $self.\" - \".$cell[\"description\"].\" - \" .$cell[\"name\"];__LINE_BREAK__","configurable":"2","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Attribute/int/83","label":"Manufacturer","index":"14","color":"rgba(255, 81, 0, 0.5)","tag":"","source":"Manufacturer","default":"","scripting":"","configurable":"2","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Attribute/varchar/134","label":"Gender","index":"15","color":"rgba(255, 81, 0, 0.5)","tag":"","source":"Gender","default":"","scripting":"","configurable":"2","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Attribute/int/141","label":"Sale","index":"17","color":"rgba(255, 0, 238, 0.5)","tag":"CONFIGURABLE PRODUCT ATTRIBUTES","source":"size","default":"","scripting":"","configurable":"0","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"ConfigurableProduct/attributes","label":"Configurable Attributes","index":"","color":"rgba(255, 0, 238, 0.5)","tag":"","source":"","default":"size","scripting":"","configurable":"1","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"ConfigurableProduct/parentSku","label":"Parent SKU","index":"18","color":"rgba(255, 0, 238, 0.5)","tag":"","source":"parent_sku","default":"","scripting":"","configurable":"0","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Image/media_gallery/90","label":"Media Gallery","index":"19","color":"rgba(0, 0, 0, 0.5)","tag":"IMAGES","source":"Images","default":"","scripting":"","configurable":"2","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Image/varchar/87","label":"Base","index":"19","color":"rgba(0, 0, 0, 0.5)","tag":"","source":"Images","default":"","scripting":"<?php__LINE_BREAK__ /* Your custom script */__LINE_BREAK__ $images=explode(\",\",$self);__LINE_BREAK__return array_shift($images);","configurable":"2","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Image/varchar/88","label":"Small","index":"19","color":"rgba(0, 0, 0, 0.5)","tag":"","source":"Images","default":"","scripting":"<?php__LINE_BREAK__ /* Your custom script */__LINE_BREAK__ $images=explode(\",\",$self);__LINE_BREAK__return array_shift($images);","configurable":"2","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Image/varchar/89","label":"Thumbnail","index":"19","color":"rgba(0, 0, 0, 0.5)","tag":"","source":"Images","default":"","scripting":"<?php__LINE_BREAK__ /* Your custom script */__LINE_BREAK__ $images=explode(\",\",$self);__LINE_BREAK__return array_shift($images);","configurable":"2","importupdate":"2","storeviews":["0"],"enabled":true}]',
            "cron_settings" => "{\"days\":[],\"hours\":[]}",
            "backup" => "1",
            "sql" => "0",
            "sql_file" => "configurableProducts.sql",
            "sql_path" => "pub/",
            "identifier_offset" => "0",
            "identifier" => "sku",
            "auto_set_instock" => "0",
            "file_system_type" => "3",
            "profile_method" => "3",
            "default_values" => "",
            "use_sftp" => "0",
            "ftp_host" => "",
            "ftp_port" => "",
            "ftp_password" => "",
            "ftp_login" => "",
            "ftp_active" => "0",
            "ftp_dir" => "",
            "file_type" => "1",
            "file_path" => "https://docs.google.com/spreadsheets/u/1/d/1SEaHH0T_vTkCDFRo_wbQgp467MDsy1e-EgK9oTdv7m0/export?format=csv&amp;id=1SEaHH0T_vTkCDFRo_wbQgp467MDsy1e-EgK9oTdv7m0&amp;gid=188252179",
            "field_delimiter" => ", ",
            "field_enclosure" => "non",
            "xml_xpath_to_product" => "",
            "use_custom_rules" => "0",
            "custom_rules" => "",
            "last_import_report" => "&lt;h3&gt;21 products updated.&lt;/h3&gt;&lt;p&gt;Summer-hoodie-44.5, Summer-hoodie-45, Summer-hoodie-45.5, Summer-hoodie-36, Summer-hoodie-37, Summer-hoodie-38, Summer-hoodie-38.5, Summer-hoodie-40, Summer-hoodie-42, Summer-hoodie-42.5, Summer-hoodie-43, Summer-hoodie-44, Winter-hoodie-44.5, Winter-hoodie-46, Winter-hoodie-36, Winter-hoodie-37, Winter-hoodie-38, Winter-hoodie-38.5, Winter-hoodie-39, Winter-hoodie-40.5, Winter-hoodie-42.5&lt;/p&gt;",
            "webservice_params" => "",
            "webservice_login" => "",
            "webservice_password" => "",
            "images_system_type" => "2",
            "images_use_sftp" => "0",
            "images_ftp_host" => "",
            "images_ftp_port" => "",
            "images_ftp_login" => "",
            "images_ftp_password" => "",
            "images_ftp_active" => "1",
            "images_ftp_dir" => "",
            "product_removal" => "0",
            "create_configurable_onthefly" => "1",
            "xml_column_mapping" => "",
            "preserve_xml_column_mapping" => "0",
            "create_category_onthefly" => "1",
            "category_is_active" => "0",
            "category_include_in_menu" => "0",
            "category_parent_id" => "1",
            "dropbox_token" => "",
            "line_filter" => "#(Summer|Winter)-hoodie-(.*)#",
            "has_header" => "1",
            "tree_detection" => "1",
            "product_target" => "0",
            "post_process_action" => "0",
            "post_process_move_folder" => "",
            "post_process_indexers" => "1",


        ];
        $installer->getConnection()->insert($installer->getTable("massproductimport_profiles"), $sample);
        $sample = [

            "enabled"=>1,
            "name" => "Simple + Configurable Products",
            "imported_at" => "2018-11-23 11:19:16",
            "mapping" => '[{"id":"System/attribute_set_id","label":"Attribute set","index":"1","color":"rgba(0, 136, 255, 0.5)","tag":"MANDATORY ATTRIBUTES","source":"attribute_set_code","default":"","scripting":"","configurable":"0","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"System/type_id","label":"Type","index":"2","color":"rgba(0, 136, 255, 0.5)","tag":"","source":"product_type","default":"","scripting":"","configurable":"0","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"System/website","label":"Website","index":"4","color":"rgba(0, 136, 255, 0.5)","tag":"","source":"product_websites","default":"","scripting":"","configurable":"0","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Attribute/int/99/visibility","label":"Visibility","index":"8","color":"rgba(0, 136, 255, 0.5)","tag":"","source":"visibility","default":"","scripting":"","configurable":"0","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Attribute/int/99/visibility","label":"Visibility","index":"","color":"rgba(0, 136, 255, 0.5)","tag":"","source":"","default":"Not Visible Individually","scripting":"","configurable":"0","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Attribute/int/97/status","label":"Status","index":"","color":"rgba(0, 136, 255, 0.5)","tag":"","source":"","default":"Disabled","scripting":"","configurable":"0","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Category/mapping","label":"Replace all categories with","index":"3","color":"rgba(34, 255, 0, 0.5)","tag":"CATEGORIES","source":"categories","default":"","scripting":"<?php__LINE_BREAK__ /* Your custom script */__LINE_BREAK__ return \"Default Category/\".$self;__LINE_BREAK__","configurable":"0","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Price/decimal/77","label":"Price","index":"9","color":"rgba(255, 234, 0, 0.5)","tag":"PRICE ATTRIBUTES","source":"price","default":"","scripting":"","configurable":"0","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Price/decimal/78","label":"Special Price","index":"10","color":"rgba(255, 234, 0, 0.5)","tag":"","source":"special_price","default":"","scripting":"","configurable":"0","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Attribute/int/127/tax_class_id","label":"Tax Class","index":"","color":"rgba(255, 234, 0, 0.5)","tag":"","source":"","default":"None","scripting":"","configurable":"0","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Stock/qty","label":"Warehouse France [default] | Quantity","index":"11","color":"rgba(187, 255, 0, 0.5)","tag":"STOCK ATTRIBUTES","source":"qty","default":"","scripting":"","configurable":"0","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Stock/is_in_stock","label":"Warehouse France [default] | Stock Status","index":"","color":"rgba(187, 255, 0, 0.5)","tag":"","source":"","default":"Disabled","scripting":"","configurable":"0","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Attribute/varchar/73","label":"Product Name","index":"5","color":"rgba(255, 81, 0, 0.5)","tag":"PRODUCT ATTRIBUTES","source":"name","default":"","scripting":"","configurable":"0","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Attribute/text/75","label":"Description","index":"6","color":"rgba(255, 81, 0, 0.5)","tag":"","source":"description","default":"","scripting":"<?php__LINE_BREAK__ /* Your custom script */__LINE_BREAK__ return $cell[\"name\"].\" - \".$self;__LINE_BREAK__","configurable":"0","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Attribute/text/76","label":"Short Description","index":"7","color":"rgba(255, 81, 0, 0.5)","tag":"","source":"short_description","default":"","scripting":"<?php__LINE_BREAK__ /* Your custom script */__LINE_BREAK__ return $self.\" - \".$cell[\"description\"].\" - \" .$cell[\"name\"];__LINE_BREAK__","configurable":"0","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Attribute/int/83","label":"Manufacturer","index":"14","color":"rgba(255, 81, 0, 0.5)","tag":"","source":"Manufacturer","default":"","scripting":"","configurable":"0","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Attribute/varchar/134","label":"Gender","index":"15","color":"rgba(255, 81, 0, 0.5)","tag":"","source":"Gender","default":"","scripting":"","configurable":"0","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Attribute/int/141","label":"Sale","index":"17","color":"rgba(255, 0, 238, 0.5)","tag":"CONFIGURABLE PRODUCT ATTRIBUTES","source":"size","default":"","scripting":"","configurable":"0","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"ConfigurableProduct/attributes","label":"Configurable Attributes","index":"","color":"rgba(255, 0, 238, 0.5)","tag":"","source":"","default":"size","scripting":"","configurable":"0","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"ConfigurableProduct/parentSku","label":"Parent SKU","index":"18","color":"rgba(255, 0, 238, 0.5)","tag":"","source":"parent_sku","default":"","scripting":"","configurable":"0","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Image/media_gallery/90","label":"Media Gallery","index":"19","color":"rgba(0, 0, 0, 0.5)","tag":"IMAGES","source":"Images","default":"","scripting":"","configurable":"0","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Image/varchar/87","label":"Base","index":"19","color":"rgba(0, 0, 0, 0.5)","tag":"","source":"Images","default":"","scripting":"<?php__LINE_BREAK__ /* Your custom script */__LINE_BREAK__ $images=explode(\",\",$self);__LINE_BREAK__return array_shift($images);","configurable":"0","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Image/varchar/88","label":"Small","index":"19","color":"rgba(0, 0, 0, 0.5)","tag":"","source":"Images","default":"","scripting":"<?php__LINE_BREAK__ /* Your custom script */__LINE_BREAK__ $images=explode(\",\",$self);__LINE_BREAK__return array_shift($images);","configurable":"0","importupdate":"2","storeviews":["0"],"enabled":true},{"id":"Image/varchar/89","label":"Thumbnail","index":"19","color":"rgba(0, 0, 0, 0.5)","tag":"","source":"Images","default":"","scripting":"<?php__LINE_BREAK__ /* Your custom script */__LINE_BREAK__ $images=explode(\",\",$self);__LINE_BREAK__return array_shift($images);","configurable":"0","importupdate":"2","storeviews":["0"],"enabled":true}]',
            "cron_settings" => "{\"days\":[],\"hours\":[]}",
            "backup" => "1",
            "sql" => "0",
            "sql_file" => "configurableProducts.sql",
            "sql_path" => "pub/",
            "identifier_offset" => "0",
            "identifier" => "sku",
            "auto_set_instock" => "0",
            "file_system_type" => "3",
            "profile_method" => "3",
            "default_values" => "",
            "use_sftp" => "0",
            "ftp_host" => "",
            "ftp_port" => "",
            "ftp_password" => "",
            "ftp_login" => "",
            "ftp_active" => "0",
            "ftp_dir" => "",
            "file_type" => "1",
            "file_path" => "https://docs.google.com/spreadsheets/u/1/d/1SEaHH0T_vTkCDFRo_wbQgp467MDsy1e-EgK9oTdv7m0/export?format=csv&amp;id=1SEaHH0T_vTkCDFRo_wbQgp467MDsy1e-EgK9oTdv7m0&amp;gid=188252179",
            "field_delimiter" => ", ",
            "field_enclosure" => "non",
            "xml_xpath_to_product" => "",
            "use_custom_rules" => "0",
            "custom_rules" => "",
            "last_import_report" => "&lt;h3&gt;23 products imported.&lt;/h3&gt;&lt;p&gt;Summer-hoodie, Summer-hoodie-44.5, Summer-hoodie-45, Summer-hoodie-45.5, Summer-hoodie-36, Summer-hoodie-37, Summer-hoodie-38, Summer-hoodie-38.5, Summer-hoodie-40, Summer-hoodie-42, Summer-hoodie-42.5, Summer-hoodie-43, Summer-hoodie-44, Winter-hoodie, Winter-hoodie-44.5, Winter-hoodie-46, Winter-hoodie-36, Winter-hoodie-37, Winter-hoodie-38, Winter-hoodie-38.5, Winter-hoodie-39, Winter-hoodie-40.5, Winter-hoodie-42.5&lt;/p&gt;",
            "webservice_params" => "",
            "webservice_login" => "",
            "webservice_password" => "",
            "images_system_type" => "2",
            "images_use_sftp" => "0",
            "images_ftp_host" => "",
            "images_ftp_port" => "",
            "images_ftp_login" => "",
            "images_ftp_password" => "",
            "images_ftp_active" => "1",
            "images_ftp_dir" => "",
            "product_removal" => "0",
            "create_configurable_onthefly" => "0",
            "xml_column_mapping" => "",
            "preserve_xml_column_mapping" => "0",
            "create_category_onthefly" => "1",
            "category_is_active" => "0",
            "category_include_in_menu" => "0",
            "category_parent_id" => "1",
            "dropbox_token" => "",
            "line_filter" => "",
            "has_header" => "1",
            "tree_detection" => "1",
            "product_target" => "0",
            "post_process_action" => "0",
            "post_process_move_folder" => "",
            "post_process_indexers" => "1",
        ];
        $installer->getConnection()->insert($installer->getTable("massproductimport_profiles"), $sample);

        $installer->endSetup();
    }
}
