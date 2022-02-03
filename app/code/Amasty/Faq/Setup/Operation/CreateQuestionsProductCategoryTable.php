<?php

declare(strict_types=1);

namespace Amasty\Faq\Setup\Operation;

use Amasty\Faq\Api\Data\QuestionInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateQuestionsProductCategoryTable
{
    const TABLE_NAME = 'amasty_faq_question_product_category';

    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
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
     * @throws \Zend_Db_Exception
     */
    private function createTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable(self::TABLE_NAME);
        $questionTable = $setup->getTable(CreateQuestionTable::TABLE_NAME);
        $categoryFieldName = 'category_id';

        return $setup->getConnection()
            ->newTable($table)
            ->setComment('Amasty Faq question product categories relation table')
            ->addColumn(
                QuestionInterface::QUESTION_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true
                ],
                'Question Id'
            )->addColumn(
                $categoryFieldName,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true
                ],
                'Category Id'
            )->addIndex(
                $setup->getIdxName(
                    $table,
                    [QuestionInterface::QUESTION_ID, $categoryFieldName],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                [QuestionInterface::QUESTION_ID, $categoryFieldName],
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
            );
    }
}
