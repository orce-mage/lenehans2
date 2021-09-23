<?php

namespace Searchanise\SearchAutocomplete\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Cms\Model\PageFactory;
use Magento\Store\Model\Store as StoreModel;
use Magento\Cms\Model\Page as PageModel;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

use Searchanise\SearchAutocomplete\Helper\ApiSe as ApiSeHelper;
use Searchanise\SearchAutocomplete\Helper\Logger as SeLogger;
use Searchanise\SearchAutocomplete\Model\QueueFactory;
use Searchanise\SearchAutocomplete\Model\Configuration;

class Queue extends AbstractModel
{
    const NOT_DATA                  = 'N';

    const ACT_PHRASE                = 'phrase';

    const ACT_UPDATE_PAGES          = 'update_pages';
    const ACT_UPDATE_PRODUCTS       = 'update_products';
    const ACT_UPDATE_ATTRIBUTES     = 'update_attributes';
    const ACT_UPDATE_CATEGORIES     = 'update_categories';

    const ACT_DELETE_PAGES          = 'delete_pages';
    const ACT_DELETE_PAGES_ALL      = 'delete_pages_all';
    const ACT_DELETE_PRODUCTS       = 'delete_products';
    const ACT_DELETE_PRODUCTS_ALL   = 'delete_products_all';
    const ACT_DELETE_FACETS         = 'delete_facets';
    const ACT_DELETE_FACETS_ALL     = 'delete_facets_all';
    const ACT_DELETE_ATTRIBUTES     = 'delete_attributes';     // not used
    const ACT_DELETE_ATTRIBUTES_ALL = 'delete_attributes_all'; // not used
    const ACT_DELETE_CATEGORIES     = 'delete_categories';
    const ACT_DELETE_CATEGORIES_ALL = 'delete_categories_all';

    const ACT_PREPARE_FULL_IMPORT   = 'prepare_full_import';
    const ACT_START_FULL_IMPORT     = 'start_full_import';
    const ACT_GET_INFO              = 'update_info';
    const ACT_END_FULL_IMPORT       = 'end_full_import';

    public static $mainActionTypes = [
        self::ACT_PREPARE_FULL_IMPORT,
        self::ACT_START_FULL_IMPORT,
        self::ACT_END_FULL_IMPORT,
    ];

    public static $actionTypes = [
        self::ACT_PHRASE,

        self::ACT_UPDATE_PAGES,
        self::ACT_UPDATE_PRODUCTS,
        self::ACT_UPDATE_CATEGORIES,
        self::ACT_UPDATE_ATTRIBUTES,

        self::ACT_DELETE_PAGES,
        self::ACT_DELETE_PAGES_ALL,
        self::ACT_DELETE_PRODUCTS,
        self::ACT_DELETE_PRODUCTS_ALL,
        self::ACT_DELETE_FACETS,
        self::ACT_DELETE_FACETS_ALL,
        self::ACT_DELETE_ATTRIBUTES,
        self::ACT_DELETE_ATTRIBUTES_ALL,
        self::ACT_DELETE_CATEGORIES,
        self::ACT_DELETE_CATEGORIES_ALL,

        self::ACT_PREPARE_FULL_IMPORT,
        self::ACT_START_FULL_IMPORT,
        self::ACT_END_FULL_IMPORT,
    ];

    const STATUS_PENDING    = 'pending';
    const STATUS_DISABLED   = 'disabled';
    const STATUS_PROCESSING = 'processing';

    public static $statusTypes = [
        self::STATUS_PENDING,
        self::STATUS_DISABLED,
        self::STATUS_PROCESSING,
    ];

    /**
     * @var ApiSeHelper
     */
    private $apiSeHelper;

    /**
     * @var SeLogger
     */
    private $loggerHelper;

