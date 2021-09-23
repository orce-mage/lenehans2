<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Setup;

use Amasty\GiftCardAccount\Model\CustomerCard\ResourceModel\CustomerCard;
use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\Account;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Creditmemo\ResourceModel\Creditmemo;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Invoice\ResourceModel\Invoice;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Order\ResourceModel\Order;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\ResourceModel\Quote;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

/**
 * @codeCoverageIgnore
 */
class Uninstall implements UninstallInterface
{
    protected $tablesToDelete = [
        CustomerCard::TABLE_NAME,
        Quote::TABLE_NAME,
        Order::TABLE_NAME,
        Invoice::TABLE_NAME,
        Creditmemo::TABLE_NAME,
        Account::TABLE_NAME
    ];

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        foreach ($this->tablesToDelete as $tableName) {
            $setup->getConnection()->dropTable(
                $setup->getTable($tableName)
            );
        }

        $setup->endSetup();
    }
}
