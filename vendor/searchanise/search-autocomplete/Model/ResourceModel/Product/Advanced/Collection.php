<?php

namespace Searchanise\SearchAutocomplete\Model\ResourceModel\Product\Advanced;

use Magento\Framework\App\ObjectManager;
use Magento\CatalogSearch\Model\ResourceModel\Advanced\Collection as AdvancedCollection;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Framework\Search\EntityMetadata;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory;
use Magento\Framework\Search\Adapter\Mysql\DocumentFactory;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorage;

use Searchanise\SearchAutocomplete\Helper\Data as SearchaniseHelper;
use Searchanise\SearchAutocomplete\Model\Request as SearchaniseRequest;
use Searchanise\SearchAutocomplete\Helper\ApiSe as ApiSeHelper;
use Searchanise\SearchAutocomplete\Helper\Logger as SeLogger;

/**
 * Searchanise advanced fulltext collection class
 */
class Collection extends AdvancedCollection
{
    /**
     * @var string Container name
     */
    protected $searchRequestName = 'advanced_search_container';

    /**
     * @var mixed Search query
     */
    private $queryText = '';

    /**
     * @var array Search filters
     */
    private $filters = [];

    /**
     * @var array Query filters
     */
    private $queryFilters = [];

    /**
     * @var array Sorting orders
     */
    private $orders = [];

    /**
     * @var int Original page size
     */
    private $originalPageSize = false;

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

    public function _construct()
    {
        // Using object manager to get instance class is not recommended
        // but we have to do that because overwrite constuction is worse idea
        $this->searchaniseHelper = ObjectManager::getInstance()
            ->get(SearchaniseHelper::class);
        $this->apiSeHelper = ObjectManager::getInstance()
            ->get(ApiSeHelper::class);
        $this->loggerHelper = ObjectManager::getInstance()
            ->get(SeLogger::class);

        return parent::_construct();
    }

    /**
     * Check if searchanise fulltext search is enabled
     *
     * @return bool
     */
    protected function getIsSearchaniseSearchEnabled()
    {
        return
            $this->apiSeHelper->getIsSearchaniseSearchEnabled()
            && $this->searchaniseHelper->checkEnabled();
    }

    /**
     * {@inheritDoc}
     */
    public function setOrder($attribute, $dir = self::SORT_ORDER_DESC)
    {
        if ($this->getIsSearchaniseSearchEnabled()) {
            $this->orders[$attribute] = $dir;
            return $this;
        } else {
            return parent::setOrder($attribute, $dir);
        }
    }

    /**
     * Reset the sort order.
     *
     * @return self
     */
    public function resetOrder()
    {
        $this->orders = [];
        return parent::resetOrder();
    }

