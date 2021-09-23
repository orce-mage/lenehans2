<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Operation\UpdateSchemaTo200
     */
    private $updateSchemaTo200;

    /**
     * @var Operation\CreateImageBakingInfoTable
     */
    private $createImageBakingInfoTable;

    public function __construct(
        Operation\UpdateSchemaTo200 $updateSchemaTo200,
        Operation\CreateImageBakingInfoTable $createImageBakingInfoTable
    ) {
        $this->updateSchemaTo200 = $updateSchemaTo200;
        $this->createImageBakingInfoTable = $createImageBakingInfoTable;
    }

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if ($context->getVersion() && version_compare($context->getVersion(), '2.0.0', '<')) {
            $disabled = explode(',', str_replace(' ', ',', ini_get('disable_functions')));
            if (!in_array('class_exists', $disabled)
                && function_exists('class_exists')
                && class_exists(\Amasty\GiftCard\Cron\SendGiftCard::class)) {
                throw new \RuntimeException("This update requires removing folder app/code/Amasty/GiftCard\n"
                    . "Remove this folder and unpack new version of package into app/code/Amasty/\n"
                    . "Run `php bin/magento setup:upgrade` again\n");
            }
            $this->updateSchemaTo200->execute($setup);
        }

        if ($context->getVersion() && version_compare($context->getVersion(), '2.0.0', '>=')
            && version_compare($context->getVersion(), '2.5.0', '<')
        ) {
            $this->createImageBakingInfoTable->execute($setup);
        }

        $setup->endSetup();
    }
}
