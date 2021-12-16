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
 * @package   mirasvit/module-sorting
 * @version   1.1.14
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Sorting\Setup\UpgradeSchema;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Mirasvit\Sorting\Api\Data\CriterionInterface;
use Mirasvit\Sorting\Api\Data\IndexInterface;

class UpgradeSchema105 implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $setup->getConnection();
        $indexTable = $setup->getTable(IndexInterface::TABLE_NAME);

        if ($connection->isTableExists($indexTable)) {
            $connection->dropTable($indexTable);
        }

        $table = $connection->newTable(
            $indexTable
        )->addColumn(
            IndexInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'identity' => true, 'primary' => true],
            IndexInterface::ID
        )->addColumn(
            IndexInterface::PRODUCT_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            IndexInterface::PRODUCT_ID
        )->addColumn(
            IndexInterface::STORE_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 0],
            CriterionInterface::CODE
        )->addIndex(
            $setup->getIdxName(IndexInterface::TABLE_NAME, [IndexInterface::PRODUCT_ID]),
            [IndexInterface::PRODUCT_ID]
        )->addIndex(
            $setup->getIdxName(IndexInterface::TABLE_NAME, [IndexInterface::STORE_ID]),
            [IndexInterface::STORE_ID]
        )->addIndex(
            $setup->getIdxName(IndexInterface::TABLE_NAME, [IndexInterface::PRODUCT_ID, IndexInterface::STORE_ID]),
            [IndexInterface::PRODUCT_ID, IndexInterface::STORE_ID],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        );

        for ($i = 1; $i < 10; $i++) {
            $table->addColumn(
                'factor_' . $i . '_score',
                Table::TYPE_DECIMAL,
                '18,8',
                ['unsigned' => false, 'nullable' => true],
                'factor_' . $i
            );

            $table->addColumn(
                'factor_' . $i . '_value',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'factor_' . $i
            );
        }

        $connection->createTable($table);
    }
}