    /**
     * {@inheritDoc}
     */
    public function setVisibility($visibility)
    {
        if ($this->getIsSearchaniseSearchEnabled()) {
            return $this->addFieldToFilter('visibility', $visibility);
        } else {
            return parent::setVisibility($visibility);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($this->getIsSearchaniseSearchEnabled()) {
            $this->filters[$field] = $condition;
        } else {
            parent::addFieldToFilter($field, $condition);
        }

        return $this;
    }

    /**
     * Filter in stock product.
     *
     * @return $this
     */
    public function addIsInStockFilter()
    {
        return $this->addFieldToFilter('is_in_stock', true);
    }

    /**
     * {@inheritDoc}
     */
    public function addAttributeToSort($attribute, $dir = self::SORT_ORDER_ASC)
    {
        if ($attribute !== 'entity_id' && $this->getIsSearchaniseSearchEnabled()) {
            $this->setOrder($attribute, $dir);
        }

        return parent::addAttributeToSort($attribute, $dir);
    }

    /**
     * Append a prebuilt query filter to the collection.
     *
     * @param mixed $queryFilter Query filter.
     *
     * @return $this
     */
    public function addQueryFilter($queryFilter)
    {
        $this->queryFilters[] = $queryFilter;
        return parent::addQueryFilter($queryFilter); // TODO: Check if method exists
    }

    /**
     * Add search query filter.
     *
     * @deprecated Replaced by setSearchQuery
     *
     * @param string $query Search query text.
     *
     * @return $this
     */
    public function addSearchFilter($query)
    {
        $this->queryText = trim($this->queryText . ' ' . $query);
        return parent::addSearchFilter($query);
    }

    /**
     * {@inheritDoc}
     */
    public function getSize()
    {
        if ($this->getIsSearchaniseSearchEnabled()) {
            if ($this->searchRequest) {
                return $this->searchRequest->getTotalProducts();
            } else {
                $this->_renderFilters();

                if ($this->searchRequest) {
                    return $this->searchRequest->getTotalProducts();
                }
            }
        }

        return parent::getSize();
    }

    /**
     * {@inheritDoc}
     */
    public function addCategoryFilter(CategoryModel $category)
    {
        if ($this->getIsSearchaniseSearchEnabled()) {
            $categoryId = $category;

            if (is_object($category)) {
                $categoryId = $category->getId();
            }

            $this->addFieldToFilter('category_ids', $categoryId);
            $this->_productLimitationFilters['category_ids'] = $categoryId;
        } else {
            parent::addCategoryFilter($category);
        }

        return $this;
    }

    /**
     * Return field faceted data from faceted search result.
     *
     * @param string $field Facet field.
     *
     * @return array
     */
    public function getFacetedData($field)
    {
        $this->_renderFilters();

        if (!$this->searchRequest) {
            return parent::getFacetedData($field);
        }

        return $this->searchaniseHelper->getFacetedData($field);
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * {@inheritdoc}
     */
    protected function _renderFiltersBefore()
    {
        if (empty($this->queryText)) {
            $this->queryText = ObjectManager::getInstance()
                ->get(\Magento\Search\Model\QueryFactory::class)
                ->get()
                ->getQueryText();
        }

        $processed = $this->execute($this->searchRequestName, [
            'query'        => $this->queryText,
            'filters'      => $this->filters,
            'queryFilters' => $this->queryFilters,
            'orders'       => $this->orders,
            'pageSize'     => $this->_pageSize ? $this->_pageSize : $this->searchaniseHelper->getLimit(),
            'curPage'      => $this->searchaniseHelper->getCurrentPage(),
        ]);

        if ($processed === true) {
            // Prepare documents for cacheing
            $documents = [];

            try {
                $entityMetadata = ObjectManager::getInstance()->create(EntityMetadata::class, ['entityId' => 'id']);
                $temporaryStorageFactory = ObjectManager::getInstance()->get(TemporaryStorageFactory::class);
                $documentFactory = ObjectManager::getInstance()->get(DocumentFactory::class, ['entityMetadata' => $entityMetadata]);
            } catch (\Exception $e) {
                // Classes unavailable, use default search
                return parent::_renderFiltersBefore();
            }

            if ($this->searchRequest) {
                $docIds = $this->searchRequest->getProductIds();
            } else {
                $docIds = [0];
            }

            foreach ($docIds as $k => $docId) {
                if (version_compare($this->apiSeHelper->getMagentoVersion(), '2.1.0', '<')) {
                    // 2.0.x versions
                    $documents[$docId] = $documentFactory->create([
                        [
                            'name'  => 'entity_id',
                            'value' => $docId,
                        ],
                        [
                            'name'  => 'score',
                            'value' => count($docIds) - $k,
                        ]
                    ]);
                } else {
                    // 2.1.0 and later
                    $documents[$docId] = $documentFactory->create([
                        'entity_id' => $docId,
                        'score'     => count($docIds) - $k,
                    ]);
                }
            }

            $temporaryStorage = $temporaryStorageFactory->create();
            $table = $temporaryStorage->storeDocuments($documents);

            $this->getSelect()->joinInner([
                    'search_result' => $table->getName(),
                ],
                'e.entity_id = search_result.' . TemporaryStorage::FIELD_ENTITY_ID,
                []
            );

            if ($this->searchRequest && $this->searchRequest->checkSearchResult()) {
                // Update product count
                $this->_totalRecords = $this->searchRequest->getTotalProducts();
            } else {
                $this->_totalRecords = 0;
            }

            return $this;
        }

        return parent::_renderFiltersBefore();
    }

    /**
     * Run searchanise search
     *
     * @param string $requestType
     * @param array $request
     *
     * @return bool
     */
    protected function execute($requestType, array $request)
    {
        if ($this->getIsSearchaniseSearchEnabled()) {
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

        return false;
    }

    /**
     * Load entities records into items
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     * @throws \Exception
     */
    public function _loadEntities($printQuery = false, $logQuery = false)
    {
        if ($this->searchRequest) {
            $docIds = $this->searchRequest->getProductIds();

            if (empty($docIds)) {
                $docIds[] = 0;
            }

            $this->getSelect()->where('e.entity_id IN (?)', ['in' => $docIds]);
            $this->originalPageSize = $this->_pageSize;
            $this->_pageSize = false;
        }

        return parent::_loadEntities($printQuery, $logQuery);
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * {@inheritDoc}
     */
    protected function _afterLoad()
    {
        if ($this->searchRequest) {
            $originalItems = $this->_items;
            $this->_items  = [];

            // Resort items according the search response.
            $docIds = $this->searchRequest->getProductIds();
            $totalProducts = $this->searchRequest->getTotalProducts();

            foreach ($docIds as $k => $documentId) {
                if (isset($originalItems[$documentId])) {
                    $originalItems[$documentId]->setDocumentScore($totalProducts - $k);
                    $this->_items[$documentId] = $originalItems[$documentId];
                }
            }

            if (false === $this->_pageSize && false !== $this->originalPageSize) {
                $this->_pageSize = $this->originalPageSize;
            }

            unset($originalItems);
        }

        return parent::_afterLoad();
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * {@inheritDoc}
     */
    protected function _renderFilters()
    {
        $this->_filters = [];
        return parent::_renderFilters();
    }

    /**
     * Add multiple fields to filter
     *
     * @param array $fields The fields to filter
     *
     * @return $this
     */
    public function addFieldsToFilter($fields)
    {
        if ($this->getIsSearchaniseSearchEnabled()) {
            if ($fields) {
                foreach ($fields as $fieldByType) {
                    foreach ($fieldByType as $attributeId => $condition) {
                        $attributeCode = $this->getEntity()->getAttribute($attributeId)->getAttributeCode();
                        $condition     = $this->cleanCondition($condition);

                        if (null !== $condition) {
                            $this->addFieldToFilter($attributeCode, $condition);
                        }
                    }
                }
            }
        } else {
            parent::addFieldsToFilter($fields);
        }

        return $this;
    }

    /**
     * Ensure proper building of condition
     *
     * @param array|string $condition The condition to apply
     *
     * @return array|string|null
     */
    private function cleanCondition($condition)
    {
        if (is_array($condition)) {
            $condition = array_filter($condition);
            if (empty($condition)) {
                $condition = null;
            }
        }
        return $condition;
    }
}
