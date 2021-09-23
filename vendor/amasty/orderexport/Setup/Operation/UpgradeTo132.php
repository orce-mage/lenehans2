<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Amasty\Orderexport\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeTo132
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->changeColumn(
            $setup->getTable('amasty_amorderexport_history'),
            'last_increment_id',
            'last_increment_id',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 50,
                'comment' => 'Last Increment ID of Order'
            ]
        );

        $setup->getConnection()->changeColumn(
            $setup->getTable('amasty_amorderexport_history'),
            'last_invoice_increment_id',
            'last_invoice_increment_id',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 50,
                'comment' => 'Last Increment ID of Invoice'
            ]
        );
    }
}
