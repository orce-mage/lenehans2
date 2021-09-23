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

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Mirasvit\CacheWarmer\Api\Data\PageInterface;
use Mirasvit\CacheWarmer\Api\Repository\PageRepositoryInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema1012 implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $setup->getConnection();
        $connection->addColumn(
            $setup->getTable(PageInterface::TABLE_NAME),
            PageInterface::VARY_DATA_HASH,
            [
                'type'     => Table::TYPE_TEXT,
                'size' => 255,
                'nullable' => false,
                'comment'  => PageInterface::VARY_DATA_HASH,
                'after' => PageInterface::VARY_DATA,
            ]
        );
        $connection->addColumn(
            $setup->getTable(PageInterface::TABLE_NAME),
            PageInterface::USER_AGENT,
            [
                'type'     => Table::TYPE_TEXT,
                'size' => 255,
                'nullable' => true,
                'comment'  => PageInterface::USER_AGENT,
                'after' => PageInterface::VARY_DATA_HASH,
            ]
        );
    }
}
