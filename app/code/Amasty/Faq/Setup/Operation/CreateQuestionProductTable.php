<?php

namespace Amasty\Faq\Setup\Operation;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\Faq\Api\Data\QuestionInterface;

class CreateQuestionProductTable
{
    const TABLE_NAME = 'amasty_faq_question_product';

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
        $productTable = $setup->getTable('catalog_product_entity');

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty Faq question product relation table'
            )->addColumn(
                QuestionInterface::QUESTION_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true, 'nullable' => false
                ],
                'Question Id'
            )->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true, 'nullable' => false
                ],
                'Product Id'
            )->addIndex(
                $setup->getIdxName(
                    $table,
                    [QuestionInterface::QUESTION_ID, 'product_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                [QuestionInterface::QUESTION_ID, 'product_id'],
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
                    'product_id',
                    $productTable,
                    'entity_id'
                ),
                'product_id',
                $productTable,
                'entity_id',
                Table::ACTION_CASCADE
            );
    }
}
