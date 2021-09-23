<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\SmartMenu\Model\Category;

/**
 * Class DataProvider.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProvider extends \Magento\Catalog\Model\Category\DataProvider
{
    /**
     * @return array
     */
    protected function getFieldsMap()
    {
        return [
            'general' => [
                    'parent',
                    'path',
                    'is_active',
                    'include_in_menu',
                    'name',
                ],
            'content' => [
                    'image',
                    'description',
                    'landing_page',
                ],
            'smartmenu' => [
                    'smartmenu_show_on_cat',
                    'smartmenu_cat_target',
                    'smartmenu_cat_style',
                    'smartmenu_cat_position',
                    'smartmenu_cat_dropdown_width',
                    'smartmenu_cat_column',
                    'smartmenu_block_right',
                    'smartmenu_block_left',
                    'smartmenu_static_right',
                    'smartmenu_static_left',
                    'smartmenu_static_top',
                    'smartmenu_static_bottom',
                    'smartmenu_cat_label',
                    'smartmenu_cat_icon',
                    'smartmenu_cat_imgicon',
                ],
            'display_settings' => [
                    'display_mode',
                    'is_anchor',
                    'available_sort_by',
                    'use_config.available_sort_by',
                    'default_sort_by',
                    'use_config.default_sort_by',
                    'filter_price_range',
                    'use_config.filter_price_range',
                ],
            'search_engine_optimization' => [
                    'url_key',
                    'url_key_create_redirect',
                    'use_default.url_key',
                    'url_key_group',
                    'meta_title',
                    'meta_keywords',
                    'meta_description',
                ],
            'assign_products' => [
                ],
            'design' => [
                    'custom_use_parent_settings',
                    'custom_apply_to_products',
                    'custom_design',
                    'page_layout',
                    'custom_layout_update',
                ],
            'schedule_design_update' => [
                    'custom_design_from',
                    'custom_design_to',
                ],
            'category_view_optimization' => [
                ],
            'category_permissions' => [
                ],
        ];
    }
}
