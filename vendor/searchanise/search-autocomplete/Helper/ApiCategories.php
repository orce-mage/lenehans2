<?php

namespace Searchanise\SearchAutocomplete\Helper;

use Searchanise\SearchAutocomplete\Model\Queue;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\App\State as AppState;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Store\Model\Store as StoreModel;
use Magento\Framework\Data\Collection as DataCollection;

/**
 * Categories helper for searchanise
 */
class ApiCategories extends AbstractHelper
{
    const USE_GENERATED_URLS = true;

    // use id to hide categories
    private static $excludedCategories = [
    ];

    private static $additionalsAttrs = [];

    /**
     *
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AppState
     */
    private $appState;

    /**
     * @var CollectionFactory
     */
    private $catalogResourceModelCategoryCollectionFactory;

    /**
     * @var CategoryUrlPathGenerator
     */
    private $categoryUrlPathGenerator;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        CollectionFactory $catalogResourceModelCategoryCollectionFactory,
        CategoryRepository $categoryRepository,
        AppState $appState,
        CategoryUrlPathGenerator $categoryUrlPathGenerator
    ) {
        $this->storeManager = $storeManager;
        $this->catalogResourceModelCategoryCollectionFactory = $catalogResourceModelCategoryCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        $this->appState = $appState;
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;

        parent::__construct($context);
    }

    /**
     * Generate feed for category
     *
     * @param  CategoryModel $category  Category
     * @param  StoreModel    $store     Store
     * @param  string        $checkData Flag to check the data
     *
     * @return array
     */
    public function generateCategoryFeed(
        CategoryModel $category,
        StoreModel $store = null,
        $checkData = true
    ) {
        $item = [];

        if ($checkData
            && (empty($category)
            || !$category->getName()
            || in_array($category->getId(), self::$excludedCategories))
            || !$this->getIsCategoryActive($category, $store)
        ) {
            return $item;
        }

        try {
            $this->appState->setAreaCode('frontend');
        } catch (\Exception $e) {
            // No action is required
        }

        $item['id'] = $category->getId();
        $item['parent_id'] = $this->getParentCategoryId($category, $store);
        $item['title'] = $category->getName();
        $item['link'] = $this->getCategoryUrl($category, $store);
        $item['image_link'] = $this->getCategoryImageUrl($category, $store);
        $item['summary'] = $category->getDescription();

        return $item;
    }

    /**
     * Generate storefront category url
     *
     * @param CategoryModel $category
     * @param StoreModel $store
     *
     * @return string
     */
    public function getCategoryUrl(
        CategoryModel $category,
        StoreModel $store = null
    ) {
        if (!$store) {
            $store = $this->storeManager->getStore();
        }

        $apiSeHelper = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Searchanise\SearchAutocomplete\Helper\ApiSe::class);

        $requestParams = [
            '_nosid'  => true,
            '_secure' => $apiSeHelper->getIsUseSecureUrlsInFrontend($store->getId()),
            '_scope'  => $store->getId(),
        ];

        if (self::USE_GENERATED_URLS && $category->getUrlKey() != '') {
            $path = $this->categoryUrlPathGenerator->getUrlPathWithSuffix($category);
            $url = $apiSeHelper
                ->getStoreUrl($store->getId())
                ->getBaseUrl($requestParams) . $path;
        } else {
            $requestParams['s'] = $category->getUrlKey();
            $requestParams['id'] = $category->getId();
            $url = $apiSeHelper
                ->getStoreUrl($store->getId())
                ->getUrl('catalog/category/view', $requestParams);
        }

        return $url;
    }

    /**
     * Check if category is active
     *
     * @param CategoryModel $category
     * @param StoreModel    $store = null
     *
     * @return bool
     */
    public function getIsCategoryActive(
        CategoryModel $category,
        StoreModel $store = null
    ) {
        if (!$store) {
            $categoryStoreId = $category->getStoreId();

            if ($categoryStoreId) {
                $store = $this->storeManager->getStore($categoryStoreId);
            } else {
                $store = $this->storeManager->getStore();
            }
        }

        $pathIds = $category->getPathIds();
        array_shift($pathIds);

        foreach ($pathIds as $pathId) {
            try {
                $parent = $this->categoryRepository->get($pathId, $store->getId());
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                // Unable to fetch parent category
                $parent = null;
            }

            if ($parent && (bool) $parent->getIsActive() === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return parent category id
     *
     * @param CategoryModel $category Category object
     * @param StoreModel    $store    Store object
     *
     * @return int
     */
    private function getParentCategoryId(
        \Magento\Catalog\Model\Category $category,
        \Magento\Store\Model\Store $store = null
    ) {
        $parentCategoryId = 0;

        if ($category) {
            try {
                $parentCategory = $category->getParentCategory();
                $parentCategoryId = $parentCategory ? $parentCategory->getId() : 0;
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                // No parent category
                $parentCategoryId = 0;
            }
        }

        return $parentCategoryId;
    }

    /**
     * Returns category image url
     *
     * @param CategoryModel $category Category object
     * @param StoreModel    $store    Store object
     *
     * @return string
     */
    private function getCategoryImageUrl(
        \Magento\Catalog\Model\Category $category,
        \Magento\Store\Model\Store $store = null
    ) {
        $imageUrl = '';

        if ($category) {
            try {
                $imageUrl = $category->getImageUrl();
            } catch (\Exception $e) {
                // No image
                $imageUrl = '';
            }
        }

        return $imageUrl;
    }

    /**
     * Returns additional categories attributes
     *
     * @return array
     */
    private function getAdditionalAttrs()
    {
        return self::$additionalsAttrs;
    }

    /**
     * Returns root category id
     *
     * @return int
     */
    private function getRootCategoryId()
    {
        static $rootCategoryId = -1;

        if ($rootCategoryId !== -1) {
            return $rootCategoryId;
        }

        $collection = $this->catalogResourceModelCategoryCollectionFactory
            ->create()
            ->addAttributeToFilter('parent_id', '0');

        $rootCategory = $collection->getFirstItem();
        $rootCategoryId = $rootCategory->getId();

        return $rootCategoryId;
    }

    /**
     * Return categories by category ids
     *
     * @param  mixed      $categoryIds
     * @param  StoreModel $store
     *
     * @return array|object
     */
    public function getCategories(
        $categoryIds = Queue::NOT_DATA,
        \Magento\Store\Model\Store $store = null
    ) {
        static $arrCategories = [];

        $keyCategories = '';
        $storeId = !empty($store) ? $store->getId() : 0;
        $storeRootCategoryId = $this->storeManager->getStore($storeId)->getRootCategoryId();
        $storeRootCategoryPath = sprintf('%d/%d', $this->getRootCategoryId(), $storeRootCategoryId);
        $additionalAttr = $this->getAdditionalAttrs();

        if (!empty($categoryIds)) {
            if (is_array($categoryIds)) {
                $keyCategories .= implode('_', $categoryIds);
            } else {
                $keyCategories .= $categoryIds;
            }
        }

        $keyCategories .= ':' .  $storeId;

        if (!isset($arrCategories[$keyCategories])) {
            $collection = $this->catalogResourceModelCategoryCollectionFactory->create();

            $collection
                ->clear()
                ->distinct(true)
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('level', ['gt' => 1])
                ->addPathFilter($storeRootCategoryPath)
                ->addOrderField('entity_id')
                ->addUrlRewriteToResult();

            if (!empty($store)) {
                $collection->setStoreId($storeId);
            }

            if ($categoryIds !== Queue::NOT_DATA) {
                // Already exist automatic definition 'one value' or 'array'.
                $collection->addIdFilter(array_unique($categoryIds));
            }

            $arrCategories[$keyCategories] = $collection->load();
        }

        return $arrCategories[$keyCategories];
    }

    /**
     * Generate categories feeds
     *
     * @param  mixed      $categoryIds
     * @param  StoreModel $store
     * @param  string     $checkData
     *
     * @return array[]
     */
    public function generateCategoriesFeed(
        $categoryIds = Queue::NOT_DATA,
        StoreModel $store = null,
        $checkData = true
    ) {
        $items = [];

        $categories = $this->getCategories($categoryIds, $store);

        if (!empty($categories)) {
            foreach ($categories as $category) {
                if ($item = $this->generateCategoryFeed($category, $store, $checkData)) {
                    $items[] = $item;
                }
            }
        }

        return $items;
    }

    /**
     * Returns mix/max category ids values
     *
     * @param StoreModel $store
     *
     * @return array(mix, max)
     */
    public function getMinMaxCategoryId(StoreModel $store = null)
    {
        $startId = $endId = 0;

        $categoryStartCollection = $this->catalogResourceModelCategoryCollectionFactory->create()
            ->addAttributeToSelect(['entity_id'])
            ->addAttributeToSort('entity_id', DataCollection::SORT_ORDER_ASC)
            ->setPageSize(1);

        if (!empty($store)) {
            $categoryStartCollection->setStoreId($store->getId());
        }

        $categoryEndCollection = $this->catalogResourceModelCategoryCollectionFactory->create()
            ->addAttributeToSelect(['entity_id'])
            ->addAttributeToSort('entity_id', DataCollection::SORT_ORDER_DESC)
            ->setPageSize(1);

        if (!empty($store)) {
            $categoryEndCollection->setStoreId($store->getId());
        }

        if ($categoryStartCollection->getSize() > 0) {
            $firstItem = $categoryStartCollection->getFirstItem();
            $startId = $firstItem->getId();
        }

        if ($categoryEndCollection->getSize() > 0) {
            $firstItem = $categoryEndCollection->getFirstItem();
            $endId = $firstItem->getId();
        }

        return [$startId, $endId];
    }

    /**
     * Returns category ids from range
     *
     * @param  int                        $start Start category id
     * @param  int                        $end   End category id
     * @param  int                        $step  Step value
     * @param  StoreModel                 $store
     *
     * @return array
     */
    public function getCategoryIdsFromRange($start, $end, $step, StoreModel $store = null)
    {
        $arrCategories = [];

        $categories = $this->catalogResourceModelCategoryCollectionFactory->create()
            ->distinct(true)
            ->addAttributeToSelect(['entity_id'])
            ->addFieldToFilter('entity_id', ['from' => $start, 'to' => $end])
            ->setPageSize($step);

        if (!empty($store)) {
            $categories->setStoreId($store->getId());
        }

        $arrCategories = $categories->getAllIds();
        // It is necessary for save memory.
        unset($categories);

        return $arrCategories;
    }

    /**
     * Get children for categories
     *
     * @param  number $catId Category identifier
     *
     * @return array
     */
    public function getAllChildrenCategories($catId)
    {
        $categoryIds = [];

        $categories = $this->catalogResourceModelCategoryCollectionFactory->create()
            ->setStoreId($this->storeManager->getStore()->getId())
            ->addFieldToFilter('entity_id', $catId)
            ->load();

        if (!empty($categories)) {
            foreach ($categories as $cat) {
                if (!empty($cat)) {
                    $categoryIds = $cat->getAllChildren(true);
                }
            }
        }

        return $categoryIds;
    }
}
