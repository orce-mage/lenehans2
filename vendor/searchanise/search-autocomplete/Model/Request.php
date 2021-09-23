<?php

namespace Searchanise\SearchAutocomplete\Model;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Json\Helper\Data as JsonDataHelper;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store as StoreModel;

use Searchanise\SearchAutocomplete\Helper\ApiSe as ApiSeHelper;
use Searchanise\SearchAutocomplete\Helper\Data as SearchaniseHelper;
use Searchanise\SearchAutocomplete\Helper\Logger as SeLogger;
use Searchanise\SearchAutocomplete\Model\Configuration;
use Searchanise\SearchAutocomplete\Exception\RequestException;
use Searchanise\SearchAutocomplete\Exception\ApiKeyException;

/**
 * Searchanise request model
 */
class Request extends AbstractModel
{
    const ERROR_EMPTY_API_KEY                   = 'EMPTY_API_KEY';
    const ERROR_INVALID_API_KEY                 = 'INVALID_API_KEY';
    const ERROR_TO_BIG_START_INDEX              = 'TO_BIG_START_INDEX';
    const ERROR_SEARCH_DATA_NOT_IMPORTED        = 'SEARCH_DATA_NOT_IMPORTED';
    const ERROR_FULL_IMPORT_PROCESSED           = 'FULL_IMPORT_PROCESSED';
    const ERROR_FACET_ERROR_TOO_MANY_ATTRIBUTES = 'FACET_ERROR_TOO_MANY_ATTRIBUTES';
    const ERROR_NEED_RESYNC_YOUR_CATALOG        = 'NEED_RESYNC_YOUR_CATALOG';
    const ERROR_FULL_FEED_DISABLED              = 'FULL_FEED_DISABLED';
    const ERROR_ENGINE_SUSPENDED                = 'ENGINE_SUSPENDED';

    const SEPARATOR_ITEMS = "'";

    /**
     * @var array
     */
    private $searchResult = [];

    /**
     * @var array
     */
    private $searchParams = [];

    /**
     * @var string
     */
    private $apiKey = '';

    /**
     * @var string
     */
    private $privateKey = '';

    /**
     * @var StoreModel
     */
    private $store = null;

    /**
     * @var ApiSeHelper
     */
    private $apiSeHelper;

    /**
     * @var SearchaniseHelper
     */
    private $searchaniseHelper;

    /**
     * @var SeLogger
     */
    private $loggerHelper;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var JsonDataHelper
     */
    private $jsonHelper;

    private $error = '';

