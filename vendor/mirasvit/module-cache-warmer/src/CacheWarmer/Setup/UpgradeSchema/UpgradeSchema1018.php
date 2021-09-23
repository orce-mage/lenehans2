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
use Mirasvit\CacheWarmer\Api\Data\PageInterface;
use Mirasvit\CacheWarmer\Api\Data\SourceInterface;

class UpgradeSchema1018 implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $setup->getConnection();
        $tableSource   = $connection->newTable(
            $setup->getTable(SourceInterface::TABLE_NAME)
        )->addColumn(
            SourceInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            SourceInterface::ID
        )->addColumn(
            SourceInterface::SOURCE_NAME,
            Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            SourceInterface::SOURCE_NAME
        )->addColumn(
            SourceInterface::SOURCE_TYPE,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => SourceInterface::TYPE_VISITOR],
            SourceInterface::SOURCE_TYPE
        )->addColumn(
            SourceInterface::PATH,
            Table::TYPE_TEXT,
            null,
            ['nullable' => true, 'default' => null],
            SourceInterface::PATH
        )->addColumn(
            SourceInterface::CUSTOMER_GROUPS,
            Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            SourceInterface::CUSTOMER_GROUPS
        )->addColumn(
            SourceInterface::IS_ACTIVE,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => '1'],
            SourceInterface::IS_ACTIVE
        )->addColumn(
            SourceInterface::LAST_SYNC_AT,
            Table::TYPE_DATETIME,
            null,
            ['nullable' => true, 'default' => null],
            SourceInterface::LAST_SYNC_AT
        );
        $connection->createTable($tableSource);

        $connection->addColumn(
            $setup->getTable(PageInterface::TABLE_NAME),
            SourceInterface::ID,
            [
                'type'     => Table::TYPE_INTEGER,
                'nullable' => false,
                'unsigned' => false,
                'comment'  => SourceInterface::ID,
                'default'  => SourceInterface::DEFAULT_SOURCE_ID,
                'after'    => PageInterface::ATTEMPTS,
            ]
        );
    }
}
