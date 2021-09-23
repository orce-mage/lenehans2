<?php

namespace MageBig\WidgetPlus\Model\ResourceModel;

class Crosssell extends \Magento\Checkout\Block\Cart\Crosssell {

    public function getItems()
    {
        $items = $this->getData('items_crossell');
        if ($items === null) {
            $items = [];
            $ninProductIds = $this->_getCartProductIds();
            if ($ninProductIds) {
                $lastAdded = (int)$this->_getLastAddedProductId();
                if ($lastAdded) {
                    $collection = $this->_getCollection()->addProductFilter($lastAdded);
                    if (!empty($ninProductIds)) {
                        $collection->addExcludeProductFilter($ninProductIds);
                    }
                    $collection->setPositionOrder()->load();
                }
                $filterProductIds = array_merge(
                    $this->_getCartProductIds(),
                    $this->_itemRelationsList->getRelatedProductIds($this->getQuote()->getAllItems())
                );
                $collection = $this->_getCollection()->addProductFilter(
                    $filterProductIds
                )->addExcludeProductFilter(
                    $ninProductIds
                )->setGroupBy()->setPositionOrder()->load();
            }

            $this->setData('items_crossell', $collection);
        }
        return $collection;
    }

    protected function _getCollection()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection $collection */
        $collection = $this->_productLinkFactory->create()->useCrossSellLinks()->getProductCollection()->setStoreId(
            $this->_storeManager->getStore()->getId()
        )->addStoreFilter()->setVisibility(
            $this->_productVisibility->getVisibleInCatalogIds()
        );
        $this->_addProductAttributesAndPrices($collection);

        return $collection;
    }
}