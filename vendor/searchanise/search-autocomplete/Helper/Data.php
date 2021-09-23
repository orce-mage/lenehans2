<?php

namespace Searchanise\SearchAutocomplete\Helper;

use Searchanise\SearchAutocomplete\Model\Configuration;
use Searchanise\SearchAutocomplete\Model\RequestFactory;
use Searchanise\SearchAutocomplete\Model\Request as SeRequest;
use Searchanise\SearchAutocomplete\Helper\ApiSe as ApiSeHelper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Area as AppArea;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\App\Emulation as AppEmulation;
use Magento\Store\Model\Store as StoreModel;
use Magento\CatalogSearch\Helper\Data as CatalogSearchHelper;
use Magento\Theme\Block\Html\Pager as HtmlPager;

/**
 * Searhanise data helper
 */
class Data extends AbstractHelper
{
    const PARENT_PRIVATE_KEY = 'parent_private_key';

    const DISABLE_VAR_NAME = 'disabled_module_searchanise';
    const DISABLE_KEY      = 'Y';

    const DEBUG_VAR_NAME   = 'debug_module_searchanise';
    const DEBUG_KEY        = 'Y';

    const VISUAL_VAR_NAME  = 'visual';
    const VISUAL_KEY       = 'Y';

    const TEXT_FIND          = 'quick_search_container';
    const TEXT_ADVANCED_FIND = 'advanced_search_container';
    const TEXT_TEST          = 'test_container';

    private $disableText  = '';
    private $debugText    = '';
    private $runEmulation = false;

    /**
     * @var array
     */
    private static $searchaniseTypes = [
        self::TEXT_TEST,
        self::TEXT_FIND,
        self::TEXT_ADVANCED_FIND,
    ];

    /**
     * Field mapping list
     *
     * @var array
     */
    private $fieldNameMapping = [
        'name'              => 'title',
        'sku'               => 'product_code',
        'description'       => 'full_description',
        'short_description' => 'description',
    ];

    /**
     * @var SeRequest
     */
    private $searchaniseRequest = null;

    /**
     * @var string
     */
    private $searchaniseCurentType = null;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * @var AppEmulation
     */
    private $appEmulation;

    private $initialEnvironmentInfo;

    /**
     * @var CatalogSearchHelper
     */
    private $catalogSearchHelper;

