<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var Operation\CreateGiftCardAccountTable
     */
    private $createGiftCardAccountTable;

    /**
     * @var Operation\CreateGiftCardCustomerCardTable
     */
    private $createGiftCardCustomerCardTable;

    /**
     * @var Operation\CreateQuoteTable
     */
    private $createQuoteTable;

    /**
     * @var Operation\CreateOrderTable
     */
    private $createOrderTable;

    /**
     * @var Operation\CreateInvoiceTable
     */
    private $createInvoiceTable;

    /**
     * @var Operation\CreateCreditmemoTable
     */
    private $createCreditmemoTable;

    /**
     * @var Operation\UpdateOldSchema
     */
    private $updateOldSchema;

    public function __construct(
        Operation\CreateGiftCardAccountTable $createGiftCardAccountTable,
        Operation\CreateGiftCardCustomerCardTable $createGiftCardCustomerCardTable,
        Operation\CreateQuoteTable $createQuoteTable,
        Operation\CreateOrderTable $createOrderTable,
        Operation\CreateInvoiceTable $createInvoiceTable,
        Operation\CreateCreditmemoTable $createCreditmemoTable,
        Operation\UpdateOldSchema $updateOldSchema
    ) {
        $this->createGiftCardAccountTable = $createGiftCardAccountTable;
        $this->createGiftCardCustomerCardTable = $createGiftCardCustomerCardTable;
        $this->createQuoteTable = $createQuoteTable;
        $this->createOrderTable = $createOrderTable;
        $this->createInvoiceTable = $createInvoiceTable;
        $this->createCreditmemoTable = $createCreditmemoTable;
        $this->updateOldSchema = $updateOldSchema;
    }

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->createOrderTable->execute($setup);
        $this->createInvoiceTable->execute($setup);
        $this->createCreditmemoTable->execute($setup);
        $this->createQuoteTable->execute($setup);

        if (!$setup->tableExists($setup->getTable('amasty_amgiftcard_account'))) {
            $this->createGiftCardAccountTable->execute($setup);
            $this->createGiftCardCustomerCardTable->execute($setup);
        } else {
            $this->updateOldSchema->execute($setup);
        }

        $setup->endSetup();
    }
}
