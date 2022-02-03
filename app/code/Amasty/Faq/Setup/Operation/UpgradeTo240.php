<?php

namespace Amasty\Faq\Setup\Operation;

use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\Faq\Api\Data\VisitStatInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeTo240
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $viewStatTable = $setup->getTable(CreateViewStatTables::TABLE_NAME);
        $storeTable = $setup->getTable('store');

        $setup->getConnection()
            ->addColumn(
                $viewStatTable,
                VisitStatInterface::STORE_IDS,
                [
                    'type' => Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'unsigned' => true,
                    'comment' => 'Customer store'
                ]
            );

        $setup->getConnection()
            ->addForeignKey(
                $setup->getFkName(
                    $viewStatTable,
                    VisitStatInterface::STORE_IDS,
                    $storeTable,
                    'store_id'
                ),
                $viewStatTable,
                VisitStatInterface::STORE_IDS,
                $storeTable,
                'store_id',
                Table::ACTION_CASCADE
            );

        $setup->getConnection()->addColumn(
            $viewStatTable,
            VisitStatInterface::COUNT_OF_RESULT,
            [
                'type' => Table::TYPE_INTEGER,
                'nullable' => false,
                'comment' => 'Count of output results'
            ]
        );

        $setup->getConnection()->truncateTable($viewStatTable);
    }
}