    /**
     * @var HtmlPager
     */
    private $pager;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Configuration $configuration,
        RequestFactory $requestFactory,
        AppEmulation $appEmulation,
        CatalogSearchHelper $catalogSearchHelper,
        HtmlPager $pager
    ) {
        $this->storeManager = $storeManager;
        $this->configuration = $configuration;
        $this->requestFactory = $requestFactory;
        $this->appEmulation = $appEmulation;
        $this->catalogSearchHelper = $catalogSearchHelper;
        $this->pager = $pager;

        parent::__construct($context);
    }

    /**
     * Init request
     *
     * @return $this
     */
    public function initSearchaniseRequest()
    {
        $this->searchaniseRequest = $this->requestFactory->create();

        return $this;
    }

    /**
     * Returns searchanise request
     *
     * @return SeRequest|null
     */
    public function getSearchaniseRequest()
    {
        return $this->searchaniseRequest;
    }

    /**
     * Set current request
     *
     * @param SeRequest $request
     */
    public function setSearchaniseRequest(SeRequest $request)
    {
        $this->searchaniseRequest = $request;
    }

    /**
     * Set current type
     *
     * @param string $type
     */
    public function setSearchaniseCurentType($type = null)
    {
        $this->searchaniseCurentType = $type;
    }

    /**
     * Returns current type
     *
     * @return string
     */
    public function getSearchaniseCurentType()
    {
        return $this->searchaniseCurentType;
    }

    /**
     * Get disable text
     *
     * @return boolean
     */
    public function getDisableText()
    {
        if (empty($this->disableText)) {
            $this->disableText = $this->_getRequest()->getParam(self::DISABLE_VAR_NAME, '');
        }

        return $this->disableText;
    }

    /**
     *  Checks if the text is disabled
     *
     * @return boolean
     */
    public function checkEnabled()
    {
        return ($this->getDisableText() != self::DISABLE_KEY);
    }

    /**
     * Get results from path
     *
     * @param  int|null $store_id Store identifier
     *
     * @return string
     */
    public function getResultsFormPath($store_id = null)
    {
        $store = $this->storeManager->getStore($store_id);

        return $store->getUrl('', ['_secure' => $store->isCurrentlySecure()]) . 'searchanise/result';
    }

    /**
     * Checks debug
     *
     * @param  bool $checkPrivateKey
     *
     * @return bool
     */
    public function checkDebug($checkPrivateKey = false)
    {
        $checkDebug = ($this->getDebugText() == self::DEBUG_KEY);

        if ($checkDebug && $checkPrivateKey) {
            $checkDebug = $checkDebug && $this->checkPrivateKey();
        }

        return $checkDebug || $this->configuration->getIsDebugEnabled();
    }

    /**
     * Checks if visual
     *
     * @return bool
     */
    public function checkVisual()
    {
        $checkVisual = ($this->_getRequest()->getParam(self::VISUAL_VAR_NAME) == self::VISUAL_KEY);

        return $checkVisual;
    }

    /**
     * Get debug text
     *
     * @return string
     */
    public function getDebugText()
    {
        if (empty($this->debugText)) {
            $this->debugText = $this->_getRequest()->getParam(self::DEBUG_VAR_NAME, '');
        }

        return $this->debugText;
    }

    /**
     * checks if the private key exists
     *
     * @return bool
     */
    public function checkPrivateKey()
    {
        static $check;

        if (!isset($check)) {
            $parentPrivateKey = $this->_getRequest()->getParam(self::PARENT_PRIVATE_KEY);

            if ((empty($parentPrivateKey))
                || ($this->configuration->getValue(Configuration::XML_PATH_PARENT_PRIVATE_KEY) != $parentPrivateKey)
            ) {
                $check = false;
            } else {
                $check = true;
            }
        }

        return $check;
    }

    /**
     * Main execute funtion
     *
     * @param array $searchRequest
     *
     * @return SeRequest
     * @throws \Exception
     */
    public function search(array $searchRequest)
    {
        $searchParams = $this->buildSearchParamsFromRequest(
            $searchRequest['type'],
            $searchRequest['request']
        );

        if (empty($searchParams)) {
            return null;
        }

        $request = $this
            ->initSearchaniseRequest()
            ->getSearchaniseRequest()
            ->setStore($this->storeManager->getStore())
            ->setSearchaniseCurentType($searchRequest['type'])
            ->setSearchParams($searchParams)
            ->sendSearchRequest();

        $this->setSearchaniseRequest($request);
        $this->renderSuggestions();

        return $request;
    }

    /**
     * Construct Searchanise search params from request data
     *
     * @param string $requestType
     * @param array $request
     *
     * @return array
     */
    private function buildSearchParamsFromRequest($requestType, array $request)
    {
        $searchRequest = [];

        $request = array_merge([
            'query' => '',
            'filters' => [],
            'queryFilters' => [],
            'orders' => [],
            'pageSize' => 9,
            'curPage' => 1,
        ], $request);

        if (!in_array($requestType, self::$searchaniseTypes)) {
            $requestType = self::TEXT_FIND;
        }

        if ($requestType == self::TEXT_TEST) {
            $searchRequest['facets']               = 'true';
            $searchRequest['suggestions']          = 'true';
            $searchRequest['query_correction']     = 'false';
            $searchRequest['pages']                = 'true';
            $searchRequest['pagesMaxResults']      = 10;
            $searchRequest['categories']           = 'true';
            $searchRequest['categoriesMaxResults'] = 10;
        } elseif ($requestType == self::TEXT_ADVANCED_FIND) {
            $searchRequest['facets']           = 'true';
            $searchRequest['suggestions']      = 'false';
            $searchRequest['query_correction'] = 'false';
        } else {
            $searchRequest['facets']           = 'true';
            $searchRequest['suggestions']      = 'true';
            $searchRequest['query_correction'] = 'false';
        }

        $searchRequest['restrictBy']['status'] = '1';
        $searchRequest['union']['price']['min'] = ApiSeHelper::getLabelForPricesUsergroup();

        if (!$this->configuration->getIsShowOutOfStockProducts()) {
            $searchRequest['restrictBy']['is_in_stock'] = '1';
        }

        if ($requestType == self::TEXT_FIND) {
            $searchRequest['q'] = strtolower(trim($request['query']));
        }

        // Query filters
        if (!empty($request['queryFilters'])) {
            // TODO: Adds query filters here
        }

        // Filters
        foreach ($request['filters'] as $filterName => $condition) {
            $filterName = $this->mapFieldName($filterName);

            if (is_array($condition)) {
                if (isset($condition['like'])) {
                    // Like condition
                    $searchRequest['queryBy'][$filterName] = $condition['like'];
                } elseif (isset($condition['from']) || isset($condition['to'])) {
                    // Range condition
                    $searchRequest['restrictBy'][$filterName] =
                        (isset($condition['from']) ? $condition['from'] : '')
                        . ','
                        . (isset($condition['to']) ? $condition['to'] : '');
                } elseif (isset($condition['in'])) {
                    $searchRequest['restrictBy'][$filterName] = implode('|', $condition['in']);
                } elseif (isset($condition['in_set'])) {
                    $searchRequest['restrictBy'][$filterName] = implode('|', $condition['in_set']);
                } else {
                    // OR condition
                    $searchRequest['restrictBy'][$filterName] = implode('|', $condition);
                }
            } else {
                $searchRequest['restrictBy'][$filterName] = $condition;
            }
        }

        // Orders
        if (!empty($request['orders'])) {
            foreach ($request['orders'] as $sortBy => $order) {
                $searchRequest['sortBy']    = $this->mapFieldName($sortBy);
                $searchRequest['sortOrder'] = $order;
                // Ignore other conditions if exist
                break;
            }
        } else {
            $searchRequest['sortBy'] = 'relevance';
        }

        // Pagination params.
        $size = $request['pageSize'];
        $from = $size * (max(1, $request['curPage']) - 1);

        $searchRequest['startIndex'] = $from;
        $searchRequest['maxResults'] = $size;

        if (
            $requestType == self::TEXT_FIND
            && empty($searchRequest['q'])
        ) {
            // Do not process search if query not set
            return [];
        }

        return $searchRequest;
    }

    /**
     * Render suggestions
     *
     * @return $this
     */
    private function renderSuggestions()
    {
        if ($this->searchaniseRequest) {
            $suggestions = $this->searchaniseRequest->getSuggestions();
            $totalProducts = $this->searchaniseRequest->getTotalProducts();
            $suggestionsMaxResults = ApiSeHelper::getSuggestionsMaxResults();

            if (!empty($suggestions) && $totalProducts == 0) {
                $message = __('Did you mean: ');
                $count_sug = 0;
                $link = [];
                $catalogSearchHelper = ObjectManager::getInstance()->get(\Magento\CatalogSearch\Helper\Data::class);
                $textFind = $catalogSearchHelper->getEscapedQueryText();

                foreach ($suggestions as $k => $sug) {
                    if (!empty($sug) && mb_strtolower($sug) != mb_strtolower($textFind)) {
                        $link[] = '<a href="' . $this->getUrlSuggestion($sug). '">' . $sug .'</a>';
                        $count_sug++;
                    }

                    if ($count_sug >= $suggestionsMaxResults) {
                        break;
                    }
                }

                if (!empty($link)) {
                    $catalogSearchHelper->addNoteMessage($message . implode(', ', $link) . '?');
                }
            }
        }

        return $this;
    }

    /**
     * Returns raw suggestions data
     *
     * @return array
     */
    public function getRawSuggestions()
    {
        if (!$this->searchaniseRequest) {
            return [];
        }

        $rawSuggestions = [];
        $suggestions = $this->searchaniseRequest->getSuggestions();

        $catalogSearchHelper = ObjectManager::getInstance()->get(\Magento\CatalogSearch\Helper\Data::class);
        $textFind = $catalogSearchHelper->getEscapedQueryText();

        foreach ($suggestions as $k => $sug) {
            if (!empty($sug) && mb_strtolower($sug) != mb_strtolower($textFind)) {
                $rawSuggestions[] = $sug;
            }
        }

        return $rawSuggestions;
    }

    /**
     * Returns raw documents for internal search
     *
     * @param string $key Primary key
     *
     * @return array
     */
    public function getRawDocuments($idKey = 'entity_id', $scoreKey = 'score')
    {
        if (!$this->searchaniseRequest) {
            return [];
        }

        $rawDocuments = [];
        $docIds = $this->searchaniseRequest->getProductIds();

        foreach ($docIds as $k => $docId) {
            $rawDocuments[] = [
                $idKey    => $docId,
                $scoreKey => count($docIds) - $k,
            ];
        }

        return $rawDocuments;
    }

    public function getRawAggregationsFromFacets()
    {
        if (!$this->searchaniseRequest) {
            return [];
        }

        $aggregations = [];
        $facets = $this->searchaniseRequest->getFacets();

        if (!empty($facets)) {
            foreach ($facets as $facet) {
                $aggregation = $buckets = [];

                if ($facet['attribute'] == 'price') {
                    // Hack for price, since Searchanise returns price in 60,70 format but magento requires 60_70
                    $buckets = array_map(function ($metrics) {
                        return [
                            'value' => implode('_', explode(',', $metrics['value'])),
                            'count' => $metrics['count']
                        ];
                    }, $facet['buckets']);
                } else {
                    $buckets = $facet['buckets'];
                }

                if (!empty($buckets)) {
                    foreach ($buckets as $metrics) {
                        $aggregation[$metrics['value']] = [
                            'value' => $metrics['value'],
                            'count' => $metrics['count'],
                        ];
                    }
                }

                $aggregationName = $facet['attribute'] == 'category_ids' ? 'category' : $facet['attribute'];

                if (!preg_match("/reviews/", $aggregationName)) {
                    $aggregations[$aggregationName . \Magento\CatalogSearch\Model\Search\RequestGenerator::BUCKET_SUFFIX] = $aggregation;
                }
            }
        }

        return $aggregations;
    }

    /**
     * Returns facet data from Searchanise response
     *
     * @param string $field
     *
     * @return array
     */
    public function getFacetedData($field)
    {
        if (!$this->searchaniseRequest) {
            return [];
        }

        $result = $facets = $buckets = [];
        $facets = $this->searchaniseRequest->getFacets();

        if (!empty($facets)) {
            foreach ($facets as $facet) {
                if ($facet['attribute'] == 'category_ids' && $field == 'category') {
                    $buckets = $facet['buckets'];
                } elseif ($facet['attribute'] == 'price' && $field == 'price') {
                    // Hack for price, since Searchanise returns price in 60,70 format but magento requires 60_70
                    $buckets = array_map(function ($metrics) {
                        return [
                            'value' => implode('_', explode(',', $metrics['value'])),
                            'count' => $metrics['count']
                        ];
                    }, $facet['buckets']);
                } elseif ($facet['attribute'] == $field) {
                    $buckets = $facet['buckets'];
                }
            }
        }

        if ($buckets) {
            foreach ($buckets as $metrics) {
                $result[$metrics['value']] = [
                    'value' => $metrics['value'],
                    'count' => $metrics['count'],
                ];
            }
        }

        return $result;
    }

    /**
     * Returns suggestion link
     *
     * @param  string $suggestion
     *
     * @return string
     */
    private function getUrlSuggestion($suggestion)
    {
        $query = [
            'q'                         => $suggestion,
            $this->pager->getPageVarName()    => null // exclude current page from urls
        ];

        return $this->storeManager->getStore()->getUrl(
            '*/*/*',
            [
                '_current'      => true,
                '_use_rewrite'  => true,
                '_query'        => $query
            ]
        );
    }

    /**
     * Convert standard field name to ES fieldname.
     * (eg. category_ids => category).
     *
     * @param string $fieldName Field name to be mapped.
     *
     * @return string
     */
    public function mapFieldName($fieldName)
    {
        if (isset($this->fieldNameMapping[$fieldName])) {
            $fieldName = $this->fieldNameMapping[$fieldName];
        }

        return $fieldName;
    }

    /**
     * Get product page limit
     *
     * @return int
     */
    public function getLimit()
    {
        $maxPageSize = $this->configuration->getMaxPageSize();
        $limit = ObjectManager::getInstance()
            ->get(\Magento\Catalog\Block\Product\ProductList\Toolbar::class)
            ->getLimit();

        return (int)min($limit, $maxPageSize);
    }

    /**
     * Returns current page
     *
     * @return int
     */
    public function getCurrentPage()
    {
        $currentPage = ObjectManager::getInstance()
            ->get(\Magento\Catalog\Block\Product\ProductList\Toolbar::class)
            ->getCurrentPage();

        return max(1, $currentPage);
    }

    /**
     * Run emulation for specific store view
     *
     * @param StoreModel $store
     *
     * @return $this
     */
    public function startEmulation(StoreModel $store = null)
    {
        if ($this->runEmulation) {
            return $this;
        }

        if ($store) {
            $this->storeManager->setCurrentStore($store);
            $this->initialEnvironmentInfo = $this->appEmulation->startEnvironmentEmulation($store->getId(), AppArea::AREA_FRONTEND, true);
        } else {
            $this->storeManager->setCurrentStore(0);
            $this->initialEnvironmentInfo = $this->appEmulation->startEnvironmentEmulation(0, AppArea::AREA_FRONTEND, true);
        }

        $this->runEmulation = true;

        return $this;
    }

    /**
     * Stop current emulation
     *
     * @return $this
     */
    public function stopEmulation()
    {
        if ($this->runEmulation && $this->initialEnvironmentInfo) {
            $this->appEmulation->stopEnvironmentEmulation($this->initialEnvironmentInfo);
            $this->runEmulation = false;
            $this->initialEnvironmentInfo = null;
        }

        return $this;
    }
}
