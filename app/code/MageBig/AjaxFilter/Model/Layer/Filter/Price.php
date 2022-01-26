<?php
/**
 * Copyright © www.magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\AjaxFilter\Model\Layer\Filter;

use MageBig\AjaxFilter\Helper\Data;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory;
use Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Framework\Search\Dynamic\Algorithm;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Layer price filter based on Search API
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Price extends \Magento\CatalogSearch\Model\Layer\Filter\Price
{
    /** Price delta for filter  */
    const PRICE_DELTA = 0.001;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\DataProvider\Price
     */
    private $dataProvider;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price
     */
    private $resource;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var Algorithm
     */
    private $priceAlgorithm;

    protected $helper;

    protected $request;
    private $coreRegistry;

    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        DataBuilder $itemDataBuilder,
        \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price $resource,
        Session $customerSession,
        Algorithm $priceAlgorithm,
        PriceCurrencyInterface $priceCurrency,
        AlgorithmFactory $algorithmFactory,
        PriceFactory $dataProviderFactory,
        RequestInterface $request,
        Registry $coreRegistry,
        Data $helper,
        array $data = []
    )
    {
        $this->priceCurrency = $priceCurrency;
        $this->resource = $resource;
        $this->customerSession = $customerSession;
        $this->priceAlgorithm = $priceAlgorithm;
        $this->request = $request;
        $this->coreRegistry = $coreRegistry;
        $this->helper = $helper;

        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $resource,
            $customerSession,
            $priceAlgorithm,
            $priceCurrency,
            $algorithmFactory,
            $dataProviderFactory,
            $data
        );

        $this->dataProvider = $dataProviderFactory->create(['layer' => $this->getLayer()]);
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * {@inheritDoc}
     */
    /*protected function _renderRangeLabel($fromPrice, $toPrice)
    {
        $formattedPrice = $this->priceCurrency->format((float) $fromPrice * $this->getCurrencyRate());

        if ($toPrice === '') {
            $formattedPrice = __('%1 and above', $formattedPrice);
        } elseif ($fromPrice != $toPrice || !$this->dataProvider->getOnePriceIntervalValue()) {
            $toPrice        = (float) $toPrice * $this->getCurrencyRate();
            $formattedPrice = __('%1 - %2', $formattedPrice, $this->priceCurrency->format($toPrice));
        }

        return $formattedPrice;
    }*/

    private function prepareData($key, $count)
    {
        list($from, $to) = explode('_', $key);
        if ($from == '*') {
            $from = $this->getFrom($to);
        }
        if ($to == '*') {
            $to = $this->getTo($to);
        }
        $label = $this->_renderRangeLabel($from, $to);
        $value = $from . '-' . $to . $this->dataProvider->getAdditionalRequestData();

        $data = [
            'label' => $label,
            'value' => $value,
            'count' => $count,
            'from' => $from,
            'to' => $to,
        ];

        return $data;
    }

    public function apply(RequestInterface $request)
    {
        /**
         * Filter must be string: $fromPrice-$toPrice
         */
        $param = $request->getParam($this->getRequestVar());
        if (!$param || is_array($param)) {
            return $this;
        }

        $filterParams = explode(',', $param);
        $filter = $this->dataProvider->validateFilter($filterParams[0]);
        if (!$filter) {
            return $this;
        }

        $this->dataProvider->setInterval($filter);
        $priorFilters = $this->dataProvider->getPriorFilters($filterParams);
        if ($priorFilters) {
            $this->dataProvider->setPriorIntervals($priorFilters);
        }

        list($from, $to) = $filter;

        $this->setCurrentValue(['from' => $from, 'to' => $to]);

        $productCollection = $this->getLayer()->getProductCollection();

        $attributeCode = $this->getAttributeModel()->getAttributeCode();
        $this->setBeforeApplyFacetedData($this->helper->getBeforeApplyFacetedData($productCollection, $attributeCode));

        $productCollection->addFieldToFilter(
            'price',
            ['from' => $from, 'to' => empty($to) || $from == $to ? $to : $to - self::PRICE_DELTA]
        );

        $this->getLayer()->getState()->addFilter(
            $this->_createItem($this->_renderRangeLabel(empty($from) ? 0 : $from, $to), $filter)
        );

        return $this;
    }

    public function enablePriceSlider()
    {
        return $this->helper->enablePriceSlider();
    }

    protected function _getItemsData()
    {
        $data = [];
        $productCollection = $this->coreRegistry->registry('product_filter_collection');

        if (!$productCollection) {
            $productCollection = $this->getLayer()->getProductCollection();
        }

        $attribute = $this->getAttributeModel();
        $facets = $productCollection->getFacetedData($attribute->getAttributeCode());

        if (count($facets) > 1) {
            if (!$this->enablePriceSlider()) {
                foreach ($facets as $key => $aggregation) {
                    $count = $aggregation['count'];
                    if (strpos($key, '_') === false) {
                        continue;
                    }
                    $data[] = $this->prepareData($key, $count, $data);
                }
            } else {
                $count = $productCollection->count();
                $min = $productCollection->getMinPrice();
                $max = $productCollection->getMaxPrice();
                $this->setMinValue($min);
                $this->setMaxValue($max);

                $data[] = [
                    'label' => 'Price Slider',
                    'value' => $count,
                    'count' => $count,
                    'from' => $min,
                    'to' => $max,
                ];
            }
        }

        return $data;
    }
}
