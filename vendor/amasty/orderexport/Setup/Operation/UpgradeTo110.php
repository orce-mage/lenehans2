<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Amasty\Orderexport\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeTo110
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amorderexport_profiles'),
            'split_order_items',
            [
                'type' => Table::TYPE_SMALLINT,
                'nullable' => true,
                'default' => '1',
                'after' => 'export_include_fieldnames',
                'comment' => 'Split Order Items'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amorderexport_profiles'),
            'split_order_items_delim',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => ',',
                'length' => 12,
                'after' => 'split_order_items',
                'comment' => 'Split Order Items Delim'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amorderexport_profiles'),
            'xml_main_tag',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => 'orders',
                'length' => 255,
                'after' => 'split_order_items_delim',
                'comment' => 'Xml Main Tag'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amorderexport_profiles'),
            'xml_order_tag',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => 'order',
                'length' => 255,
                'after' => 'xml_main_tag',
                'comment' => 'Xml Order Tag'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amorderexport_profiles'),
            'xml_order_items_tag',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => 'order_items',
                'length' => 255,
                'after' => 'xml_order_tag',
                'comment' => 'Xml Main Tag'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amorderexport_profiles'),
            'xml_order_item_tag',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => 'order_item',
                'length' => 255,
                'after' => 'xml_order_items_tag',
                'comment' => 'Xml Order item Tag'
            ]
        );
    }
}
