<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Setup\Operation;

use Amasty\Stockstatus\Api\Data\RuleInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpdateRuleTable
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup): void
    {
        $setup->getConnection()->addColumn(
            $setup->getTable(RuleInterface::MAIN_TABLE),
            RuleInterface::ACTIVATE_MSI_QTY_RANGES,
            [
                'type' => Table::TYPE_BOOLEAN,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Flag for msi qty ranges of rule entity.'
            ]
        );
    }
}
