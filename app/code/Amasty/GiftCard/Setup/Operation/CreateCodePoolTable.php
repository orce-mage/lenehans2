<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Setup\Operation;

use Amasty\GiftCard\Api\Data\CodePoolInterface;
use Amasty\GiftCard\Model\CodePool\ResourceModel\CodePool;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 * phpcs:ignoreFile
 */
class CreateCodePoolTable
{
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
    private function createTable(SchemaSetupInterface $setup): Table
    {
        $mainTable = $setup->getTable(CodePool::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $mainTable
            )->setComment(
                'Amasty GiftCard Code Pool Table'
            )->addColumn(
                CodePoolInterface::CODE_POOL_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Code Pool ID'
            )->addColumn(
                CodePoolInterface::TITLE,
                Table::TYPE_TEXT,
                225,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Code Pool Title'
            )->addColumn(
                CodePoolInterface::TEMPLATE,
                Table::TYPE_TEXT,
                225,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Code Pool Template'
            );
    }
}
