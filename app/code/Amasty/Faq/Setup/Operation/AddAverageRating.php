<?php

declare(strict_types=1);

namespace Amasty\Faq\Setup\Operation;

use Amasty\Faq\Api\Data\QuestionInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class AddAverageRating
{
    public function execute(SchemaSetupInterface $setup)
    {
        $questionTable = $setup->getTable(CreateQuestionTable::TABLE_NAME);
        $setup->getConnection()->addColumn(
            $questionTable,
            QuestionInterface::AVERAGE_RATING,
            [
                'type' => Table::TYPE_DECIMAL,
                'default' => 0.0000,
                'length' => '10,4',
                'nullable' => false,
                'unsigned' => true,
                'comment' => 'Average Question Rating'
            ]
        );
        $setup->getConnection()->addColumn(
            $questionTable,
            QuestionInterface::AVERAGE_TOTAL,
            [
                'type' => Table::TYPE_INTEGER,
                'default' => 0,
                'nullable' => false,
                'comment' => 'Total Average Ratings Count'
            ]
        );
    }
}
