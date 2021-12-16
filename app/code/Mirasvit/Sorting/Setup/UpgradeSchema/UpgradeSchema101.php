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

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Mirasvit\Sorting\Api\Data\CriterionInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;

class UpgradeSchema101 implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $setup->getConnection();

        if ($connection->isTableExists($setup->getTable(CriterionInterface::TABLE_NAME))) {
            $connection->dropTable($setup->getTable(CriterionInterface::TABLE_NAME));
        }

        $table = $connection->newTable(
            $setup->getTable(CriterionInterface::TABLE_NAME)
        )->addColumn(
            CriterionInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            CriterionInterface::ID
        )->addColumn(
            CriterionInterface::NAME,
            Table::TYPE_TEXT,
            256,
            ['nullable' => false],
            CriterionInterface::NAME
        )->addColumn(
            CriterionInterface::CODE,
            Table::TYPE_TEXT,
            256,
            ['nullable' => false],
            CriterionInterface::CODE
        )->addColumn(
            CriterionInterface::IS_ACTIVE,
            Table::TYPE_INTEGER,
            1,
            ['nullable' => false, 'default' => 0],
            CriterionInterface::IS_ACTIVE
        )->addColumn(
            CriterionInterface::IS_DEFAULT,
            Table::TYPE_INTEGER,
            1,
            ['nullable' => false, 'default' => 0],
            CriterionInterface::IS_DEFAULT
        )->addColumn(
            CriterionInterface::IS_SEARCH_DEFAULT,
            Table::TYPE_INTEGER,
            1,
            ['nullable' => false, 'default' => 0],
            CriterionInterface::IS_SEARCH_DEFAULT
        )->addColumn(
            CriterionInterface::POSITION,
            Table::TYPE_INTEGER,
            11,
            ['nullable' => false, 'default' => 1],
            CriterionInterface::POSITION
        )->addColumn(
            CriterionInterface::CONDITIONS_SERIALIZED,
            Table::TYPE_TEXT,
            66000,
            ['nullable' => true],
            CriterionInterface::CONDITIONS_SERIALIZED
        );
        $connection->createTable($table);

        if ($connection->isTableExists($setup->getTable(RankingFactorInterface::TABLE_NAME))) {
            $connection->dropTable($setup->getTable(RankingFactorInterface::TABLE_NAME));
        }

        $table = $connection->newTable(
            $setup->getTable(RankingFactorInterface::TABLE_NAME)
        )->addColumn(
            RankingFactorInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            RankingFactorInterface::ID
        )->addColumn(
            RankingFactorInterface::NAME,
            Table::TYPE_TEXT,
            256,
            ['nullable' => false],
            RankingFactorInterface::NAME
        )->addColumn(
            RankingFactorInterface::TYPE,
            Table::TYPE_TEXT,
            256,
            ['nullable' => false],
            RankingFactorInterface::TYPE
        )->addColumn(
            RankingFactorInterface::IS_ACTIVE,
            Table::TYPE_INTEGER,
            1,
            ['nullable' => false, 'default' => 0],
            RankingFactorInterface::IS_ACTIVE
        )->addColumn(
            RankingFactorInterface::IS_GLOBAL,
            Table::TYPE_INTEGER,
            1,
            ['nullable' => false, 'default' => 0],
            RankingFactorInterface::IS_GLOBAL
        )->addColumn(
            RankingFactorInterface::WEIGHT,
            Table::TYPE_INTEGER,
            11,
            ['nullable' => false, 'default' => 0],
            RankingFactorInterface::WEIGHT
        )->addColumn(
            RankingFactorInterface::CONFIG_SERIALIZED,
            Table::TYPE_TEXT,
            66000,
            ['nullable' => true],
            RankingFactorInterface::CONFIG_SERIALIZED
        );
        $connection->createTable($table);
    }
}