    public function __construct(
        Context $context,
        Registry $registry,
        JsonDataHelper $jsonHelper,
        ApiSeHelper $apiSeHelper,
        SearchaniseHelper $searchaniseHelper,
        SeLogger $loggerHelper,
        Configuration $configuration,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->apiSeHelper = $apiSeHelper;
        $this->searchaniseHelper = $searchaniseHelper;
        $this->jsonHelper = $jsonHelper;
        $this->loggerHelper = $loggerHelper;
        $this->configuration = $configuration;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function hasError()
    {
        return !empty($this->error);
    }

    /**
     * Set request store
     *
     * @param  StoreModel $value
     *
     * @return Request
     */
    public function setStore(StoreModel $value)
    {
        $this->store = $value;

        return $this;
    }

    /**
     * Returns selected store
     *
     * @return StoreModel
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * Returns private key for current store
     *
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->apiSeHelper->getPrivateKey($this->store ? $this->store->getId() : null);
    }

    /**
     * Returns api key
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiSeHelper->getApiKey($this->store ? $this->store->getId() : null);
    }

    /**
     * Checks if api key exists
     *
     * @return boolean
     */
    public function checkApiKey()
    {
        $apiKey = $this->getApiKey();

        return !empty($apiKey);
    }

    /**
     * Checks if Searchanise result is valid
     *
     * @return bool
     */
    public function checkSearchResult()
    {
        return !empty($this->searchResult);
    }

    /**
     * Set current Searchanise result
     *
     * @param  array $value
     *
     * @return Request
     */
    public function setSearchResult(array $value = [])
    {
        $this->searchResult = $value;

        $this->setAttributesCount();

        return $this;
    }

    /**
     * Returns Searchanise result
     *
     * @return array
     */
    public function getSearchResult()
    {
        return $this->searchResult;
    }

    /**
     * Returns list of founded product ids
     *
     * @return array
     */
    public function getProductIds()
    {
        $res = $this->getSearchResult();

        return empty($res['items'])
            ? []
            : array_map(function ($item) {
                return $item['product_id'];
            }, $res['items']);
    }

    /**
     * Returns result facets
     *
     * @return array
     */
    public function getFacets()
    {
        $res = $this->getSearchResult();

        return !empty($res['facets']) ? $res['facets'] : [];
    }

    /**
     * Returns sort order
     *
     * @return array
     */
    public function getSortOrder()
    {
        $res = $this->getSearchResult();

        return [
            isset($res['sortBy']) ? $res['sortBy'] : 'relevance',
            isset($res['sortOrder']) ? $res['sortOrder'] : '',
        ];
    }

    /**
     * Returns total found products
     *
     * @return int
     */
    public function getTotalProducts()
    {
        $res = $this->getSearchResult();

        return empty($res['totalItems']) ? 0 : (int)$res['totalItems'];
    }

    /**
     * Returns suggestion list
     *
     * @return array
     */
    public function getSuggestions()
    {
        $res = $this->getSearchResult();

        return empty($res['suggestions']) ? [] : $res['suggestions'];
    }

    /**
     * Set search parameters
     *
     * @param  array $params
     *
     * @return Request
     */
    public function setSearchParams(array $params = [])
    {
        $this->searchParams = $params;

        return $this;
    }

    /**
     * Set search parameter
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return Request
     */
    public function setSearchParam($key, $value)
    {
        if (empty($this->searchParams)) {
            $this->searchParams = [];
        }

        $this->searchParams[$key] = $value;

        return $this;
    }

    /**
     * Merge search parameters
     *
     * @param string $key
     * @param array  $value
     *
     * @return Request
     */
    public function mergeSearchParam($key, array $value)
    {
        if (empty($this->searchParams)) {
            $this->searchParams = [];
        }

        $this->searchParams[$key] = array_merge($value, $this->searchParams[$key]);

        return $this;
    }

    /**
     * Returns current search parameters
     *
     * @return array
     */
    public function getSearchParams()
    {
        return $this->searchParams;
    }

    /**
     * Build search string
     *
     * @return string
     */
    protected function getStrFromParams(array $params = [], $mainKey = null)
    {
        $ret = '';

        if (!empty($params)) {
            foreach ($params as $key => $param) {
                if (is_array($param)) {
                    $ret .= $this->getStrFromParams($param, $key);
                } else {
                    if (!$mainKey) {
                        $ret .= $key . '=' . $param . '&';
                    } else {
                        $ret .= $mainKey . '[' . $key . ']=' . $param . '&';
                    }
                }
            }
        }

        return $ret;
    }

    /**
     * Search string getter
     *
     * @return string
     */
    public function getSearchParamsStr()
    {
        return $this->getStrFromParams($this->getSearchParams());
    }

    /**
     * Merge search parameters
     *
     * @param  array $new_params Search parameters to merge
     *
     * @return Request
     */
    public function mergeSearchParams(array $new_params = [])
    {
        return $this->setSearchParams(array_merge($new_params, $this->getSearchParams()));
    }

    /**
     * Unset search paramter
     *
     * @param  string $key Search parameter
     *
     * @return Request
     */
    public function unsetSearchParams($key = '')
    {
        if (isset($this->searchParams[$key])) {
            unset($this->searchParams[$key]);
        }

        return $this;
    }

    /**
     * Checks if search paramter exists
     *
     * @param  string $key Search parameters
     *
     * @return Request
     */
    public function checkSearchParams($key = '')
    {
        if (empty($this->searchParams[$key])) {
            return $this->unsetSearchParams($key);
        }

        return $this;
    }

    /**
     * Send search request to Searchanse
     *
     * @return \Searchanise\SearchAutocomplete\Model\Request
     */
    public function sendSearchRequest()
    {
        $this->error = '';
        $this->setSearchResult();

        if (!$this->checkApiKey()) {
            throw new ApiKeyException();
        }

        $default_params = [
            'items'  => 'true',
            'facets' => 'true',
            'output' => 'json',
        ];

        $this
            ->mergeSearchParams($default_params)
            ->checkSearchParams('restrictBy')
            ->checkSearchParams('union');

        $query = $this->apiSeHelper->buildQuery($this->getSearchParams());
        $this->setSearchParam('api_key', $this->getApiKey());

        if ($this->searchaniseHelper->checkDebug()) {
            $this->loggerHelper->printR(
                $this->apiSeHelper->getServiceUrl()
                . '/search?' . $this->getSearchParamsStr()
            );
            $this->loggerHelper->printR($this->getSearchParams());
        }

        if (strlen($query) > $this->configuration->getMaxSearchRequestLength()) {
            list($header, $received) = $this->apiSeHelper->httpRequest(
                \Zend_Http_Client::POST,
                $this->apiSeHelper->getServiceUrl() . '/search',
                $this->getSearchParams(),
                [],
                [],
                $this->configuration->getSearchTimeout()
            );
        } else {
            list($header, $received) = $this->apiSeHelper->httpRequest(
                \Zend_Http_Client::GET,
                $this->apiSeHelper->getServiceUrl(). '/search',
                $this->getSearchParams(),
                [],
                [],
                $this->configuration->getSearchTimeout()
            );
        }

        if (empty($received)) {
            throw new RequestException(__('Searchanise: Empty response was returned by server.'));
        }

        try {
            $result = $this->jsonHelper->jsonDecode($received);
        } catch (\Exception $e) {
            throw new RequestException(__('Searchanise: Decode response error occurs.') . ' ' . $e->getMessage());
        }

        if ($this->searchaniseHelper->checkDebug()) {
            $this->loggerHelper->printR($result);
        }

        if (isset($result['error'])) {
            switch ($result['error']) {
                case self::ERROR_EMPTY_API_KEY:
                case self::ERROR_TO_BIG_START_INDEX:
                case self::ERROR_SEARCH_DATA_NOT_IMPORTED:
                case self::ERROR_FULL_IMPORT_PROCESSED:
                case self::ERROR_FACET_ERROR_TOO_MANY_ATTRIBUTES:
                case self::ERROR_ENGINE_SUSPENDED:
                    // Nothing
                    break;

                case self::ERROR_INVALID_API_KEY:
                    if ($this->getStore()) {
                        $this->apiSeHelper->deleteKeys($this->getStore()->getId(), true);

                        if ($this->apiSeHelper->signup($this->getStore()->getId(), false)) {
                            $this->apiSeHelper->queueImport($this->getStore()->getId(), false);
                        }
                    }
                    break;

                case self::ERROR_NEED_RESYNC_YOUR_CATALOG:
                    $this->apiSeHelper->queueImport($this->getStore()->getId(), false);
                    break;

                case self::ERROR_FULL_FEED_DISABLED:
                    break;
            }

            throw new RequestException($result['error']);
        }

        if (empty($result) || !is_array($result) || !isset($result['totalItems'])) {
            throw new RequestException(__('Searchanise: Invalid search result.'));
        }

        $this->setSearchResult($result);

        return $this;
    }
}
