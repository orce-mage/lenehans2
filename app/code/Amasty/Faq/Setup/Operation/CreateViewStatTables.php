<?php

namespace Amasty\Faq\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\Faq\Api\Data\VisitStatInterface;

class CreateViewStatTables
{
    const TABLE_NAME = 'amasty_faq_view_stat';

    /**
     * @var AddTriggers
     */
    private $addTriggers;

    public function __construct(
        AddTriggers $addTriggers
    ) {
        $this->addTriggers = $addTriggers;
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->createTable(
            $this->createTable($setup)
        );
        $this->addStatFields($setup);
        $this->addTriggers->addVisitStatTrigger($setup);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return Table
     */
    public function createTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable(self::TABLE_NAME);
        $storeTable = $setup->getTable('store');

        return $setup->getConnection()->newTable(
            $table
        )->setComment(
            'Amasty Faq visit statistic table'
        )->addColumn(
            VisitStatInterface::VISIT_ID,
            Table::TYPE_INTEGER,
            null,
            [
                'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true
            ],
            'Visit Id'
        )->addColumn(
            VisitStatInterface::CATEGORY_ID,
            Table::TYPE_SMALLINT,
            null,
            [
                'unsigned' => true, 'nullable' => true
            ],
            'Category Id'
        )->addColumn(
            VisitStatInterface::QUESTION_ID,
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true, 'nullable' => true
            ],
            'Question Id'
        )->addColumn(
            VisitStatInterface::CUSTOMER_ID,
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true, 'nullable' => true
            ],
            'Customer Id'
        )->addColumn(
            VisitStatInterface::VISITOR_ID,
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true, 'nullable' => true
            ],
            'Visitor Id'
        )->addColumn(
            VisitStatInterface::SEARCH_QUERY,
            Table::TYPE_TEXT,
            255,
            [
                'nullable' => true
            ],
            'Search Query'
        )->addColumn(
            VisitStatInterface::DATETIME,
            Table::TYPE_TIMESTAMP,
            null,
            [
                'default' => Table::TIMESTAMP_INIT
            ],
            'Visit Date and Time'
        )->addColumn(
            VisitStatInterface::PAGE_URL,
            Table::TYPE_TEXT,
            255,
            [
                'nullable' => true
            ],
            'Page Url'
        )->addColumn(
            VisitStatInterface::STORE_IDS,
            Table::TYPE_SMALLINT,
            null,
            [
                'nullable' => false, 'unsigned' => true
            ],
            'Customer store'
        )->addColumn(
            VisitStatInterface::COUNT_OF_RESULT,
            Table::TYPE_INTEGER,
            null,
            [
                'nullable' => false
            ],
            'Count of output results'
        )->addForeignKey(
            $setup->getFkName(
                $table,
                VisitStatInterface::STORE_IDS,
                $storeTable,
                'store_id'
            ),
            VisitStatInterface::STORE_IDS,
            $storeTable,
            'store_id',
            Table::ACTION_CASCADE
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    public function addStatFields(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $column = [
            'type' => Table::TYPE_INTEGER,
            'nullable' => false,
            'comment' => 'Visit count',
            'default' => '0'
        ];
        $connection->addColumn(
            $setup->getTable(\Amasty\Faq\Setup\Operation\CreateQuestionTable::TABLE_NAME),
            \Amasty\Faq\Api\Data\QuestionInterface::VISIT_COUNT,
            $column
        );
        $connection->addColumn(
            $setup->getTable(\Amasty\Faq\Setup\Operation\CreateCategoryTable::TABLE_NAME),
            \Amasty\Faq\Api\Data\CategoryInterface::VISIT_COUNT,
            $column
        );
    }
}
