<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageBig\MbFrame\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        /* @var $connection \Magento\Framework\DB\Adapter\AdapterInterface */
        $connection = $installer->getConnection();

        $installer->startSetup();

        /**
         * Create table 'design_change'
         */
        $table = $connection->newTable(
            $installer->getTable('design_config_grid_flat')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity ID'
        )->addColumn(
            'store_website_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            [],
            'Store_website_id'
        )->addColumn(
            'store_group_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            [],
            'Store_group_id'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            [],
            'Store_id'
        )->addColumn(
            'theme_theme_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Theme_theme_id'
        )->addIndex(
            $installer->getIdxName('design_config_grid_flat', ['store_website_id']),
            ['store_website_id']
        )->addIndex(
            $installer->getIdxName('design_config_grid_flat', ['store_group_id']),
            ['store_group_id']
        )->addIndex(
            $installer->getIdxName('design_config_grid_flat', ['store_id']),
            ['store_id']
        )->addIndex(
            $installer->getIdxName('design_config_grid_flat', ['theme_theme_id']),
            ['theme_theme_id'], ['type' => 'fulltext']
        )->setComment(
            'design_config_grid_flat'
        );
        $connection->createTable($table);

        $installer->endSetup();
    }
}
