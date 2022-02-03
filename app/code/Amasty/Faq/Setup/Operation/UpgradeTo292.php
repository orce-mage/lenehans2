<?php

namespace Amasty\Faq\Setup\Operation;

use Amasty\Faq\Api\Data\CategoryInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeTo292
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $categoryTable = $setup->getTable(CreateCategoryTable::TABLE_NAME);
        $setup->getConnection()->addColumn(
            $categoryTable,
            CategoryInterface::PAGE_TITLE,
            [
                'type' => Table::TYPE_TEXT,
                'default' => null,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Title on Category Page'
            ]
        );
    }
}
