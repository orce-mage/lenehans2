<?php

namespace Amasty\Faq\Setup\Operation;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\Faq\Api\Data\CategoryInterface;
use Amasty\Faq\Api\Data\QuestionInterface;

class CreateQuestionCategoryTable
{
    const TABLE_NAME = 'amasty_faq_question_category';

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
        $questionTable = $setup->getTable(CreateQuestionTable::TABLE_NAME);
        $categoryTable = $setup->getTable(CreateCategoryTable::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty Faq question category relation table'
            )->addColumn(
                QuestionInterface::QUESTION_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true, 'nullable' => false
                ],
                'Question Id'
            )->addColumn(
                CategoryInterface::CATEGORY_ID,
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true, 'nullable' => false
                ],
                'Category Id'
            )->addIndex(
                $setup->getIdxName(
                    $table,
                    [QuestionInterface::QUESTION_ID, CategoryInterface::CATEGORY_ID],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                [QuestionInterface::QUESTION_ID, CategoryInterface::CATEGORY_ID],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )->addForeignKey(
                $setup->getFkName(
                    $table,
                    QuestionInterface::QUESTION_ID,
                    $questionTable,
                    QuestionInterface::QUESTION_ID
                ),
                QuestionInterface::QUESTION_ID,
                $questionTable,
                QuestionInterface::QUESTION_ID,
                Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    $table,
                    CategoryInterface::CATEGORY_ID,
                    $questionTable,
                    CategoryInterface::CATEGORY_ID
                ),
                CategoryInterface::CATEGORY_ID,
                $categoryTable,
                CategoryInterface::CATEGORY_ID,
                Table::ACTION_CASCADE
            );
    }
}
