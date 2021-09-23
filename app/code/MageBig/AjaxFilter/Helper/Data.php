<?php
/**
 * Copyright © www.magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\AjaxFilter\Helper;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const RATING_CODE = \MageBig\AjaxFilter\Model\Layer\Filter\Rating::RATING_CODE;
    const ENABLE_AJAX = 'magebig_ajaxfilter/general/enable';
    const ENABLE_PRICE_SLIDER = 'magebig_ajaxfilter/general/enable_price_slider';
    const MAX_HEIGHT_BOX_STAGE = 'magebig_ajaxfilter/general/max_height_box_state';
    const ENABLE_FILTER_BY_RATING = 'magebig_ajaxfilter/general/enable_filter_rating';
    const ENABLE_SORT_BY_RATING = 'magebig_ajaxfilter/general/enable_sort_rating';
    const RATING_FILTER_TYPE_PATH = 'magebig_ajaxfilter/general/rating_filter_type';

    protected $filterManager;

    protected $enable;

    protected $layout;

    protected $objectManager;

    protected $_filters;

    protected $enableMultiSelect;

    protected $ratingFilterType;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\Filter\FilterManager $filterManager
    ) {
        parent::__construct($context);
        $this->layout = $layout;
        $this->filterManager = $filterManager;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    public function getLayout()
    {
        if (null === $this->layout) {
            $this->layout = $this->objectManager->get('\Magento\Framework\View\LayoutInterface');
        }
        return $this->layout;
    }

    public function getFilterManager()
    {
        return $this->filterManager;
    }

    public function getConfig($path)
    {
        return $this->scopeConfig->getValue($path, 'store');
    }

    public function boxMaxHeight()
    {
        return $this->getConfig(self::MAX_HEIGHT_BOX_STAGE, 'store');
    }

    public function enableAjaxFilter()
    {
        return (int) $this->getConfig(self::ENABLE_AJAX, 'store');
    }

    public function enablePriceSlider()
    {
        return (bool) $this->getConfig(self::ENABLE_PRICE_SLIDER, 'store');
    }

    public function getFilters()
    {
        if (null === $this->_filters) {
            if ($this->_request->getFullActionName() === 'catalogsearch_result_index') {
                $this->_filters = $this->objectManager->get('Magento\LayeredNavigation\Block\Navigation\Search')->getFilters();
            } else {
                $this->_filters = $this->objectManager->get('Magento\LayeredNavigation\Block\Navigation\Category')->getFilters();
            }
        }
        return $this->_filters;
    }

    public function getBeforeApplyFacetedData($collection, $attributeCode)
    {
        $cloneCollection = clone $collection;
        $cloneFilterBuilder = clone $this->objectManager->get(FilterBuilder::class);
        $cloneCollection->setFilterBuilder($cloneFilterBuilder);
        $cloneSearchCriteriaBuilder = clone $this->objectManager->get(SearchCriteriaBuilder::class);
        $cloneCollection->setSearchCriteriaBuilder($cloneSearchCriteriaBuilder);

        foreach ($this->getFilters() as $filter) {
            if ($filter->getRequestVar() != $attributeCode) {
                if (method_exists($filter, 'applyToCollection')) {
                    $filter->applyToCollection($cloneCollection, $this->_request, $filter->getRequestVar());
                }
            }
        }

        return $cloneCollection;
    }

    public function enableRatingFilter()
    {
        return (bool) $this->getConfig(self::ENABLE_FILTER_BY_RATING);
    }

    public function enableRatingSort()
    {
        return (bool) $this->getConfig(self::ENABLE_SORT_BY_RATING);
    }

    public function getObjectManager()
    {
        return $this->objectManager;
    }

    public function getRatingTypes()
    {
        return $this->getConfig(self::RATING_FILTER_TYPE_PATH) ?: 'up';
    }
}
