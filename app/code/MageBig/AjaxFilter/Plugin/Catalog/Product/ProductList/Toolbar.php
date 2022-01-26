<?php
/**
 * Copyright © www.magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageBig\AjaxFilter\Plugin\Catalog\Product\ProductList;

use MageBig\AjaxFilter\Model\Layer\Filter\Rating;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;

class Toolbar
{
    private $toolbarModel;
    private $coreRegistry;
    private $scopeConfig;

    public function __construct(
        \Magento\Catalog\Model\Product\ProductList\Toolbar $toolbarModel,
        ScopeConfigInterface $scopeConfig,
        Registry $coreRegistry
    ) {
        $this->toolbarModel = $toolbarModel;
        $this->scopeConfig = $scopeConfig;
        $this->coreRegistry = $coreRegistry;
    }

    public function aroundSetCollection(
        \Magento\Catalog\Block\Product\ProductList\Toolbar $subject,
        \Closure $proceed,
        $collection
    ) {
        if (!$this->coreRegistry->registry('product_filter_collection')) {
            $c1 = clone $collection;
            $c1->setOrder('price', 'desc')->getFirstItem();
            $this->coreRegistry->register('product_filter_collection', $c1);
        }

        $order = $subject->getCurrentOrder();
        $result = $proceed($collection);
        $ratingCode = Rating::RATING_CODE;

        if ($ratingCode && ($order == $ratingCode)) {
            $direction = $subject->getCurrentDirection();

            $searchEngine = $this->scopeConfig->getValue('catalog/search/engine', ScopeInterface::SCOPE_STORE);
            if ($searchEngine == 'mysql') {
                $collection->setOrder('rating_summary', $direction);
            } else {
                $collection->setOrder('rating', $direction);
            }
        }

        return $result;
    }

    /**
     * @param $subject
     * @param $dir
     * @return string
     */
    public function afterGetCurrentDirection($subject, $dir)
    {
        $defaultDir = $subject->getCurrentOrder() == 'rating' ? 'desc' : $dir;
        $subject->setDefaultDirection($defaultDir);

        if (!$this->toolbarModel->getDirection()) {
            $dir = $defaultDir;
        }

        return $dir;
    }
}
