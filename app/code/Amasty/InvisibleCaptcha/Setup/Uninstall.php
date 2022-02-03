<?php

declare(strict_types=1);

namespace Amasty\InvisibleCaptcha\Setup;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    /**
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $this->removeConfig($setup);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function removeConfig(SchemaSetupInterface $setup): void
    {
        $defaultConnection = $setup->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $defaultConnection->delete(
            $setup->getTable('core_config_data'),
            "`path` LIKE 'aminvisiblecaptcha/%'"
        );
    }
}
