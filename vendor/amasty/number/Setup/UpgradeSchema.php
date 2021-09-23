<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Number
 */


namespace Amasty\Number\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\Number\Setup\Operation;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Operation\UpgradeSchemaTo200
     */
    private $upgradeTo200;

    public function __construct(
        Operation\UpgradeSchemaTo200 $upgradeTo200
    ) {
        $this->upgradeTo200 = $upgradeTo200;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /** @since 2.0.0 DB architecture changed. Counter data moved to our table form core_config_data */
        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->upgradeTo200->execute($setup);
        }

        $setup->endSetup();
    }
}
