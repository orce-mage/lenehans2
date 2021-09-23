<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\SmartMenu\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory.
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init.
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @param  ModuleDataSetupInterface
     * @param  ModuleContextInterface
     * @return install data
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $dataSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $dataSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'smartmenu_show_on_cat',
            [
                'label'                    => 'Display in Catalog Page',
                'note'                     => 'Display category in Catalog Page',
                'type'                     => 'int',
                'input'                    => 'select',
                'source'                   => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'sort_order'               => 10,
                'visible'                  => true,
                'required'                 => false,
                'searchable'               => false,
                'filterable'               => false,
                'comparable'               => false,
                'user_defined'             => true,
                'visible_on_front'         => true,
                'wysiwyg_enabled'          => false,
                'is_html_allowed_on_front' => false,
                'global'                   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group'                    => 'Smart Menu',
            ]
        );

        $dataSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'smartmenu_cat_target',
            [
                'label'                    => 'Custom URL',
                'note'                     => "# - return not click, page - return yoursite.com/page, http://yoururl.com - return http://yoururl.com",
                'type'                     => 'varchar',
                'input'                    => 'text',
                'sort_order'               => 20,
                'user_defined'             => true,
                'required'                 => false,
                'visible'                  => true,
                'searchable'               => false,
                'filterable'               => false,
                'comparable'               => false,
                'visible_on_front'         => true,
                'wysiwyg_enabled'          => false,
                'is_html_allowed_on_front' => false,
                'global'                   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group'                    => 'Smart Menu',
            ]
        );

        $dataSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'smartmenu_cat_style',
            [
                'label'                    => 'Category Menu Style',
                'note'                     => 'Only applicable to category level 1',
                'type'                     => 'varchar',
                'input'                    => 'select',
                'source'                   => 'MageBig\SmartMenu\Model\System\Config\Source\Category\Style',
                'sort_order'               => 30,
                'visible'                  => true,
                'required'                 => false,
                'searchable'               => false,
                'filterable'               => false,
                'comparable'               => false,
                'user_defined'             => true,
                'visible_on_front'         => true,
                'wysiwyg_enabled'          => false,
                'is_html_allowed_on_front' => false,
                'global'                   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group'                    => 'Smart Menu',
            ]
        );

        $dataSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'smartmenu_cat_position',
            [
                'label'                    => 'Align Submenu',
                'note'                     => 'Only applicable to category level 1',
                'type'                     => 'varchar',
                'input'                    => 'select',
                'source'                   => 'MageBig\SmartMenu\Model\System\Config\Source\Category\Position',
                'sort_order'               => 40,
                'visible'                  => true,
                'required'                 => false,
                'searchable'               => false,
                'filterable'               => false,
                'comparable'               => false,
                'user_defined'             => true,
                'visible_on_front'         => true,
                'wysiwyg_enabled'          => false,
                'is_html_allowed_on_front' => false,
                'global'                   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group'                    => 'Smart Menu',
            ]
        );

        $dataSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'smartmenu_cat_dropdown_width',
            [
                'label'                    => 'Dropdown Menu Width',
                'note'                     => 'E.g: 350px, 60%',
                'type'                     => 'text',
                'input'                    => 'text',
                'sort_order'               => 50,
                'visible'                  => true,
                'required'                 => false,
                'searchable'               => false,
                'filterable'               => false,
                'comparable'               => false,
                'user_defined'             => true,
                'visible_on_front'         => true,
                'wysiwyg_enabled'          => false,
                'is_html_allowed_on_front' => true,
                'global'                   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group'                    => 'Smart Menu',
            ]
        );

        $dataSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'smartmenu_cat_column',
            [
                'label'                    => 'Subcategory Columns',
                'note'                     => 'Only applicable to category level 1 - Mega Dropdown',
                'type'                     => 'varchar',
                'input'                    => 'select',
                'source'                   => 'MageBig\SmartMenu\Model\System\Config\Source\Category\Column',
                'sort_order'               => 60,
                'visible'                  => true,
                'required'                 => false,
                'searchable'               => false,
                'filterable'               => false,
                'comparable'               => false,
                'user_defined'             => true,
                'visible_on_front'         => true,
                'wysiwyg_enabled'          => false,
                'is_html_allowed_on_front' => false,
                'global'                   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group'                    => 'Smart Menu',
            ]
        );

        $dataSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'smartmenu_block_right',
            [
                'label'                    => 'Static Block Right',
                'type'                     => 'varchar',
                'input'                    => 'select',
                'source'                   => 'MageBig\SmartMenu\Model\System\Config\Source\Category\Width',
                'sort_order'               => 70,
                'visible'                  => true,
                'required'                 => false,
                'searchable'               => false,
                'filterable'               => false,
                'comparable'               => false,
                'user_defined'             => true,
                'visible_on_front'         => true,
                'wysiwyg_enabled'          => false,
                'is_html_allowed_on_front' => false,
                'global'                   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group'                    => 'Smart Menu',
            ]
        );

        $dataSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'smartmenu_block_left',
            [
                'label'                    => 'Static Block Left',
                'type'                     => 'varchar',
                'input'                    => 'select',
                'source'                   => 'MageBig\SmartMenu\Model\System\Config\Source\Category\Width',
                'sort_order'               => 80,
                'visible'                  => true,
                'required'                 => false,
                'searchable'               => false,
                'filterable'               => false,
                'comparable'               => false,
                'user_defined'             => true,
                'visible_on_front'         => true,
                'wysiwyg_enabled'          => false,
                'is_html_allowed_on_front' => false,
                'global'                   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group'                    => 'Smart Menu',
            ]
        );

        $dataSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'smartmenu_static_right',
            [
                'label'                    => 'Static Block Right',
                'note'                     => 'Only applicable to category level 2 - Mega Dropdown',
                'type'                     => 'text',
                'input'                    => 'textarea',
                'sort_order'               => 90,
                'visible'                  => true,
                'required'                 => false,
                'searchable'               => false,
                'filterable'               => false,
                'comparable'               => false,
                'user_defined'             => true,
                'visible_on_front'         => true,
                'wysiwyg_enabled'          => true,
                'is_html_allowed_on_front' => true,
                'global'                   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group'                    => 'Smart Menu',
            ]
        );

        $dataSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'smartmenu_static_left',
            [
                'label'                    => 'Static Block Left',
                'note'                     => 'Only applicable to category level 2 - Mega Dropdown',
                'type'                     => 'text',
                'input'                    => 'textarea',
                'sort_order'               => 100,
                'visible'                  => true,
                'required'                 => false,
                'searchable'               => false,
                'filterable'               => false,
                'comparable'               => false,
                'user_defined'             => true,
                'visible_on_front'         => true,
                'wysiwyg_enabled'          => true,
                'is_html_allowed_on_front' => true,
                'global'                   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group'                    => 'Smart Menu',
            ]
        );

        $dataSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'smartmenu_static_top',
            [
                'label'                    => 'Static Block Top',
                'note'                     => 'Only applicable to category level 2 - Mega Dropdown',
                'type'                     => 'text',
                'input'                    => 'textarea',
                'sort_order'               => 110,
                'visible'                  => true,
                'required'                 => false,
                'searchable'               => false,
                'filterable'               => false,
                'comparable'               => false,
                'user_defined'             => true,
                'visible_on_front'         => true,
                'wysiwyg_enabled'          => true,
                'is_html_allowed_on_front' => true,
                'global'                   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group'                    => 'Smart Menu',
            ]
        );

        $dataSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'smartmenu_static_bottom',
            [
                'label'                    => 'Static Block Bottom',
                'note'                     => 'Only applicable to category level 2 - Mega Dropdown',
                'type'                     => 'text',
                'input'                    => 'textarea',
                'sort_order'               => 120,
                'visible'                  => true,
                'required'                 => false,
                'searchable'               => false,
                'filterable'               => false,
                'comparable'               => false,
                'user_defined'             => true,
                'visible_on_front'         => true,
                'wysiwyg_enabled'          => true,
                'is_html_allowed_on_front' => true,
                'global'                   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group'                    => 'Smart Menu',
            ]
        );

        $dataSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'smartmenu_cat_bg',
            [
                'label'                    => 'Background Image',
                'note'                     => 'Only applicable to category level 1',
                'type'                     => 'text',
                'input'                    => 'image',
                'sort_order'               => 125,
                'visible'                  => true,
                'required'                 => false,
                'backend'                  => 'Magento\Catalog\Model\Category\Attribute\Backend\Image',
                'searchable'               => false,
                'filterable'               => false,
                'comparable'               => false,
                'user_defined'             => true,
                'visible_on_front'         => true,
                'wysiwyg_enabled'          => false,
                'is_html_allowed_on_front' => false,
                'global'                   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group'                    => 'Smart Menu',
            ]
        );

        $dataSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'smartmenu_cat_label',
            [
                'label'                    => 'Category Label',
                'type'                     => 'varchar',
                'input'                    => 'select',
                'source'                   => 'MageBig\SmartMenu\Model\System\Config\Source\Category\Catlabel',
                'sort_order'               => 130,
                'visible'                  => true,
                'required'                 => false,
                'searchable'               => false,
                'filterable'               => false,
                'comparable'               => false,
                'user_defined'             => true,
                'visible_on_front'         => true,
                'wysiwyg_enabled'          => false,
                'is_html_allowed_on_front' => false,
                'global'                   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group'                    => 'Smart Menu',
            ]
        );

        $dataSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'smartmenu_cat_icon',
            [
                'label'                    => 'Icon Font',
                'type'                     => 'text',
                'input'                    => 'text',
                'sort_order'               => 140,
                'visible'                  => true,
                'required'                 => false,
                'searchable'               => false,
                'filterable'               => false,
                'comparable'               => false,
                'user_defined'             => true,
                'visible_on_front'         => true,
                'wysiwyg_enabled'          => false,
                'is_html_allowed_on_front' => false,
                'global'                   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group'                    => 'Smart Menu',
            ]
        );

        $dataSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'smartmenu_cat_imgicon',
            [
                'label'                    => 'Icon Image',
                'type'                     => 'text',
                'input'                    => 'image',
                'sort_order'               => 150,
                'visible'                  => true,
                'required'                 => false,
                'backend'                  => 'Magento\Catalog\Model\Category\Attribute\Backend\Image',
                'searchable'               => false,
                'filterable'               => false,
                'comparable'               => false,
                'user_defined'             => true,
                'visible_on_front'         => true,
                'wysiwyg_enabled'          => false,
                'is_html_allowed_on_front' => false,
                'global'                   => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group'                    => 'Smart Menu',
            ]
        );
    }
}
