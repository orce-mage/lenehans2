<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Setup\Operation;

use Amasty\GiftCard\Api\Data\CodeInterface;
use Amasty\GiftCard\Model\Code\ResourceModel\Code;
use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\Account;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class CreateGiftCardAccountTable
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
        $mainTable = $setup->getTable(Account::TABLE_NAME);
        $codeTable = $setup->getTable(Code::TABLE_NAME);
        $websiteTable = $setup->getTable('store_website');

        return $setup->getConnection()
            ->newTable(
                $mainTable
            )->setComment(
                'Amasty Gift Card Account Table'
            )->addColumn(
                GiftCardAccountInterface::ACCOUNT_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Account ID'
            )->addColumn(
                GiftCardAccountInterface::CODE_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Code ID'
            )->addColumn(
                GiftCardAccountInterface::IMAGE_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => true
                ],
                'Image ID'
            )->addColumn(
                GiftCardAccountInterface::ORDER_ITEM_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => true
                ],
                'Order Item ID'
            )->addColumn(
                GiftCardAccountInterface::WEBSITE_ID,
                Table::TYPE_SMALLINT,
                5,
                [
                    'unsigned' => true,
                    'nullable' => true
                ],
                'Website ID'
            )->addColumn(
                GiftCardAccountInterface::STATUS,
                Table::TYPE_SMALLINT,
                1,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Status'
            )->addColumn(
                GiftCardAccountInterface::INITIAL_VALUE,
                Table::TYPE_DECIMAL,
                '12,4',
                [
                    'nullable' => false,
                    'default' => 0.0000
                ],
                'Initial Value'
            )->addColumn(
                GiftCardAccountInterface::CURRENT_VALUE,
                Table::TYPE_DECIMAL,
                '12,4',
                [
                    'nullable' => false,
                    'default' => 0.0000
                ],
                'Current Value'
            )->addColumn(
                GiftCardAccountInterface::EXPIRED_DATE,
                Table::TYPE_DATETIME,
                null,
                [
                    'nullable' => true
                ],
                'Date of Expiration'
            )->addColumn(
                GiftCardAccountInterface::COMMENT,
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true
                ],
                'Comment'
            )->addColumn(
                GiftCardAccountInterface::DATE_DELIVERY,
                Table::TYPE_DATETIME,
                null,
                [
                    'nullable' => true
                ],
                'Delivery Date'
            )->addColumn(
                GiftCardAccountInterface::IS_SENT,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false,
                    'default' => false
                ],
                'Is Email Sent'
            )->addColumn(
                GiftCardAccountInterface::CUSTOMER_CREATED_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => true,
                    'unsigned' => true
                ],
                'Customer Created ID'
            )->addForeignKey(
                $setup->getFkName(
                    $mainTable,
                    GiftCardAccountInterface::WEBSITE_ID,
                    $websiteTable,
                    'website_id'
                ),
                GiftCardAccountInterface::WEBSITE_ID,
                $websiteTable,
                'website_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    $mainTable,
                    GiftCardAccountInterface::CODE_ID,
                    $codeTable,
                    CodeInterface::CODE_ID
                ),
                GiftCardAccountInterface::CODE_ID,
                $codeTable,
                CodeInterface::CODE_ID,
                Table::ACTION_CASCADE
            );
    }
}
