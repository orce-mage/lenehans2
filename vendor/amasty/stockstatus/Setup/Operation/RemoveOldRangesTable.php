<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Setup\Operation;

use Magento\Framework\Setup\ModuleDataSetupInterface;

class RemoveOldRangesTable
{
    public function execute(ModuleDataSetupInterface $setup): void
    {
        $setup->getConnection()->dropTable($setup->getTable('amasty_stockstatus_quantityranges'));
    }
}
