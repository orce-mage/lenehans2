<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\SocialLogin\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 * @package MageBig\SocialLogin\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();
        $tableName  = $setup->getTable('magebig_social_customer');
        if (version_compare($context->getVersion(), '1.1.0', '<')
            && $connection->tableColumnExists($tableName, 'social_created_at') === false
        ) {
            $connection->addColumn(
                $tableName,
                'social_created_at',
                [
                    'type'    => Table::TYPE_TIMESTAMP,
                    'comment' => 'Social Created At',
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $connection->addColumn(
                $tableName,
                'status',
                [
                    'type'    => Table::TYPE_TEXT,
                    'length'  => 255,
                    'comment' => 'Status',
                ]
            );
        }
        $installer->endSetup();
    }
}
