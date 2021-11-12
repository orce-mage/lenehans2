<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-finder
 * @version   1.0.18
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Finder\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Mirasvit\Finder\Api\Data\FilterInterface;
use Mirasvit\Finder\Api\Data\FilterOptionInterface;
use Mirasvit\Finder\Api\Data\FinderInterface;
use Mirasvit\Finder\Api\Data\IndexInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $connection = $setup->getConnection();

        $setup->startSetup();

        $table = $connection->newTable(
            $setup->getTable(FinderInterface::TABLE_NAME)
        )->addColumn(
            FinderInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            FinderInterface::ID
        )->addColumn(
            FinderInterface::NAME,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => ''],
            FinderInterface::NAME
        )->addColumn(
            FinderInterface::IS_ACTIVE,
            Table::TYPE_INTEGER,
            1,
            ['nullable' => false, 'default' => 0],
            FinderInterface::IS_ACTIVE
        )->addColumn(
            FinderInterface::DESTINATION_URL,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => ''],
            FinderInterface::DESTINATION_URL
        )->addColumn(
            FinderInterface::BLOCK_TEMPLATE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => ''],
            FinderInterface::BLOCK_TEMPLATE
        )->addColumn(
            FinderInterface::BLOCK_TITLE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => ''],
            FinderInterface::BLOCK_TITLE
        )->addColumn(
            FinderInterface::BLOCK_DESCRIPTION,
            Table::TYPE_TEXT,
            null,
            ['nullable' => false, 'default' => ''],
            FinderInterface::BLOCK_DESCRIPTION
        );

        $connection->dropTable($setup->getTable(FinderInterface::TABLE_NAME));
        $connection->createTable($table);

        $table = $connection->newTable(
            $setup->getTable(FilterInterface::TABLE_NAME)
        )->addColumn(
            FilterInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            FilterInterface::ID
        )->addColumn(
            FilterInterface::FINDER_ID,
            Table::TYPE_INTEGER,
            11,
            ['nullable' => false],
            FilterInterface::FINDER_ID
        )->addColumn(
            FilterInterface::LINK_TYPE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            FilterInterface::LINK_TYPE
        )->addColumn(
            FilterInterface::ATTRIBUTE_CODE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            FilterInterface::ATTRIBUTE_CODE
        )->addColumn(
            FilterInterface::NAME,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => ''],
            FilterInterface::NAME
        )->addColumn(
            FilterInterface::DESCRIPTION,
            Table::TYPE_TEXT,
            null,
            ['nullable' => false, 'default' => ''],
            FilterInterface::DESCRIPTION
        )->addColumn(
            FilterInterface::URL_KEY,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            FilterInterface::URL_KEY
        )->addColumn(
            FilterInterface::POSITION,
            Table::TYPE_INTEGER,
            11,
            ['nullable' => false, 'default' => 0],
            FilterInterface::POSITION
        )->addColumn(
            FilterInterface::IS_REQUIRED,
            Table::TYPE_INTEGER,
            1,
            ['nullable' => false, 'default' => 0],
            FilterInterface::IS_REQUIRED
        )->addColumn(
            FilterInterface::IS_MULTISELECT,
            Table::TYPE_INTEGER,
            1,
            ['nullable' => false, 'default' => 0],
            FilterInterface::IS_MULTISELECT
        )->addColumn(
            FilterInterface::DISPLAY_MODE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => ''],
            FilterInterface::DISPLAY_MODE
        )->addColumn(
            FilterInterface::SORT_MODE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => ''],
            FilterInterface::SORT_MODE
        )->addIndex(
            $setup->getIdxName(FilterInterface::TABLE_NAME, [FilterInterface::FINDER_ID]),
            [FilterInterface::FINDER_ID],
            AdapterInterface::INDEX_TYPE_INDEX
        )->addIndex(
            $setup->getIdxName(FilterInterface::TABLE_NAME, [FilterInterface::URL_KEY]),
            [FilterInterface::URL_KEY],
            AdapterInterface::INDEX_TYPE_INDEX
        );

        $connection->dropTable($setup->getTable(FilterInterface::TABLE_NAME));
        $connection->createTable($table);

        $table = $connection->newTable(
            $setup->getTable(FilterOptionInterface::TABLE_NAME)
        )->addColumn(
            FilterOptionInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            FilterOptionInterface::ID
        )->addColumn(
            FilterOptionInterface::FINDER_ID,
            Table::TYPE_INTEGER,
            11,
            ['nullable' => false],
            FilterOptionInterface::FINDER_ID
        )->addColumn(
            FilterOptionInterface::FILTER_ID,
            Table::TYPE_INTEGER,
            11,
            ['nullable' => false],
            FilterOptionInterface::FILTER_ID
        )->addColumn(
            FilterOptionInterface::NAME,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            FilterOptionInterface::NAME
        )->addColumn(
            FilterOptionInterface::URL_KEY,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            FilterOptionInterface::URL_KEY
        )->addColumn(
            FilterOptionInterface::IMAGE_PATH,
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            FilterOptionInterface::IMAGE_PATH
        )->addIndex(
            $setup->getIdxName(FilterOptionInterface::TABLE_NAME, [FilterOptionInterface::FINDER_ID]),
            [FilterOptionInterface::FINDER_ID],
            AdapterInterface::INDEX_TYPE_INDEX
        )->addIndex(
            $setup->getIdxName(FilterOptionInterface::TABLE_NAME, [FilterOptionInterface::FILTER_ID]),
            [FilterOptionInterface::FILTER_ID],
            AdapterInterface::INDEX_TYPE_INDEX
        )->addIndex(
            $setup->getIdxName(FilterOptionInterface::TABLE_NAME, [FilterOptionInterface::NAME]),
            [FilterOptionInterface::NAME],
            AdapterInterface::INDEX_TYPE_INDEX
        )->addIndex(
            $setup->getIdxName(FilterOptionInterface::TABLE_NAME, [FilterOptionInterface::URL_KEY]),
            [FilterOptionInterface::URL_KEY],
            AdapterInterface::INDEX_TYPE_INDEX
        );

        $connection->dropTable($setup->getTable(FilterOptionInterface::TABLE_NAME));
        $connection->createTable($table);

        $table = $connection->newTable(
            $setup->getTable(IndexInterface::TABLE_NAME)
        )->addColumn(
            IndexInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            IndexInterface::ID
        )->addColumn(
            IndexInterface::PRODUCT_ID,
            Table::TYPE_INTEGER,
            11,
            ['nullable' => false],
            IndexInterface::PRODUCT_ID
        )->addColumn(
            IndexInterface::FINDER_ID,
            Table::TYPE_INTEGER,
            11,
            ['nullable' => false],
            IndexInterface::FINDER_ID
        )->addColumn(
            IndexInterface::FILTER_ID,
            Table::TYPE_INTEGER,
            11,
            ['nullable' => false],
            IndexInterface::FILTER_ID
        )->addColumn(
            IndexInterface::OPTION_ID,
            Table::TYPE_INTEGER,
            11,
            ['nullable' => false],
            IndexInterface::OPTION_ID
        )->addIndex(
            $setup->getIdxName(IndexInterface::TABLE_NAME, [IndexInterface::PRODUCT_ID]),
            [IndexInterface::PRODUCT_ID],
            AdapterInterface::INDEX_TYPE_INDEX
        )->addIndex(
            $setup->getIdxName(IndexInterface::TABLE_NAME, [IndexInterface::FINDER_ID]),
            [IndexInterface::FINDER_ID],
            AdapterInterface::INDEX_TYPE_INDEX
        )->addIndex(
            $setup->getIdxName(IndexInterface::TABLE_NAME, [IndexInterface::FILTER_ID]),
            [IndexInterface::FILTER_ID],
            AdapterInterface::INDEX_TYPE_INDEX
        )->addIndex(
            $setup->getIdxName(IndexInterface::TABLE_NAME, [IndexInterface::OPTION_ID]),
            [IndexInterface::OPTION_ID],
            AdapterInterface::INDEX_TYPE_INDEX
        );

        $connection->dropTable($setup->getTable(IndexInterface::TABLE_NAME));
        $connection->createTable($table);

        $setup->endSetup();
    }
}
