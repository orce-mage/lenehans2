<?php

namespace MageBig\AjaxSearch\Model\Search;

use \MageBig\AjaxSearch\Helper\Data as HelperData;
use \Magento\Search\Helper\Data as SearchHelper;
use \Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use \Magento\Framework\ObjectManagerInterface as ObjectManager;
use \Magento\Search\Model\QueryFactory;
use \MageBig\AjaxSearch\Model\Source\AutocompleteFields;
use \MageBig\AjaxSearch\Model\Source\ProductFields;

/**
 * Product model. Return product data used in search autocomplete
 */
class Product implements \MageBig\AjaxSearch\Model\SearchInterface
{
    /**
     * @var \MageBig\AjaxSearch\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Search\Helper\Data
     */
    protected $searchHelper;

    /**
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    protected $layerResolver;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Search\Model\QueryFactory
     */
    private $queryFactory;

    /**
     * Product constructor.
     *
     * @param HelperData $helperData
     * @param SearchHelper $searchHelper
     * @param LayerResolver $layerResolver
     * @param ObjectManager $objectManager
     * @param QueryFactory $queryFactory
     */
    public function __construct(
        HelperData $helperData,
        SearchHelper $searchHelper,
        LayerResolver $layerResolver,
        ObjectManager $objectManager,
        QueryFactory $queryFactory
    ) {
        $this->helperData = $helperData;
        $this->searchHelper = $searchHelper;
        $this->layerResolver = $layerResolver;
        $this->objectManager = $objectManager;
        $this->queryFactory = $queryFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseData()
    {
        $responseData['code'] = AutocompleteFields::PRODUCT;
        $responseData['data'] = [];

        if (!$this->canAddToResult()) {
            return $responseData;
        }

        $query = $this->queryFactory->get();
        $queryText = $query->getQueryText();
        $productResultFields = $this->helperData->getProductResultFieldsAsArray();
        $productResultFields[] = ProductFields::URL;

        $productCollection = $this->getProductCollection($queryText);

        foreach ($productCollection as $product) {
            $responseData['data'][] = array_intersect_key(
                $this->getProductData($product),
                array_flip($productResultFields)
            );
        }

        $responseData['size'] = $productCollection->getSize();
        $responseData['url'] = ($productCollection->getSize() > 0) ? $this->searchHelper->getResultUrl(
            $queryText
        ) : '';

        $query->saveNumResults($responseData['size']);
        $query->saveIncrementalPopularity();

        return $responseData;
    }

    /**
     * Retrive product collection by query text
     *
     * @param string $queryText
     * @return mixed
     */
    protected function getProductCollection($queryText)
    {
        $productResultNumber = $this->helperData->getProductResultNumber();

        $this->layerResolver->create(LayerResolver::CATALOG_LAYER_SEARCH);

        $productCollection = $this->layerResolver->get()
            ->getProductCollection()
            ->setPageSize($productResultNumber)
            ->addAttributeToSort('relevance')
            ->setOrder('relevance')
            ->addSearchFilter($queryText);

        return $productCollection;
    }

    /**
     * Retrieve all product data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    protected function getProductData($product)
    {
        /** @var \MageBig\AjaxSearch\Block\Autocomplete\ProductAgregator $productAgregator */
        $productAgregator = $this->objectManager->create('MageBig\AjaxSearch\Block\Autocomplete\ProductAgregator')
            ->setProduct($product);

        $data = [
            ProductFields::NAME => $productAgregator->getName(),
            ProductFields::SKU => $productAgregator->getSku(),
            ProductFields::IMAGE => $productAgregator->getSmallImage(),
            ProductFields::REVIEWS_RATING => $productAgregator->getReviewsRating(),
            ProductFields::PRICE => $productAgregator->getPrice(),
            ProductFields::URL => $productAgregator->getUrl()
        ];

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function canAddToResult()
    {
        return in_array(AutocompleteFields::PRODUCT, $this->helperData->getAutocompleteFieldsAsArray());
    }
}