    /**
     * @var QueueFactory
     */
    private $queueFactory;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        ApiSeHelper $apiSeHelper,
        SeLogger $loggerHelper,
        QueueFactory $queueFactory,
        ProductFactory $productFactory,
        CategoryFactory $categoryFactory,
        Configuration $configuration,
        PageFactory $pageFactory,
        ResourceConnection $resourceConnection,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->apiSeHelper = $apiSeHelper;
        $this->loggerHelper = $loggerHelper;
        $this->queueFactory = $queueFactory;
        $this->productFactory = $productFactory;
        $this->categoryFactory = $categoryFactory;
        $this->configuration = $configuration;
        $this->pageFactory = $pageFactory;
        $this->resourceConnection = $resourceConnection;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init(\Searchanise\SearchAutocomplete\Model\ResourceModel\Queue::class);
    }

    /**
     * Adds action to the queue
     *
     * @param  string $action         Action name
     * @param  mixed  $data           Action data
     * @param  number $currentStoreId Current store
     * @param  array  $storeIds       List of store identifiers to add the action
     *
     * @return bool
     */
    public function addAction($action, $data = null, $currentStoreId = null, $storeIds = null)
    {
        if (in_array($action, self::$actionTypes)) {
            if (!$this->apiSeHelper->checkParentPrivateKey()
                || (!$this->configuration->getIsRealtimeSyncMode()
                && !in_array($action, self::$mainActionTypes))
            ) {
                return false;
            }

            $data = $this->apiSeHelper->serialize((array)$data);
            $data = [$data];

            $stores = $this->apiSeHelper->getStores(empty($storeIds) ? (array)$currentStoreId : (array)$storeIds);

            if ($action == self::ACT_PREPARE_FULL_IMPORT && !empty($currentStoreId)) {
                // Truncate queue for all
                $this->queueFactory->create()->clearActions($currentStoreId);
            }

            if ($this->apiSeHelper->getStatusModule() != 'Y') {
                if (!in_array($action, self::$mainActionTypes)) {
                    return false;
                }
            }

            $actionStoreIds = [];

            foreach ($stores as $keyStore => $store) {
                if ($store instanceof StoreModel) {
                    $actionStoreIds[] = $store->getId();
                } else {
                    $actionStoreIds[] = (int)$store;
                }
            }

            // Remove duplicate actions
            if ($action != self::ACT_PHRASE) {
                // Remove duplicate actions
                $exist_actions = $this->queueFactory
                    ->create()
                    ->getCollection()
                    ->addFilter('status', self::STATUS_PENDING)
                    ->addFilter('action', $action)
                    ->addFilter('data', $data)
                    ->addFieldToFilter('store_id', ['in' => $actionStoreIds])
                    ->load();

                $exist_actions->walk('delete');
            }

            // Add new actions
            foreach ($data as $d) {
                foreach ($actionStoreIds as $storeId) {
                    $queueData = [
                        'action'    => $action,
                        'data'      => $d,
                        'store_id'  => $storeId,
                    ];

                    // TODO: Deprecated
                    $this->setData($queueData)->save();
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Category has been updated
     *
     * @param  CategoryModel $category Category object
     * @param  string        $action   Action name
     *
     * @return boolean
     */
    public function addActionCategory(CategoryModel $category = null, $action = self::ACT_UPDATE_CATEGORIES)
    {
        $apiCategoryHelper = ObjectManager::getInstance()
            ->get(\Searchanise\SearchAutocomplete\Helper\ApiCategories::class);

        if ($category) {
            // Fixme in the future
            // need get $currentIsActive for all stores because each store can have his value of IsActive for category.
            $currentIsActive = $category->getIsActive();
            $storeId = $category->getStoreId();

            $prevCategory = $this->categoryFactory->create()
                ->setStoreId($category->getStoreId())
                ->load($category->getId());

            if ($action == self::ACT_DELETE_CATEGORIES) {
                if ($prevCategory && $apiCategoryHelper->getIsCategoryActive($prevCategory)) {
                    // Delete in all stores
                    $this->addAction($action, $category->getId());
                }
            } elseif ($action == self::ACT_UPDATE_CATEGORIES) {
                if ($currentIsActive) {
                    $this->addAction($action, $category->getId(), null, $storeId);
                } elseif ($prevCategory && $apiCategoryHelper->getIsCategoryActive($prevCategory) != $currentIsActive) {
                    // Delete need for all stores
                    $this->addAction(self::ACT_DELETE_CATEGORIES, $category->getId());
                }
            }
            // end fixme
        }

        return true;
    }

    /**
     * Adds products to the queue
     *
     * @param  ProductCollection $products
     *
     * @return Queue
     */
    public function addActionProducts(
        ProductCollection $products,
        $action = self::ACT_UPDATE_PRODUCTS
    ) {
        if (!empty($products)) {
            $allProductIds = array_filter($products->getAllIds());

            if (!empty($allProductIds)) {
                $allProductIds = array_chunk($allProductIds, $this->configuration->getProductsPerPass());

                foreach ($allProductIds as $chunkIds) {
                    $this->addAction($action, $chunkIds);
                }
            }
        }

        return $this;
    }

    /**
     * Add action page
     *
     * @param  PageModel $page   Magento page object
     * @param  string    $action Action
     *
     * @return bool
     */
    public function addActionPage(PageModel $page, $action = self::ACT_UPDATE_PAGES)
    {
        if (!empty($page)) {
            // Fixme in the future
            // need get $currentIsActive for all stores because each store can have his value of IsActive for page.
            $currentIsActive = $page->getIsActive();
            $storeIds = $page->getStoreId();

            if (is_array($storeIds) && count($storeIds) == 1) {
                $storeIds = current($storeIds);
            }

            $prevPage = $this->pageFactory->create()
                // Fixme in the future
                // need check for correct
                ->setStoreId($page->getStoreId())
                //->addStoreFilter($page->getStoreId())
                // end fixme
                ->load($page->getId());

            if ($action == self::ACT_DELETE_PAGES) {
                if ($prevPage && $prevPage->getIsActive()) {
                    // Delete in all stores
                    $this->queueFactory->create()->addAction($action, $page->getId());
                }
            } elseif ($action == self::ACT_UPDATE_PAGES) {
                if ($currentIsActive) {
                    $this->queueFactory->create()->addAction($action, $page->getId(), null, $storeIds);
                } else {
                    $prevIsActive = $prevPage->getIsActive();

                    if ($prevIsActive != $currentIsActive) {
                        // Delete need for all stores
                        $this->queueFactory->create()->addAction(self::ACT_DELETE_PAGES, $page->getId());
                    }
                }
            }
            // end fixme
        }

        return $this;
    }

    /**
     * Adds products to the queue
     *
     * @param array $productIds Product ids
     *
     * @return Queue
     */
    public function addActionProductIds($productIds, $action = self::ACT_UPDATE_PRODUCTS)
    {
        if (!empty($productIds)) {
            $productCollection = $this->productFactory
                ->create()
                ->getCollection()
                ->addAttributeToSelect('entity_id')
                ->addIdFilter($productIds)
                ->load();

            if (!empty($productCollection)) {
                $this->addActionProducts($productCollection, $action);
            }
        }

        return $this;
    }

    /**
     * Clear all store actions from queue for the store
     *
     * @param  int $storeId
     *
     * @return bool
     */
    public function clearActions($storeId = null)
    {
        $db = $this->resourceConnection->getConnection('core_write');
        $tableName = $this->resourceConnection->getTableName('searchanise_queue');

        try {
            if (empty($storeId)) {
                $db->query('TRUNCATE ' . $tableName);
            } else {
                $db->query('DELETE FROM ' . $tableName . ' WHERE store_id = ' . $storeId);
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Delete product if the product has been moved before stores
     *
     * @param ProductModel $product
     *
     * @return Queue
     */
    public function addActionDeleteProductFromOldStore(ProductModel $product = null)
    {
        if ($product && $product->getId()) {
            $storeIds = $product->getStoreIds();

            // TODO: Deprecated
            $product_old = $this->productFactory->create()->load($product->getId());

            if (!empty($product_old)) {
                $storeIdsOld = $product_old->getStoreIds();

                if (!empty($storeIdsOld)) {
                    foreach ($storeIdsOld as $k => $storeIdOld) {
                        if ((empty($storeIds)) || (!in_array($storeIdOld, $storeIds))) {
                            $this->addAction(self::ACT_DELETE_PRODUCTS, $product->getId(), null, $storeIdOld);
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Delete product
     *
     * @param ProductModel $product
     *
     * @return Queue
     */
    public function addActionDeleteProduct(ProductModel $product = null)
    {
        if ($product && $product->getId()) {
            $storeIds = $product->getStoreIds();

            if (!empty($storeIds)) {
                foreach ($storeIds as $k => $storeId) {
                    $this->addAction(self::ACT_DELETE_PRODUCTS, $product->getId(), null, $storeId);
                }
            }
        }

        return $this;
    }

    /**
     * Delete products by ids
     *
     * @param array $productIds Product Ids to delete
     * @param array $storeIds   Products stores
     *
     * @return Queue
     */
    public function addActionDeleteProductIds(array $productIds, $storeIds = null)
    {
        if (!empty($productIds)) {
            if (!empty($storeIds)) {
                $storeIds = (array)$storeIds;
            }

            $allProductIds = array_chunk($productIds, $this->configuration->getProductsPerPass());

            foreach ($allProductIds as $chunkIds) {
                $this->addAction(self::ACT_DELETE_PRODUCTS, $chunkIds, null, $storeIds);
            }
        }

        return $this;
    }

    /**
     * Product has been updated
     *
     * @param ProductModel $product
     * @param  array|int   $storeIds
     *
     * @return Queue
     */
    public function addActionUpdateProduct(ProductModel $product = null, $storeIds = null)
    {
        if ($product && $product->getId()) {
            if (!empty($storeIds)) {
                if (!is_array($storeIds)) {
                    $storeIds = (array) $storeIds;
                }
            } else {
                $storeIds = $product->getStoreIds();

                if (empty($storeIds)) {
                    $storeIds = [];
                    $stores = $this->apiSeHelper->getStores();

                    foreach ($stores as $store) {
                        $storeIds[] = $store->getId();
                    }
                }
            }

            $this->addAction(self::ACT_UPDATE_PRODUCTS, $product->getId(), null, $storeIds);
        }

        return $this;
    }

    /**
     * Returns next queue as array
     *
     * @param int  $queueId
     * @param bool $flagIgnoreError
     *
     * @return array
     */
    public function getNextQueueArray($queueId = null, $flagIgnoreError = false)
    {
        $collection = $this->queueFactory
            ->create()
            ->getCollection()
            ->addOrder('queue_id', 'ASC')
            ->setPageSize(1);

        if (!empty($queueId)) {
            $collection = $collection->addFieldToFilter('queue_id', ['gt' => $queueId]);
        }

        // TODO: Not use in current version.
        if ($flagIgnoreError) {
            $collection = $collection->addFieldToFilter(
                'error_count',
                [
                'lt' => $this->configuration->getMaxErrorCount()
                ]
            );
        }

        return $collection->load()->toArray();
    }

    /**
     * Delete all queues for stores
     *
     * @param  array $storeIds Store identifier
     *
     * @return bool
     */
    public function deleteKeys(array $storeIds = [])
    {
        if (empty($storeIds)) {
            // Process all stores
            $stores = $this->apiSeHelper->getStores();

            foreach ($stores as $store) {
                $storeIds[] = $store->getId();
            }
        }

        if (!empty($storeIds)) {
            foreach ($storeIds as $storeId) {
                $queue = $this
                    ->getCollection()
                    ->addFilter('store_id', $storeId)
                    ->toArray();

                if (!empty($queue['items'])) {
                    foreach ($queue['items'] as $item) {
                        try {
                            $this->queueFactory->create()->load($item['queue_id'])->delete();
                        } catch (\Exception $e) {
                            $this->loggerHelper->log($e->getMessage());
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * Returns next queue
     *
     * @return Queue;
     */
    public function getNextQueue($queueId = null)
    {
        $queueArr = $this->getNextQueueArray($queueId);

        if (!empty($queueArr['items'])) {
            $q = reset($queueArr['items']);
        }

        return !empty($q) ? $q : [];
    }

    /**
     * Returns total items count in queue
     *
     * @return int
     */
    public function getTotalItems()
    {
        // TODO: count() method loads all items. Can be optimize in future
        return (int)$this->queueFactory
            ->create()
            ->getCollection()
            ->count();
    }

    /**
     * Checks if action is update
     *
     * @param string $action
     *
     * @return bool
     */
    public static function isUpdateAction($action)
    {
        $isUpdate = false;

        if ($action == self::ACT_UPDATE_PAGES
            || $action == self::ACT_UPDATE_PRODUCTS
            || $action == self::ACT_UPDATE_ATTRIBUTES
            || $action == self::ACT_UPDATE_CATEGORIES
        ) {
            $isUpdate = true;
        }

        return $isUpdate;
    }

    /**
     * Checks delete action
     *
     * @param string $action
     *
     * @return bool
     */
    public static function isDeleteAction($action)
    {
        $isDelete = false;

        if ($action == self::ACT_DELETE_PAGES
            || $action == self::ACT_DELETE_PRODUCTS
            || $action == self::ACT_DELETE_ATTRIBUTES
            || $action == self::ACT_DELETE_FACETS
            || $action == self::ACT_DELETE_CATEGORIES
        ) {
            $isDelete = true;
        }

        return $isDelete;
    }

    /**
     * Checks delete actions
     *
     * @param string $action
     *
     * @return bool
     */
    public static function isDeleteAllAction($action)
    {
        $isDeleteAll = false;

        if ($action == self::ACT_DELETE_PAGES_ALL
            || $action == self::ACT_DELETE_PRODUCTS_ALL
            || $action == self::ACT_DELETE_ATTRIBUTES_ALL
            || $action == self::ACT_DELETE_FACETS_ALL
            || $action == self::ACT_DELETE_CATEGORIES_ALL
        ) {
            $isDeleteAll = true;
        }

        return $isDeleteAll;
    }

    /**
     * Returns api type
     *
     * @param string $action
     *
     * @return string
     */
    public static function getAPITypeByAction($action)
    {
        switch ($action) {
            case self::ACT_DELETE_PRODUCTS:
            case self::ACT_DELETE_PRODUCTS_ALL:
                $type = 'items';
                break;

            case self::ACT_DELETE_CATEGORIES:
            case self::ACT_DELETE_CATEGORIES_ALL:
                $type = 'categories';
                break;

            case self::ACT_DELETE_PAGES:
            case self::ACT_DELETE_PAGES_ALL:
                $type = 'pages';
                break;

            case self::ACT_DELETE_FACETS:
            case self::ACT_DELETE_FACETS_ALL:
                $type = 'facets';
                break;

            default:
                $type = '';
        }

        return $type;
    }
}
