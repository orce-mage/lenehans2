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
use Mirasvit\CacheWarmer\Api\Data\PageTypeInterface;

class UpgradeSchema104 implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $setup->getConnection();
        $tableLog   = $connection->newTable(
            $setup->getTable(PageTypeInterface::TABLE_NAME)
        )->addColumn(
            PageTypeInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Page type Id'
        )->addColumn(
            PageTypeInterface::PAGE_TYPE,
            Table::TYPE_TEXT,
            '255',
            ['nullable' => true],
            'Page type'
        )->addIndex(
            $setup->getIdxName(PageTypeInterface::TABLE_NAME, [PageTypeInterface::ID]),
            [PageTypeInterface::ID]
        )->addIndex(
            $setup->getIdxName(PageTypeInterface::TABLE_NAME, [PageTypeInterface::PAGE_TYPE]),
            [PageTypeInterface::PAGE_TYPE]
        );
        $connection->createTable($tableLog);
    }
}
