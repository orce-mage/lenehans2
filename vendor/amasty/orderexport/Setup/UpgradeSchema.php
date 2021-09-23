<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Amasty\Orderexport\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Operation\UpgradeTo110
     */
    private $upgradeTo110;

    /**
     * @var Operation\UpgradeTo111
     */
    private $upgradeTo111;

    /**
     * @var Operation\UpgradeTo112
     */
    private $upgradeTo112;

    /**
     * @var Operation\UpgradeTo121
     */
    private $upgradeTo121;

    /**
     * @var Operation\UpgradeTo130
     */
    private $upgradeTo130;

    /**
     * @var Operation\UpgradeTo132
     */
    private $upgradeTo132;

    /**
     * @var Operation\UpgradeTo133
     */
    private $upgradeTo133;

    /**
     * @var Operation\InstallAttributesSchema
     */
    private $installAttributesSchema;

    public function __construct(
        Operation\UpgradeTo110 $upgradeTo110,
        Operation\UpgradeTo111 $upgradeTo111,
        Operation\UpgradeTo112 $upgradeTo112,
        Operation\UpgradeTo121 $upgradeTo121,
        Operation\UpgradeTo130 $upgradeTo130,
        Operation\UpgradeTo132 $upgradeTo132,
        Operation\UpgradeTo133 $upgradeTo133,
        Operation\InstallAttributesSchema $installAttributesSchema
    ) {
        $this->upgradeTo110 = $upgradeTo110;
        $this->upgradeTo111 = $upgradeTo111;
        $this->upgradeTo112 = $upgradeTo112;
        $this->upgradeTo121 = $upgradeTo121;
        $this->upgradeTo130 = $upgradeTo130;
        $this->upgradeTo132 = $upgradeTo132;
        $this->upgradeTo133 = $upgradeTo133;
        $this->installAttributesSchema = $installAttributesSchema;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->upgradeTo110->execute($setup);
        }

        if (version_compare($context->getVersion(), '1.1.1', '<')) {
            $this->upgradeTo111->execute($setup);
        }

        if (version_compare($context->getVersion(), '1.1.2', '<')) {
            $this->upgradeTo112->execute($setup);
        }

        if (version_compare($context->getVersion(), '1.2.0', '<')) {
            $this->installAttributesSchema->execute($setup, $context);
        }

        if (version_compare($context->getVersion(), '1.2.1', '<')) {
            $this->upgradeTo121->execute($setup);
        }

        if (version_compare($context->getVersion(), '1.3.0', '<')) {
            $this->upgradeTo130->execute($setup);
        }

        if (version_compare($context->getVersion(), '1.3.2', '<')) {
            $this->upgradeTo132->execute($setup);
        }

        if (version_compare($context->getVersion(), '1.3.3', '<')) {
            $this->upgradeTo133->execute($setup);
        }

        $setup->endSetup();
    }
}
