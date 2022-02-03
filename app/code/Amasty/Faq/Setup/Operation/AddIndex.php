<?php

namespace Amasty\Faq\Setup\Operation;

use Amasty\Faq\Api\Data\QuestionInterface;
use Amasty\Faq\Api\Data\CategoryInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class AddIndex
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $installer = $setup;
        $connection = $installer->getConnection();
        $connection->addIndex(
            $installer->getTable(CreateQuestionTable::TABLE_NAME),
            $setup->getIdxName(
                $installer->getTable(CreateQuestionTable::TABLE_NAME),
                [QuestionInterface::SHORT_ANSWER],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            ),
            [QuestionInterface::SHORT_ANSWER],
            AdapterInterface::INDEX_TYPE_FULLTEXT
        );

        $connection->addIndex(
            $installer->getTable(CreateCategoryTable::TABLE_NAME),
            $setup->getIdxName(
                $installer->getTable(CreateCategoryTable::TABLE_NAME),
                [CategoryInterface::TITLE, CategoryInterface::DESCRIPTION],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            ),
            [CategoryInterface::TITLE, CategoryInterface::DESCRIPTION],
            AdapterInterface::INDEX_TYPE_FULLTEXT
        );
    }
}
