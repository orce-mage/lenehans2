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
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\CacheWarmer\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Mirasvit\CacheWarmer\Api\Data\JobInterface;
use Mirasvit\CacheWarmer\Api\Data\PageInterface;

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
        $installer  = $setup;
        $connection = $installer->getConnection();

        $installer->startSetup();

        $table = $connection->newTable(
            $installer->getTable(PageInterface::TABLE_NAME)
        )->addColumn(
            'page_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Page Id'
        )->addColumn(
            'uri',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'URI'
        )->addColumn(
            'cache_id',
            Table::TYPE_TEXT,
            '255',
            ['nullable' => true],
            'Cache Id'
        )->addColumn(
            'page_type',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Page Type'
        )->addColumn(
            'product_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Product ID'
        )->addColumn(
            'category_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Category ID'
        )->addColumn(
            'vary_data',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Vary Data'
        )->addColumn(
            'popularity',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => 0],
            'Popularity'
        )->addIndex(
            $installer->getIdxName(PageInterface::TABLE_NAME, ['cache_id']),
            ['cache_id']
        );
        $connection->createTable($table);

        $table = $connection->newTable(
            $installer->getTable(JobInterface::TABLE_NAME)
        )->addColumn(
            'job_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Job Id'
        )->addColumn(
            'priority',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => 0],
            'Job Priority'
        )->addColumn(
            'filter_serialized',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Page Filter'
        )->addColumn(
            'info_serialized',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Info'
        )->addColumn(
            'started_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => true],
            'Started At'
        )->addColumn(
            'finished_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => true],
            'Finished At'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'updated_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
            'Updated At'
        )->addIndex(
            $installer->getIdxName(JobInterface::TABLE_NAME, ['job_id']),
            ['job_id']
        );
        $connection->createTable($table);
    }
}
