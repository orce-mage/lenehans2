<?php

namespace Amasty\Faq\Setup\Operation;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\Faq\Api\Data\QuestionInterface;

class CreateQuestionTable
{
    const TABLE_NAME = 'amasty_faq_question';

    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->createTable(
            $this->createQuestionTable($setup)
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @return Table
     */
    private function createQuestionTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable(self::TABLE_NAME);
        $storeTable = $setup->getTable('store');
        
        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty Faq question table'
            )->addColumn(
                QuestionInterface::QUESTION_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true
                ],
                'Question Id'
            )->addColumn(
                QuestionInterface::TITLE,
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => false
                ],
                'Question text'
            )->addColumn(
                QuestionInterface::SHORT_ANSWER,
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true
                ],
                'Short answer'
            )->addColumn(
                QuestionInterface::ANSWER,
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true
                ],
                'Answer'
            )->addColumn(
                QuestionInterface::VISIBILITY,
                Table::TYPE_SMALLINT,
                null,
                [
                    'default' => 0, 'unsigned' => true, 'nullable' => false
                ],
                'Visibility'
            )->addColumn(
                QuestionInterface::STATUS,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'default' => false, 'unsigned' => true, 'nullable' => false
                ],
                'Status'
            )->addColumn(
                QuestionInterface::NAME,
                Table::TYPE_TEXT,
                255,
                [
                    'default' => null, 'nullable' => true
                ],
                'Name'
            )->addColumn(
                QuestionInterface::EMAIL,
                Table::TYPE_TEXT,
                255,
                [
                    'default' => null, 'nullable' => true
                ],
                'Email'
            )->addColumn(
                QuestionInterface::POSITION,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false, 'default' => 0
                ],
                'Position'
            )->addColumn(
                QuestionInterface::URL_KEY,
                Table::TYPE_TEXT,
                255,
                [
                    'default' => null
                ],
                'Url Key'
            )->addColumn(
                QuestionInterface::POSITIVE_RATING,
                Table::TYPE_INTEGER,
                null,
                [
                    'default' => 0, 'nullable' => false
                ],
                'Question Positive Rating'
            )->addColumn(
                QuestionInterface::NEGATIVE_RATING,
                Table::TYPE_INTEGER,
                null,
                [
                    'default' => 0, 'nullable' => false
                ],
                'Question Negative Rating'
            )->addColumn(
                QuestionInterface::TOTAL_RATING,
                Table::TYPE_INTEGER,
                null,
                [
                    'default' => 0, 'nullable' => false
                ],
                'Total Question Rating'
            )->addColumn(
                QuestionInterface::META_TITLE,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null
                ],
                'Meta Title'
            )->addColumn(
                QuestionInterface::META_DESCRIPTION,
                Table::TYPE_TEXT,
                null,
                [
                    'default' => null
                ],
                'Meta Description'
            )->addColumn(
                QuestionInterface::META_ROBOTS,
                Table::TYPE_TEXT,
                255,
                [
                    'default' => null
                ],
                'Meta Robots'
            )->addColumn(
                QuestionInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false, 'default' => Table::TIMESTAMP_INIT
                ],
                'Created At'
            )->addColumn(
                QuestionInterface::UPDATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE
                ],
                'Updated At'
            )->addColumn(
                QuestionInterface::EXCLUDE_SITEMAP,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false, 'default' => 0
                ],
                'Exclude From Sitemap'
            )->addColumn(
                QuestionInterface::CANONICAL_URL,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => true
                ],
                'Canonical Url'
            )->addColumn(
                QuestionInterface::NOFOLLOW,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false, 'default' => 0
                ],
                'Is Nofollow question'
            )->addColumn(
                QuestionInterface::NOINDEX,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false, 'default' => 0
                ],
                'Is Noindex question'
            )->addColumn(
                QuestionInterface::IS_SHOW_FULL_ANSWER,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => true, 'default' => 0
                ],
                'Show Full answer In question list'
            )->addColumn(
                QuestionInterface::ASKED_FROM_STORE,
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => true, 'default' => null, 'unsigned' => true
                ],
                'Asked From Store ID'
            )->addIndex(
                $setup->getIdxName(
                    $table,
                    [QuestionInterface::TITLE, QuestionInterface::ANSWER],
                    AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                [QuestionInterface::TITLE, QuestionInterface::ANSWER],
                ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
            )->addIndex(
                $setup->getIdxName(
                    $table,
                    QuestionInterface::TOTAL_RATING
                ),
                QuestionInterface::TOTAL_RATING
            )->addForeignKey(
                $setup->getFkName(
                    $table,
                    QuestionInterface::ASKED_FROM_STORE,
                    $storeTable,
                    'store_id'
                ),
                QuestionInterface::ASKED_FROM_STORE,
                $storeTable,
                'store_id',
                Table::ACTION_SET_NULL
            );
    }
}
