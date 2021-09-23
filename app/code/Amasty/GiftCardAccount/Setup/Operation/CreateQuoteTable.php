<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Setup\Operation;

use Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterface;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\ResourceModel\Quote;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class CreateQuoteTable
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
    private function createTable(SchemaSetupInterface $setup)
    {
        $mainTable = $setup->getTable(Quote::TABLE_NAME);
        $quoteTable = $setup->getTable('quote');

        return $setup->getConnection()
            ->newTable(
                $mainTable
            )->setComment(
                'Amasty Gift Card Account Quote Table'
            )->addColumn(
                GiftCardQuoteInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Entity ID'
            )->addColumn(
                GiftCardQuoteInterface::QUOTE_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Quote ID'
            )->addColumn(
                GiftCardQuoteInterface::GIFT_CARDS,
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true
                ],
                'Gift Cards Applied To Quote'
            )->addColumn(
                GiftCardQuoteInterface::GIFT_AMOUNT,
                Table::TYPE_DECIMAL,
                '12,4',
                [
                    'nullable' => false,
                    'default' => '0.0000'
                ],
                'Gift Cards Amount'
            )->addColumn(
                GiftCardQuoteInterface::BASE_GIFT_AMOUNT,
                Table::TYPE_DECIMAL,
                '12,4',
                [
                    'nullable' => false,
                    'default' => '0.0000'
                ],
                'Base Gift Cards Amount'
            )->addColumn(
                GiftCardQuoteInterface::GIFT_AMOUNT_USED,
                Table::TYPE_DECIMAL,
                '12,4',
                [
                    'nullable' => false,
                    'default' => '0.0000'
                ],
                'Used Gift Cards Amount'
            )->addColumn(
                GiftCardQuoteInterface::BASE_GIFT_AMOUNT_USED,
                Table::TYPE_DECIMAL,
                '12,4',
                [
                    'nullable' => false,
                    'default' => '0.0000'
                ],
                'Base Used Gift Cards Amount'
            )->addForeignKey(
                $setup->getFkName(
                    $mainTable,
                    GiftCardQuoteInterface::QUOTE_ID,
                    $quoteTable,
                    'entity_id'
                ),
                GiftCardQuoteInterface::QUOTE_ID,
                $quoteTable,
                'entity_id',
                Table::ACTION_CASCADE
            );
    }
}
