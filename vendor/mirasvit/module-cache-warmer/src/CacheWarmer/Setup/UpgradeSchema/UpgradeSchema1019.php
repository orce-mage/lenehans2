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


use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Mirasvit\CacheWarmer\Api\Data\PageInterface;

class UpgradeSchema1019 implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $setup->getConnection();

        $connection->addIndex(
            $setup->getTable(PageInterface::TABLE_NAME),
            $setup->getIdxName(
                PageInterface::TABLE_NAME,
                ['warm_rule_version'],
                AdapterInterface::INDEX_TYPE_INDEX
            ),
            [
                'warm_rule_version',
            ]
        );
    }
}
