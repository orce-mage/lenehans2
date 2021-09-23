<?php
/**
 * Copyright © 2020 MageBig, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageBig\Shopbybrand\Model\ResourceModel\SelectedBrands;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    protected function _construct()
    {
        parent::_construct();
    }
    protected function _beforeLoad()
    {
        parent::_beforeLoad();

        $configAttributeCode = \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getValue('magebig_shopbybrand/general/attribute_code');

        $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;

        $optionValueTable = $this->getConnection()->select()
            ->from($this->getTable('eav_attribute_option_value'), ['oid' => 'option_id', 'store_id', 'brand_label' => 'value'])
            ->where("store_id = {$storeId}");

        $this->getSelect()
            ->joinLeft(['cea' => $this->getTable('catalog_eav_attribute') ], 'main_table.attribute_id = cea.attribute_id', 'is_visible')
            ->joinLeft(['ea' => $this->getTable('eav_attribute') ], 'cea.attribute_id = ea.attribute_id', 'attribute_code')
            ->joinLeft(['eaov' => $optionValueTable ], 'eaov.oid = main_table.option_id', ['brand_label'])
            ->where("ea.attribute_code = '{$configAttributeCode}'")
            ->group("main_table.option_id");
    }
}
