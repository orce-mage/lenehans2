<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


declare(strict_types=1);

namespace Amasty\GiftCardAccount\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Operation\AddRecipientAccountColumn
     */
    private $addRecipientAccountColumn;

    public function __construct(
        Operation\AddRecipientAccountColumn $addRecipientAccountColumn
    ) {
        $this->addRecipientAccountColumn = $addRecipientAccountColumn;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (!$context->getVersion() || version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->addRecipientAccountColumn->execute($setup);
        }

        $setup->endSetup();
    }
}
