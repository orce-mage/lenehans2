<?php

namespace Amasty\Faq\Setup\Operation;

use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\Faq\Api\Data\QuestionInterface;
use Amasty\Faq\Api\Data\CategoryInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeTo110
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $questionTable = $setup->getTable(CreateQuestionTable::TABLE_NAME);
        $setup->getConnection()->addColumn(
            $questionTable,
            QuestionInterface::POSITIVE_RATING,
            [
                'type' => Table::TYPE_INTEGER,
                'default' => 0,
                'nullable' => false,
                'comment' => 'Question Positive Rating'
            ]
        );

        $setup->getConnection()->addColumn(
            $questionTable,
            QuestionInterface::NEGATIVE_RATING,
            [
                'type' => Table::TYPE_INTEGER,
                'default' => 0,
                'nullable' => false,
                'comment' => 'Question Negative Rating'
            ]
        );

        $setup->getConnection()->addColumn(
            $questionTable,
            QuestionInterface::TOTAL_RATING,
            [
                'type' => Table::TYPE_INTEGER,
                'default' => 0,
                'nullable' => false,
                'comment' => 'Total Question Rating'
            ]
        );

        $setup->getConnection()->addIndex(
            $questionTable,
            $setup->getIdxName(
                $questionTable,
                QuestionInterface::TOTAL_RATING
            ),
            QuestionInterface::TOTAL_RATING
        );

        $setup->getConnection()->addColumn(
            $questionTable,
            QuestionInterface::EXCLUDE_SITEMAP,
            [
                'type' => Table::TYPE_BOOLEAN,
                'default' => 0,
                'nullable' => false,
                'comment' => 'Exclude From Sitemap'
            ]
        );

        $setup->getConnection()->addColumn(
            $questionTable,
            QuestionInterface::UPDATED_AT,
            [
                'type' => Table::TYPE_TIMESTAMP,
                'default' => Table::TIMESTAMP_INIT_UPDATE,
                'nullable' => false,
                'comment' => 'Updated at'
            ]
        );

        $setup->getConnection()->addColumn(
            $questionTable,
            QuestionInterface::CANONICAL_URL,
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'length' => 255,
                'comment' => 'Canonical Url'
            ]
        );

        $setup->getConnection()->addColumn(
            $questionTable,
            QuestionInterface::NOINDEX,
            [
                'type' => Table::TYPE_BOOLEAN,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Is Noindex question'
            ]
        );

        $setup->getConnection()->addColumn(
            $questionTable,
            QuestionInterface::NOFOLLOW,
            [
                'type' => Table::TYPE_BOOLEAN,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Is Nofollow question'
            ]
        );

        $setup->getConnection()->addColumn(
            $questionTable,
            QuestionInterface::IS_SHOW_FULL_ANSWER,
            [
                'type' => Table::TYPE_BOOLEAN,
                'nullable' => true,
                'default' => 0,
                'comment' => 'Show Full answer In question list'
            ]
        );

        $categoryTable = $setup->getTable(CreateCategoryTable::TABLE_NAME);
        $setup->getConnection()->addColumn(
            $categoryTable,
            CategoryInterface::EXCLUDE_SITEMAP,
            [
                'type' => Table::TYPE_BOOLEAN,
                'default' => 0,
                'nullable' => false,
                'comment' => 'Exclude From Sitemap'
            ]
        );

        $setup->getConnection()->addColumn(
            $categoryTable,
            CategoryInterface::CREATED_AT,
            [
                'type' => Table::TYPE_TIMESTAMP,
                'default' => Table::TIMESTAMP_INIT,
                'nullable' => false,
                'comment' => 'Created at'
            ]
        );

        $setup->getConnection()->addColumn(
            $categoryTable,
            CategoryInterface::UPDATED_AT,
            [
                'type' => Table::TYPE_TIMESTAMP,
                'default' => Table::TIMESTAMP_INIT_UPDATE,
                'nullable' => false,
                'comment' => 'Updated at'
            ]
        );

        $setup->getConnection()->addColumn(
            $categoryTable,
            CategoryInterface::CANONICAL_URL,
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'length' => 255,
                'comment' => 'Canonical Url'
            ]
        );

        $setup->getConnection()->addColumn(
            $categoryTable,
            CategoryInterface::NOINDEX,
            [
                'type' => Table::TYPE_BOOLEAN,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Is Noindex category'
            ]
        );

        $setup->getConnection()->addColumn(
            $categoryTable,
            CategoryInterface::NOFOLLOW,
            [
                'type' => Table::TYPE_BOOLEAN,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Is Nofollow category'
            ]
        );
    }
}
