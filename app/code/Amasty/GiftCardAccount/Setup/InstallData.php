<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Setup;

use Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel\Account;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var Operation\UpdateOldData
     */
    private $updateOldData;

    public function __construct(
        Operation\UpdateOldData $updateOldData
    ) {
        $this->updateOldData = $updateOldData;
    }

    /**
     * @inheritDoc
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $accountTable = $setup->getTable(Account::TABLE_NAME);

        if ($setup->getConnection()->tableColumnExists($accountTable, 'order_id')) {
            $this->updateOldData->execute($setup);
        }
    }
}
