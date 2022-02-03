<?php

declare(strict_types=1);

namespace Amasty\Faq\Setup\Operation;

use Amasty\Faq\Api\Data\CategoryInterface;
use Magento\Customer\Model\Group;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateFaqCategoryCustomerGroupTable
{
    const TABLE_NAME = 'amasty_faq_category_customer_groups';

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
        $customerGroupTable = $setup->getTable(Group::ENTITY);
        $faqCategoryTable = $setup->getTable(CreateCategoryTable::TABLE_NAME);

        return $setup->getConnection()
            ->newTable($table)
            ->setComment('Amasty Faq category customer groups relation table')
            ->addColumn(
                CategoryInterface::CATEGORY_ID,
                Table::TYPE_SMALLINT,
                5,
                [
                    'unsigned' => true,
                    'nullable' => true
                ],
                'Category Id'
            )->addColumn(
                'customer_group_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Customer group id'
            )->addIndex(
                $setup->getIdxName(
                    $table,
                    [CategoryInterface::CATEGORY_ID, 'customer_group_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                [CategoryInterface::CATEGORY_ID, 'customer_group_id'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )->addForeignKey(
                $setup->getFkName(
                    $table,
                    CategoryInterface::CATEGORY_ID,
                    $faqCategoryTable,
                    CategoryInterface::CATEGORY_ID
                ),
                CategoryInterface::CATEGORY_ID,
                $faqCategoryTable,
                CategoryInterface::CATEGORY_ID,
                Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    $table,
                    'customer_group_id',
                    $customerGroupTable,
                    'customer_group_id'
                ),
                'customer_group_id',
                $customerGroupTable,
                'customer_group_id',
                Table::ACTION_CASCADE
            );
    }
}
