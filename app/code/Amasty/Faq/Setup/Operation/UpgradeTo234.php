<?php

namespace Amasty\Faq\Setup\Operation;

use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\Faq\Api\Data\QuestionInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeTo234
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $questionTable = $setup->getTable(CreateQuestionTable::TABLE_NAME);
        $storeTable = $setup->getTable('store');

        $setup->getConnection()->addColumn(
            $questionTable,
            QuestionInterface::ASKED_FROM_STORE,
            [
                'type' => Table::TYPE_SMALLINT,
                'default' => null,
                'nullable' => true,
                'unsigned' => true,
                'comment' => 'Asked From Store ID'
            ]
        );

        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                $questionTable,
                QuestionInterface::ASKED_FROM_STORE,
                $storeTable,
                'store_id'
            ),
            $questionTable,
            QuestionInterface::ASKED_FROM_STORE,
            $storeTable,
            'store_id',
            Table::ACTION_SET_NULL
        );
    }
}
