<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Setup\Operation;

use Amasty\GiftCard\Api\Data\CodeInterface;
use Amasty\GiftCard\Model\Code\ResourceModel\Code;
use Amasty\GiftCard\Model\OptionSource\Status;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 * phpcs:ignoreFile
 */
class CreateCodeTable
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
        $mainTable = $setup->getTable(Code::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $mainTable
            )->setComment(
                'Amasty GiftCard Code Table'
            )->addColumn(
                CodeInterface::CODE_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Code ID'
            )->addColumn(
                CodeInterface::CODE,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false,
                ],
                'Code'
            )->addColumn(
                CodeInterface::CODE_POOL_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ],
                'Code Pool ID'
            )->addColumn(
                CodeInterface::STATUS,
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false,
                    'default' => Status::AVAILABLE
                ],
                'Code Status'
            );
    }
}
