<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Geoip
 */


declare(strict_types=1);

namespace Amasty\Geoip\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     *
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if ($context->getVersion() && version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->addIndexes($setup);
        }

        if ($context->getVersion() && version_compare($context->getVersion(), '1.1.1', '<')) {
            $this->addCommonKey($setup);
        }

        if ($context->getVersion() && version_compare($context->getVersion(), '1.2.0', '<')) {
            $this->addRegion($setup);
        }

        if ($context->getVersion() && version_compare($context->getVersion(), '1.3.2', '<')) {
            $this->addIndexBlock($setup);
        }

        if ($context->getVersion() && version_compare($context->getVersion(), '1.4.0', '<')) {
            $this->addIpV6Table($setup);
        }

        if ($context->getVersion() && version_compare($context->getVersion(), '1.5.1', '<')) {
            $this->changeEngine($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addIndexes(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addIndex(
            $setup->getTable('amasty_geoip_block'),
            $setup->getConnection()->getIndexName(
                $setup->getTable('amasty_geoip_block'),
                'start_ip_num',
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            'start_ip_num'
        );

        $setup->getConnection()->addIndex(
            $setup->getTable('amasty_geoip_block'),
            $setup->getConnection()->getIndexName(
                $setup->getTable('amasty_geoip_block'),
                'end_ip_num',
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            'end_ip_num'
        );

        $setup->getConnection()->addIndex(
            $setup->getTable('amasty_geoip_location'),
            $setup->getConnection()->getIndexName(
                $setup->getTable('amasty_geoip_location'),
                'geoip_loc_id',
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            'geoip_loc_id'
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addCommonKey(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->dropIndex(
            $setup->getTable('amasty_geoip_block'),
            $setup->getConnection()->getIndexName(
                $setup->getTable('amasty_geoip_block'),
                'start_ip_num',
                AdapterInterface::INDEX_TYPE_INDEX
            )
        );
        $setup->getConnection()->dropIndex(
            $setup->getTable('amasty_geoip_block'),
            $setup->getConnection()->getIndexName(
                $setup->getTable('amasty_geoip_block'),
                'end_ip_num',
                AdapterInterface::INDEX_TYPE_INDEX
            )
        );

        $setup->getConnection()->addIndex(
            $setup->getTable('amasty_geoip_block'),
            $setup->getConnection()->getIndexName(
                $setup->getTable('amasty_geoip_block'),
                ['start_ip_num', 'end_ip_num'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            ['start_ip_num', 'end_ip_num']
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addRegion(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_geoip_location'),
            'region',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Region'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addIndexBlock(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addIndex(
            $setup->getTable('amasty_geoip_block'),
            $setup->getConnection()->getIndexName(
                $setup->getTable('amasty_geoip_block'),
                'geoip_loc_id',
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            'geoip_loc_id'
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    private function addIpV6Table(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $table = $connection->newTable($setup->getTable('amasty_geoip_block_v6'))
            ->addColumn(
                'block_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Block Id'
            )
            ->addColumn(
                'start_ip_num',
                Table::TYPE_TEXT,
                40,
                ['unsigned' => true, 'nullable' => false],
                'Start Ip Num'
            )
            ->addColumn(
                'end_ip_num',
                Table::TYPE_TEXT,
                40,
                ['unsigned' => true, 'nullable' => false],
                'End Ip Num'
            )
            ->addColumn(
                'geoip_loc_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Geoip Loc Id'
            )
            ->addColumn(
                'postal_code',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Postal Code'
            )
            ->addColumn(
                'latitude',
                Table::TYPE_FLOAT,
                null,
                ['nullable' => true],
                'Latitude'
            )
            ->addColumn(
                'longitude',
                Table::TYPE_FLOAT,
                null,
                ['nullable' => true],
                'Longitude'
            )
            ->setComment('Amasty Geoip Block Table IpV6');

        $connection->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function changeEngine(SchemaSetupInterface $setup)
    {
        $ipV6Table = $setup->getTable('amasty_geoip_block_v6');
        $geoIPBlockTable = $setup->getTable('amasty_geoip_block');
        $geoIPLocationTable = $setup->getTable('amasty_geoip_location');
        $setup->getConnection()->changeTableEngine($geoIPBlockTable, 'INNODB');
        $setup->getConnection()->changeTableEngine($ipV6Table, 'INNODB');
        $setup->getConnection()->changeTableEngine($geoIPLocationTable, 'INNODB');
    }
}
