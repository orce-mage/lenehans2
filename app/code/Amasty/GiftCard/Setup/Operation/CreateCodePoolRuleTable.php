<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Setup\Operation;

use Amasty\GiftCard\Api\Data\CodePoolInterface;
use Amasty\GiftCard\Api\Data\CodePoolRuleInterface;
use Amasty\GiftCard\Model\CodePool\ResourceModel\CodePool;
use Amasty\GiftCard\Model\CodePool\ResourceModel\CodePoolRule;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 * phpcs:ignoreFile
 */
class CreateCodePoolRuleTable
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
        $mainTable = $setup->getTable(CodePoolRule::TABLE_NAME);
        $codePoolTable = $setup->getTable(CodePool::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $mainTable
            )->setComment(
                'Amasty GiftCard Code Pool Rule Table'
            )->addColumn(
                CodePoolRuleInterface::RULE_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Code Pool Rule ID'
            )->addColumn(
                CodePoolRuleInterface::CODE_POOL_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ],
                'Code Pool ID'
            )->addColumn(
                CodePoolRuleInterface::CONDITIONS_SERIALIZED,
                Table::TYPE_TEXT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Serialized Conditions'
            )->addForeignKey(
                $setup->getFkName(
                    $mainTable,
                    CodePoolRuleInterface::CODE_POOL_ID,
                    $codePoolTable,
                    CodePoolInterface::CODE_POOL_ID
                ),
                CodePoolRuleInterface::CODE_POOL_ID,
                $codePoolTable,
                CodePoolInterface::CODE_POOL_ID,
                Table::ACTION_CASCADE
            );
    }
}
