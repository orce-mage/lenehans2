<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Geoip
 */


declare(strict_types=1);

namespace Amasty\Geoip\Setup;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{

    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->removeTables($setup);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function removeTables(SchemaSetupInterface $setup)
    {
        $defaultConnection = $setup->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $defaultConnection->dropTable($setup->getTable('amasty_geoip_block_v6'));
        $defaultConnection->dropTable($setup->getTable('amasty_geoip_location'));
        $defaultConnection->dropTable($setup->getTable('amasty_geoip_block'));
    }
}
