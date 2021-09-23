<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Setup\Operation;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Model\CustomerCard\ResourceModel\CustomerCard;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\Account;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpdateOldSchema
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $this->updateAccountsTable($setup);
        $this->updateCustomerCardTable($setup);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function updateAccountsTable(SchemaSetupInterface $setup)
    {
        $oldAccountTable = $setup->getTable('amasty_amgiftcard_account');
        $newAccountTable = $setup->getTable(Account::TABLE_NAME);
        $websiteTable = $setup->getTable('store_website');

        if (!$setup->tableExists($oldAccountTable)) {
            return;
        }
        $setup->getConnection()->renameTable(
            $oldAccountTable,
            $newAccountTable
        );
        $setup->getConnection()->dropColumn(
            $newAccountTable,
            'product_id'
        );
        $setup->getConnection()->dropColumn(
            $newAccountTable,
            'image_path'
        );
        $setup->getConnection()->dropColumn(
            $newAccountTable,
            'sender_name'
        );
        $setup->getConnection()->dropColumn(
            $newAccountTable,
            'sender_email'
        );
        $setup->getConnection()->dropColumn(
            $newAccountTable,
            'sender_message'
        );
        $setup->getConnection()->dropColumn(
            $newAccountTable,
            'recipient_name'
        );
        $setup->getConnection()->dropColumn(
            $newAccountTable,
            'recipient_email'
        );
        $setup->getConnection()->changeColumn(
            $newAccountTable,
            'status_id',
            GiftCardAccountInterface::STATUS,
            [
                'type' => Table::TYPE_SMALLINT,
                'comment' => 'Status',
                'unsigned' => true,
                'nullable' => false
            ]
        );
        $setup->getConnection()->modifyColumn(
            $newAccountTable,
            GiftCardAccountInterface::IS_SENT,
            [
                'type' => Table::TYPE_SMALLINT,
                'comment' => 'Is Email Sent',
                'nullable' => false,
                'default' => false
            ]
        );
        $setup->getConnection()->modifyColumn(
            $newAccountTable,
            GiftCardAccountInterface::WEBSITE_ID,
            [
                'type' => Table::TYPE_SMALLINT,
                'comment' => 'Website ID',
                'length' => 5,
                'nullable' => false,
                'unsigned' => true
            ]
        );
        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                $newAccountTable,
                GiftCardAccountInterface::WEBSITE_ID,
                $websiteTable,
                'website_id'
            ),
            $newAccountTable,
            GiftCardAccountInterface::WEBSITE_ID,
            $websiteTable,
            'website_id',
            Table::ACTION_CASCADE
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function updateCustomerCardTable(SchemaSetupInterface $setup)
    {
        $oldCardTable = $setup->getTable('amasty_amgiftcard_customer_card');
        $newCardTable = $setup->getTable(CustomerCard::TABLE_NAME);
        $setup->getConnection()->renameTable(
            $oldCardTable,
            $newCardTable
        );
        $setup->getConnection()->modifyColumn(
            $newCardTable,
            \Amasty\GiftCardAccount\Api\Data\CustomerCardInterface::ACCOUNT_ID,
            [
                'type' => Table::TYPE_INTEGER,
                'comment' => 'Account ID',
                'unsigned' => true,
                'nullable' => false
            ]
        );
    }
}
