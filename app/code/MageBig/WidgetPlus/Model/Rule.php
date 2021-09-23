<?php

namespace MageBig\WidgetPlus\Model;

class Rule extends \Magento\CatalogRule\Model\Rule
{
    /**
     * Callback function for product matching
     *
     * @param array $args
     * @return void
     */
    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);
        $websites = $this->_getWebsitesMap();
        foreach ($websites as $websiteId => $defaultStoreId) {
            $product->setStoreId($defaultStoreId);
            if ($this->getConditions()->validate($product)) {
                $this->_productIds[] = $product->getId();
            }
        }
    }
}