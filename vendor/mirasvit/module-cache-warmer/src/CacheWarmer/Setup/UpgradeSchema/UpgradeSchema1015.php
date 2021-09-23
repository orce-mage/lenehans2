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

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Mirasvit\CacheWarmer\Api\Data\PageInterface;
use Mirasvit\CacheWarmer\Api\Repository\PageRepositoryInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

class UpgradeSchema1015 implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $setup->getConnection();

        $connection->addColumn(
            $setup->getTable(PageInterface::TABLE_NAME),
            PageInterface::URI_HASH,
            [
                'type'     => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment'  => PageInterface::URI_HASH,
                'after' => PageInterface::URI,
            ]
        );
        $connection->modifyColumn(
            $setup->getTable(PageInterface::TABLE_NAME),
            PageInterface::VARY_DATA_HASH,
            [
                'type'     => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment'  => PageInterface::VARY_DATA_HASH,
                'after' => PageInterface::VARY_DATA,
            ]
        );
        $connection->modifyColumn(
            $setup->getTable(PageInterface::TABLE_NAME),
            PageInterface::WARM_RULE_IDS,
            [
                'type'     => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment'  => PageInterface::WARM_RULE_IDS,
            ]
        );
        $connection->modifyColumn(
            $setup->getTable(PageInterface::TABLE_NAME),
            PageInterface::WARM_RULE_VERSION,
            [
                'type'     => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment'  => PageInterface::WARM_RULE_VERSION,
            ]
        );
        $connection->modifyColumn(
            $setup->getTable(PageInterface::TABLE_NAME),
            PageInterface::STATUS,
            [
                'type'     => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment'  => PageInterface::STATUS,
            ]
        );
        $connection->addColumn(
            $setup->getTable(PageInterface::TABLE_NAME),
            PageInterface::COOKIE,
            [
                'type'     => Table::TYPE_TEXT,
                'length' => 4096,
                'nullable' => false,
                'comment'  => PageInterface::COOKIE,
                'after' => PageInterface::VARY_DATA_HASH,
            ]
        );

        $connection->addIndex(
            $setup->getTable(PageInterface::TABLE_NAME),
            $setup->getIdxName(PageInterface::TABLE_NAME, ['uri_hash', 'vary_data_hash'], AdapterInterface::INDEX_TYPE_INDEX),
            [
                'uri_hash', 'vary_data_hash'
            ]
        );

        $connection->addIndex(
            $setup->getTable(PageInterface::TABLE_NAME),
            $setup->getIdxName(PageInterface::TABLE_NAME, ['warm_rule_ids'], AdapterInterface::INDEX_TYPE_INDEX),
            [
                'warm_rule_ids',
            ]
        );
    }
}
