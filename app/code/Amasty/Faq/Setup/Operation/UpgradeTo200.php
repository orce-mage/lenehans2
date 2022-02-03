<?php

namespace Amasty\Faq\Setup\Operation;

use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\Faq\Api\Data\CategoryInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeTo200
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $categoryTable = $setup->getTable(CreateCategoryTable::TABLE_NAME);

        $setup->getConnection()->addColumn(
            $categoryTable,
            CategoryInterface::DESCRIPTION,
            [
                'type' => Table::TYPE_TEXT,
                'default' => '',
                'nullable' => false,
                'comment' => 'Description'
            ]
        );

        $setup->getConnection()->addColumn(
            $categoryTable,
            CategoryInterface::ICON,
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'default' => null,
                'nullable' => true,
                'comment' => 'Icon Image Name'
            ]
        );
    }
}
