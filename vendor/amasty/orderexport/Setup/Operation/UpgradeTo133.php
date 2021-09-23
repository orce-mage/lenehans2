<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Amasty\Orderexport\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeTo133
{
    /**
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $installer = $setup;
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amorderexport_profiles'),
            'run_by_cron',
            [
                'type' => Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Run Profile by Cron'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_amorderexport_profiles'),
            'cron_schedule',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => false,
                'comment' => 'Run Profile Cron Schedule'
            ]
        );

        $queueTable = $installer->getConnection()
            ->newTable($installer->getTable('amasty_amorderexport_cron_queue'))
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                NULL,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Queue Entity ID'
            )
            ->addColumn(
                'profile_id',
                Table::TYPE_INTEGER,
                255,
                ['default' => '0', 'nullable' => false],
                'Profile ID'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Queue Entity Creation Time'
            )
            ->addColumn(
                'scheduled_at',
                Table::TYPE_INTEGER,
                255,
                ['nullable' => false, 'default' => '0'],
                'Queue Entity Creation Time'
            );
        $installer->getConnection()->createTable($queueTable);
    }
}
