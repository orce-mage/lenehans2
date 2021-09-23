<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Amasty\Orderexport\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeTo130
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amorderexport_profiles'),
            'static_fields',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => false,
                'comment' => 'Static fields'
            ]
        );
    }
}
