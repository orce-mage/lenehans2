<?php

namespace Amasty\Faq\Setup\Operation;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\Faq\Api\Data\CategoryInterface;
use Amasty\Faq\Api\Data\TagInterface;
use Amasty\Faq\Api\Data\QuestionInterface;

class CreateQuestionTagTable
{
    const TABLE_NAME = 'amasty_faq_question_tag';

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
        $tagTable = $setup->getTable(CreateTagTable::TABLE_NAME);
        $questionTable = $setup->getTable(CreateQuestionTable::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty Faq question tag relation table'
            )->addColumn(
                QuestionInterface::QUESTION_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true, 'nullable' => false
                ],
                'Question Id'
            )->addColumn(
                TagInterface::TAG_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true, 'nullable' => false
                ],
                'Tag Id'
            )->addIndex(
                $setup->getIdxName(
                    $table,
                    [QuestionInterface::QUESTION_ID, TagInterface::TAG_ID],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                [QuestionInterface::QUESTION_ID, TagInterface::TAG_ID],
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
                    $tagTable,
                    CategoryInterface::CATEGORY_ID
                ),
                TagInterface::TAG_ID,
                $tagTable,
                TagInterface::TAG_ID,
                Table::ACTION_CASCADE
            );
    }
}
