<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Setup\Operation;

use Amasty\GiftCardAccount\Api\Data\GiftCardInvoiceInterface;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Invoice\ResourceModel\Invoice;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class CreateInvoiceTable
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
        $mainTable = $setup->getTable(Invoice::TABLE_NAME);
        $invoiceTable = $setup->getTable('sales_invoice');

        return $setup->getConnection()
            ->newTable(
                $mainTable
            )->setComment(
                'Amasty Gift Card Account Invoice Table'
            )->addColumn(
                GiftCardInvoiceInterface::ENTITY_ID,
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
                GiftCardInvoiceInterface::INVOICE_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Invoice ID'
            )->addColumn(
                GiftCardInvoiceInterface::GIFT_AMOUNT,
                Table::TYPE_DECIMAL,
                '12,4',
                [
                    'nullable' => false,
                    'default' => '0.0000'
                ],
                'Gift Cards Amount'
            )->addColumn(
                GiftCardInvoiceInterface::BASE_GIFT_AMOUNT,
                Table::TYPE_DECIMAL,
                '12,4',
                [
                    'nullable' => false,
                    'default' => '0.0000'
                ],
                'Base Gift Cards Amount'
            )->addForeignKey(
                $setup->getFkName(
                    $mainTable,
                    GiftCardInvoiceInterface::INVOICE_ID,
                    $invoiceTable,
                    'entity_id'
                ),
                GiftCardInvoiceInterface::INVOICE_ID,
                $invoiceTable,
                'entity_id',
                Table::ACTION_CASCADE
            );
    }
}
