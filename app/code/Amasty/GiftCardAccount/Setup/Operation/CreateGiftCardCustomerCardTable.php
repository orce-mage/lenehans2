<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Setup\Operation;

use Amasty\GiftCardAccount\Api\Data\CustomerCardInterface;
use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Model\CustomerCard\ResourceModel\CustomerCard;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\Account;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class CreateGiftCardCustomerCardTable
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
        $mainTable = $setup->getTable(CustomerCard::TABLE_NAME);
        $accountTable = $setup->getTable(Account::TABLE_NAME);
        $customerTable = $setup->getTable('customer_entity');

        return $setup->getConnection()
            ->newTable(
                $mainTable
            )->setComment(
                'Amasty Gift Card Customer Card Table'
            )->addColumn(
                CustomerCardInterface::CUSTOMER_CARD_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Customer Card ID'
            )->addColumn(
                CustomerCardInterface::ACCOUNT_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Account ID'
            )->addColumn(
                CustomerCardInterface::CUSTOMER_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Customer ID'
            )->addForeignKey(
                $setup->getFkName(
                    $mainTable,
                    CustomerCardInterface::ACCOUNT_ID,
                    $accountTable,
                    GiftCardAccountInterface::ACCOUNT_ID
                ),
                CustomerCardInterface::ACCOUNT_ID,
                $accountTable,
                GiftCardAccountInterface::ACCOUNT_ID,
                Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    $mainTable,
                    CustomerCardInterface::CUSTOMER_ID,
                    $customerTable,
                    'entity_id'
                ),
                CustomerCardInterface::CUSTOMER_ID,
                $customerTable,
                'entity_id',
                Table::ACTION_CASCADE
            );
    }
}
