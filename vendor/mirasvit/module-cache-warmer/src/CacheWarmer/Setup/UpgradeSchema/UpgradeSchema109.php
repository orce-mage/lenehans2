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
use Mirasvit\CacheWarmer\Api\Data\WarmRuleInterface;

class UpgradeSchema109 implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection   = $setup->getConnection();
        $jobRuleTable = $connection->newTable(
            $setup->getTable(WarmRuleInterface::TABLE_NAME)
        )->addColumn(
            WarmRuleInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            WarmRuleInterface::ID
        )->addColumn(
            WarmRuleInterface::NAME,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            WarmRuleInterface::NAME
        )->addColumn(
            WarmRuleInterface::IS_ACTIVE,
            Table::TYPE_INTEGER,
            1,
            ['nullable' => false, 'default' => 0],
            WarmRuleInterface::IS_ACTIVE
        )->addColumn(
            WarmRuleInterface::PRIORITY,
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => 1],
            WarmRuleInterface::PRIORITY
        )->addColumn(
            WarmRuleInterface::CONDITIONS_SERIALIZED,
            Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            WarmRuleInterface::CONDITIONS_SERIALIZED
        )->addColumn(
            WarmRuleInterface::HEADERS_SERIALIZED,
            Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            WarmRuleInterface::HEADERS_SERIALIZED
        )->addColumn(
            WarmRuleInterface::VARY_DATA_SERIALIZED,
            Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            WarmRuleInterface::VARY_DATA_SERIALIZED
        );
        $connection->createTable($jobRuleTable);

//        $connection->addColumn(
//            $setup->getTable(PageInterface::TABLE_NAME),
//            PageInterface::RANK,
//            [
//                'type'     => Table::TYPE_INTEGER,
//                'nullable' => false,
//                'default'  => 0,
//                'comment'  => PageInterface::RANK,
//            ]
//        );


        $connection->addColumn(
            $setup->getTable(PageInterface::TABLE_NAME),
            PageInterface::WARM_RULE_VERSION,
            [
                'type'     => Table::TYPE_TEXT,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'comment'  => PageInterface::WARM_RULE_VERSION,
            ]
        );


        $connection->addColumn(
            $setup->getTable(PageInterface::TABLE_NAME),
            PageInterface::WARM_RULE_IDS,
            [
                'type'     => Table::TYPE_TEXT,
                'nullable' => true,
                'comment'  => PageInterface::WARM_RULE_IDS,
            ]
        );
    }
}
