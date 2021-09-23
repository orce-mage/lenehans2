<?php

namespace Searchanise\SearchAutocomplete\Plugins;

use Magento\Framework\Search\RequestInterface;
use Magento\Framework\App\ObjectManager;

use Searchanise\SearchAutocomplete\Helper\Data as SearchaniseHelper;
use Searchanise\SearchAutocomplete\Model\Request as SearchaniseRequest;
use Searchanise\SearchAutocomplete\Helper\ApiSe as ApiSeHelper;
use Searchanise\SearchAutocomplete\Helper\Logger as SeLogger;
use Searchanise\SearchAutocomplete\Helper\RequestMapper as RequestMapper;

class SearchaniseSearchAdapter
{
    private static $emptyRawAggregations = [
        'price_bucket' => [],
        'category_bucket' => [
            'buckets' => [],
        ],
    ];

    /**
     * @var SearchaniseRequest
     */
    private $searchRequest = null;

    /**
     * @var SearchaniseHelper
     */
    private $searchaniseHelper;

    /**
     * @var ApiSeHelper
     */
    private $apiSeHelper;

    /**
     * SeLogger
     */
    private $loggerHelper;

    public function __construct(
        SearchaniseHelper $searchaniseHelper,
        ApiSeHelper $apiSeHelper,
        SeLogger $loggerHelper,
        RequestMapper $seMapper
    ) {
        $this->searchaniseHelper = $searchaniseHelper;
        $this->apiSeHelper = $apiSeHelper;
        $this->loggerHelper = $loggerHelper;
        $this->seMapper = $seMapper;
    }

    /**
     * @param ElasticSearchAdapter $instance
     * @param callable             $fn
     * @param RequestInterface     $request
     *
     * @return Magento\Framework\Search\Response\QueryResponse
     */
    public function aroundQuery($instance, callable $fn, RequestInterface $request)
    {
        if (!$this->getIsSearchaniseSearchEnabled($request)) {
            return $fn($request);
        }

        $query = $this->seMapper->buildQuery($request);
        $processed = $this->execute($request->getName(), $query);

        if ($processed === true) {
            if ($this->searchRequest) {
                $rawDocuments = $this->searchaniseHelper->getRawDocuments('_id', '_score');
                $rawAggregations = $this->searchaniseHelper->getRawAggregationsFromFacets();
                $totalProducts = $this->searchRequest->getTotalProducts();
            } else {
                $rawDocuments = [];
                $rawAggregations = self::$emptyRawAggregations;
                $totalProducts = 0;
            }

            return $this->getResponseFactory()->create([
                'documents'    => $rawDocuments,
                'aggregations' => $rawAggregations,
                'total'        => $totalProducts,
            ]);
        }

        return $fn($request);
    }

    /**
     * Returns response factory class
     *
     * @return ResponseFactory
     */
    private function getResponseFactory()
    {
        return ObjectManager::getInstance()->get(\Magento\Elasticsearch\SearchAdapter\ResponseFactory::class);
    }

    /**
     * Check if Searchanise fulltext search is enabled
     *
     * @param RequestInterface $request
     *
     * @return bool
     */
    private function getIsSearchaniseSearchEnabled(RequestInterface $request)
    {
        return
            in_array($request->getName(), [
                'quick_search_container',
                'advanced_search_container',
            ])
            && $this->apiSeHelper->getIsSearchaniseSearchEnabled()
            && $this->searchaniseHelper->checkEnabled();
    }

    /**
     * Run Searchanise search
     *
     * @param string $requestType
     * @param array $request
     *
     * @return bool
     */
    protected function execute($requestType, array $request)
    {
        // Clear previous data
        $this->searchRequest = null;

        // Need to render debug information
        $httpResponse = ObjectManager::getInstance()
            ->get(\Magento\Framework\App\Response\Http::class);
        $this->apiSeHelper->setHttpResponse($httpResponse);

        try {
            $this->searchRequest = $this->searchaniseHelper->search([
                'type'    => $requestType,
                'request' => $request,
            ]);
        } catch (\Exception $e) {
            $this->loggerHelper->log($e->getMessage());
            $this->searchRequest = null;

            return false;
        }

        return true;
    }
}
