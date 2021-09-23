<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Block\Email\Block\Sales;

use Magento\Framework\Exception\NoSuchEntityException;

class StaticProducts extends \Magetrend\Email\Block\Email\Block\Sales\RelatedProducts
{
    private $collection = null;

    public $productRepository;

    public $searchCriteriaBuilder;

    public $productStatus;

    public $registry;

    public function getItems()
    {
        if ($this->collection == null) {
            $this->collection = $this->getProductBySku();

            if ($this->registry->registry('mt_editor_edit_mode') == 1 && empty($this->collection)) {
                $this->collection = $this->getDemoProducts();
            }
        }

        return $this->collection;
    }

    public function getProductBySku()
    {
        $skuList = [
            $this->getSku(1),
            $this->getSku(2),
            $this->getSku(3),
            $this->getSku(4),
        ];

        $productList = [];
        foreach ($skuList as $sku) {
            try {
                $product = $this->productRepository->get($sku, false);
                $productList[] = $product;
            } catch (NoSuchEntityException $e) {
                continue;
            }
        }

        return $productList;
    }

    public function getSku($index)
    {
        $storeId = null;

        if ($order = $this->getOrder()) {
            $storeId = $order->getStoreId();
        }

        return $this->_scopeConfig->getValue(
            'mtemail/product_block/sku_'.$index,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

}
