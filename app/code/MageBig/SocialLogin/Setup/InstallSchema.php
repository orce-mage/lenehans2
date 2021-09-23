<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\SocialLogin\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

/**
 * Class InstallSchema
 *
 * @package MageBig\SocialLogin\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('magebig_social_customer')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('magebig_social_customer'))
                ->addColumn(
                    'social_customer_id',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'Social Customer ID'
                )
                ->addColumn('social_id', Table::TYPE_TEXT, 255, ['unsigned' => true, 'nullable => false'], 'Social Id')
                ->addColumn(
                    'customer_id',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true, 'nullable => false'],
                    'Customer Id'
                )
                ->addColumn(
                    'is_send_password_email',
                    Table::TYPE_INTEGER,
                    10,
                    ['unsigned' => true, 'nullable => false', 'default' => '0'],
                    'Is Send Password Email'
                )
                ->addColumn('type', Table::TYPE_TEXT, 255, ['default' => ''], 'Type')
                ->addForeignKey(
                    $installer->getFkName('magebig_social_customer', 'customer_id', 'customer_entity', 'entity_id'),
                    'customer_id',
                    $installer->getTable('customer_entity'),
                    'entity_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('Social Customer Table');
            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }
}
