<?php

namespace Amasty\Faq\Setup\Operation;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\Faq\Api\Data\TagInterface;

class CreateTagTable
{
    const TABLE_NAME = 'amasty_faq_tag';

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
                'Amasty Faq tag table'
            )->addColumn(
                TagInterface::TAG_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true
                ],
                'Tag Id'
            )->addColumn(
                TagInterface::TITLE,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false
                ],
                'Title'
            )->addIndex(
                $setup->getIdxName($table, TagInterface::TITLE, AdapterInterface::INDEX_TYPE_UNIQUE),
                TagInterface::TITLE,
                AdapterInterface::INDEX_TYPE_UNIQUE
            );
    }
}
