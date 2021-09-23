<?php

namespace MageBig\SmartMenu\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $categorySetupFactory;


    /**
     * Init
     *
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(\Magento\Catalog\Setup\CategorySetupFactory $categorySetupFactory)
    {
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var CustomerSetup $customerSetup */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
        $setup->startSetup();
        if ($context->getVersion()
            && version_compare($context->getVersion(), '2.0.1') < 0
        ) {
            $categorySetup->updateAttribute('catalog_category', 'smartmenu_show_on_cat', 'used_in_product_listing', 1);
            $categorySetup->updateAttribute('catalog_category', 'smartmenu_cat_target', 'used_in_product_listing', 1);
            $categorySetup->updateAttribute('catalog_category', 'smartmenu_cat_style', 'used_in_product_listing', 1);
            $categorySetup->updateAttribute('catalog_category', 'smartmenu_cat_position', 'used_in_product_listing', 1);
            $categorySetup->updateAttribute('catalog_category', 'smartmenu_cat_dropdown_width', 'used_in_product_listing', 1);
            $categorySetup->updateAttribute('catalog_category', 'smartmenu_cat_column', 'used_in_product_listing', 1);
            $categorySetup->updateAttribute('catalog_category', 'smartmenu_block_right', 'used_in_product_listing', 1);
            $categorySetup->updateAttribute('catalog_category', 'smartmenu_block_left', 'used_in_product_listing', 1);
            $categorySetup->updateAttribute('catalog_category', 'smartmenu_static_right', 'used_in_product_listing', 1);
            $categorySetup->updateAttribute('catalog_category', 'smartmenu_static_left', 'used_in_product_listing', 1);
            $categorySetup->updateAttribute('catalog_category', 'smartmenu_static_top', 'used_in_product_listing', 1);
            $categorySetup->updateAttribute('catalog_category', 'smartmenu_static_bottom', 'used_in_product_listing', 1);
            $categorySetup->updateAttribute('catalog_category', 'smartmenu_cat_label', 'used_in_product_listing', 1);
            $categorySetup->updateAttribute('catalog_category', 'smartmenu_cat_icon', 'used_in_product_listing', 1);
            $categorySetup->updateAttribute('catalog_category', 'smartmenu_cat_imgicon', 'used_in_product_listing', 1);
        }

        $setup->endSetup();
    }
}