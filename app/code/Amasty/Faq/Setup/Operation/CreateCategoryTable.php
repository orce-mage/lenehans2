<?php

namespace Amasty\Faq\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\Faq\Api\Data\CategoryInterface;

class CreateCategoryTable
{
    const TABLE_NAME = 'amasty_faq_category';

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

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty Faq category table'
            )->addColumn(
                CategoryInterface::CATEGORY_ID,
                Table::TYPE_SMALLINT,
                null,
                [
                    'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true
                ],
                'Category Id'
            )->addColumn(
                CategoryInterface::TITLE,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false
                ],
                'Title'
            )->addColumn(
                CategoryInterface::POSITION,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false, 'default' => 0
                ],
                'Position'
            )->addColumn(
                CategoryInterface::URL_KEY,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false
                ],
                'Url Key'
            )->addColumn(
                CategoryInterface::STATUS,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false, 'unsigned' => true, 'default' => false
                ],
                'Status'
            )->addColumn(
                CategoryInterface::META_TITLE,
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true
                ],
                'Meta Title'
            )->addColumn(
                CategoryInterface::META_DESCRIPTION,
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true
                ],
                'Meta Description'
            )->addColumn(
                CategoryInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false, 'default' => Table::TIMESTAMP_INIT
                ],
                'Created At'
            )->addColumn(
                CategoryInterface::UPDATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE
                ],
                'Updated At'
            )->addColumn(
                CategoryInterface::EXCLUDE_SITEMAP,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false, 'default' => 0
                ],
                'Exclude From Sitemap'
            )->addColumn(
                CategoryInterface::CANONICAL_URL,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => true
                ],
                'Canonical Url'
            )->addColumn(
                CategoryInterface::NOFOLLOW,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false, 'default' => 0
                ],
                'Is Nofollow category'
            )->addColumn(
                CategoryInterface::NOINDEX,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false, 'default' => 0
                ],
                'Is Noindex category'
            )->addColumn(
                CategoryInterface::DESCRIPTION,
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => false, 'default' => ''
                ],
                'Description'
            )->addColumn(
                CategoryInterface::ICON,
                Table::TYPE_TEXT,
                255,
                [
                    'default' => null,
                    'nullable' => true
                ],
                'Icon'
            );
    }
}
