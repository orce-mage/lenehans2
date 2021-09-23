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
use Mirasvit\CacheWarmer\Api\Data\TraceInterface;

class UpgradeSchema1010 implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $setup->getConnection();
        $traceTable = $connection->newTable(
            $setup->getTable(TraceInterface::TABLE_NAME)
        )->addColumn(
            TraceInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            TraceInterface::ID
        )->addColumn(
            TraceInterface::ENTITY_TYPE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            TraceInterface::ENTITY_TYPE
        )->addColumn(
            TraceInterface::ENTITY_ID,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            TraceInterface::ENTITY_ID
        )->addColumn(
            TraceInterface::TRACE,
            Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            TraceInterface::TRACE
        )->addColumn(
            TraceInterface::STARTED_AT,
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => true],
            TraceInterface::STARTED_AT
        )->addColumn(
            TraceInterface::FINISHED_AT,
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => true],
            TraceInterface::FINISHED_AT
        )->addColumn(
            TraceInterface::CREATED_AT,
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            TraceInterface::CREATED_AT
        )->addIndex(
            $setup->getIdxName(TraceInterface::TABLE_NAME, [TraceInterface::ENTITY_TYPE]),
            [TraceInterface::ENTITY_TYPE]
        )->addIndex(
            $setup->getIdxName(TraceInterface::TABLE_NAME, [TraceInterface::ENTITY_ID]),
            [TraceInterface::ENTITY_ID]
        );

        $connection->createTable($traceTable);
    }
}
