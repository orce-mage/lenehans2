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



namespace Mirasvit\CacheWarmer\Setup\UpgradeSchema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema101 implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $setup->getConnection();
        $tableLog   = $connection->newTable(
            $setup->getTable('mst_cache_warmer_report')
        )->addColumn(
            'report_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Report Id'
        )->addColumn(
            'response_time',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Response Time'
        )->addColumn(
            'response_time_hit',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Hit Response Time'
        )->addColumn(
            'response_time_miss',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Miss Response Time'
        )->addColumn(
            'hit',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => '0'],
            'Hit'
        )->addColumn(
            'miss',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => '0'],
            'Miss'
        )->addColumn(
            'url',
            Table::TYPE_TEXT,
            '1024',
            ['nullable' => true],
            'Url'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Created At'
        )->addIndex(
            $setup->getIdxName('mst_cache_warmer_report', ['report_id']),
            ['report_id']
        );
        $connection->createTable($tableLog);

        $tableLogAggregated = $connection->newTable(
            $setup->getTable('mst_cache_warmer_report_aggregated')
        )->addColumn(
            'report_aggreg_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Report Id'
        )->addColumn(
            'response_time',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Response Time'
        )->addColumn(
            'response_time_hit',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Hit Response Time'
        )->addColumn(
            'response_time_miss',
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Miss Response Time'
        )->addColumn(
            'hit',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => '0'],
            'Hit'
        )->addColumn(
            'miss',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => '0'],
            'Miss'
        )->addColumn(
            'visit_count',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => '0'],
            'Visit count'
        )->addColumn(
            'period',
            Table::TYPE_TIMESTAMP,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Hour period'
        )->addIndex(
            $setup->getIdxName('mst_cache_warmer_report_aggregated', ['report_aggreg_id']),
            ['report_aggreg_id']
        );
        $connection->createTable($tableLogAggregated);
    }
}
