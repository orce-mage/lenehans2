<?php

namespace Amasty\Faq\Setup\Operation;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\Faq\Api\Data\CategoryInterface;

class CreateCategoryStoreTable
{
    const TABLE_NAME = 'amasty_faq_category_store';

    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->createTable(
            $this->createTable($setup)
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @return Table
     */
    private function createTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable(self::TABLE_NAME);
        $storeTable = $setup->getTable('store');
        $categoryTable = $setup->getTable(CreateCategoryTable::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty Faq category store relation table'
            )->addColumn(
                CategoryInterface::CATEGORY_ID,
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true, 'nullable' => false
                ],
                'Category Id'
            )->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true, 'nullable' => false
                ],
                'Store Id'
            )->addIndex(
                $setup->getIdxName(
                    $table,
                    [CategoryInterface::CATEGORY_ID, 'store_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                [CategoryInterface::CATEGORY_ID, 'store_id'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )->addForeignKey(
                $setup->getFkName(
                    $table,
                    CategoryInterface::CATEGORY_ID,
                    $categoryTable,
                    CategoryInterface::CATEGORY_ID
                ),
                CategoryInterface::CATEGORY_ID,
                $categoryTable,
                CategoryInterface::CATEGORY_ID,
                Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    $table,
                    'store_id',
                    $storeTable,
                    'store_id'
                ),
                'store_id',
                $storeTable,
                'store_id',
                Table::ACTION_CASCADE
            );
    }
}
