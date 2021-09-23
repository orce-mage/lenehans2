<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\CacheWarmer\Setup\UpgradeSchema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Mirasvit\CacheWarmer\Api\Data\LogInterface;
use Mirasvit\CacheWarmer\Api\Service\WarmerServiceInterface;

class UpgradeSchema102 implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $setup->getConnection();
        $tableLog   = $connection->newTable(
            $setup->getTable(LogInterface::TABLE_NAME)
        )->addColumn(
            LogInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Report Id'
        )->addColumn(
            LogInterface::RESPONSE_TIME,
            Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Response Time'
        )->addColumn(
            LogInterface::IS_HIT,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => '0'],
            'Hit'
        )->addColumn(
            LogInterface::URI,
            Table::TYPE_TEXT,
            '1024',
            ['nullable' => true],
            'Url'
        )->addColumn(
            LogInterface::CREATED_AT,
            Table::TYPE_TIMESTAMP,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Created At'
        )->addIndex(
            $setup->getIdxName(LogInterface::TABLE_NAME, [LogInterface::ID]),
            [LogInterface::ID]
        )->addIndex(
            $setup->getIdxName(LogInterface::TABLE_NAME, [LogInterface::CREATED_AT]),
            [LogInterface::CREATED_AT]
        );
        $connection->createTable($tableLog);

        //unique user agent for security reason
        $data = [
            'scope'    => 'default',
            'scope_id' => 0,
            'path'     => WarmerServiceInterface::WARMER_UNIQUE_VALUE,
            'value'    => uniqid(),
        ];
        $connection->insertOnDuplicate($setup->getTable('core_config_data'), $data, ['value']);
    }
}
