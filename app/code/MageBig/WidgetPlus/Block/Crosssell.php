<?php
/**
 * Created by PhpStorm.
 * User: minhl
 * Date: 03/20/18
 * Time: 14:28
 */

namespace MageBig\WidgetPlus\Block;


class Crosssell extends \Magento\Checkout\Block\Cart\Crosssell
{

    public function getLoadedProductCollection()
    {
        $ninProductIds = $this->_getCartProductIds();
        if ($ninProductIds) {
            $lastAdded = (int)$this->_getLastAddedProductId();
            if ($lastAdded) {
                $collection = $this->_getCollection()->addProductFilter($lastAdded);
                if (!empty($ninProductIds)) {
                    $collection->addExcludeProductFilter($ninProductIds);
                }
                $collection->setPositionOrder()->load();
            } else {
                $collection = $this->_getCollection()->addProductFilter(
                    $ninProductIds
                )->addExcludeProductFilter(
                    $ninProductIds
                )->setGroupBy()->setPositionOrder()->load();
            }
            if ($collection->count()) {
                return $collection;
            }
        }

        return;
    }

    public function getWidgetId()
    {
        $widgetId = crc32(json_encode($this->getData()));
        $widgetId = 'widgetplus-'.$widgetId;

        return $widgetId;
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