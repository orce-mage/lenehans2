<?php

namespace MageBig\WidgetPlus\Model\ResourceModel\Widget;

use Magento\Customer\Model\Session as CustomerSession;

class Collection extends \Magento\Framework\Data\Collection
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $catalogProductVisibility;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $_catalogConfig;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Reports\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productsFactory;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Reports\Model\ResourceModel\Product\CollectionFactory $productsFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        CustomerSession $customerSession,
        \MageBig\WidgetPlus\Model\Rule $rule
    )
    {
        $this->_resource = $resource;
        $this->_customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->_coreRegistry = $registry;
        $this->_checkoutSession = $checkoutSession;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogConfig = $catalogConfig;
        $this->_categoryFactory = $categoryFactory;
        $this->_localeDate = $localeDate;
        $this->_productsFactory = $productsFactory;
        $this->_rule = $rule;
        parent::__construct($entityFactory);
    }

    /**
     * @param array $params
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function createCollection($params = [])
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());

        $collection = $this->_addProductAttributesAndPrices($collection)
            ->addStoreFilter();

        if (isset($params['category_ids'])) {
            $catsFilter = ['in' => $params['category_ids']];
            $collection->addCategoriesFilter($catsFilter);
        }

        return $collection;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return $this
     */
    protected function _addProductAttributesAndPrices(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
    )
    {
        return $collection
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
            ->addUrlRewrite();
    }

    /**
     * @param $type
     * @param $value
     * @param $params
     * @param int $limit
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|null|void
     */
    public function getProducts($type, $value, $params, $limit = 12)
    {
        $collection = null;
        if (!is_array($params)) {
            $params = [];
        }

        if ($type == 'category') {
            $collection = $this->_getProductCategory($value);
            $collection->setPageSize($limit);
        } else {
            switch ($value) {
                case 'featured':
                    $collection = $this->_getIdCollection($params, $limit);
                    break;
                case 'newfromdate':
                    $collection = $this->_getNewArrivals($params, $limit);
                    break;
                case 'newupdated':
                    $collection = $this->_getNewUpdated($params, $limit);
                    break;
                case 'bestseller':
                    $collection = $this->_getBestSeller($params, $limit);
                    break;
                case 'discount':
                    $collection = $this->_getDiscount($params, $limit);
                    break;
                case 'related':
                    $collection = $this->_getRelated($limit);
                    break;
                case 'upsell':
                    $collection = $this->_getUpSell($limit);
                    break;
                case 'mostviewed':
                    $collection = $this->_getMostViewed($params, $limit);
                    break;
                case 'rating':
                    $collection = $this->_getTopRated($params, $limit);
                    break;
                case 'random':
                    $collection = $this->_getRandomCollection($params, $limit);
                    break;
                default:
                    $collection = $this->_getNewReleases($params, $limit);
                    break;
            }
        }

        return $collection;
    }

    protected function _getProductCategory($category)
    {
        if (!$category) {
            return;
        }

        if (!$category instanceof \Magento\Catalog\Model\Category) {
            $categoryModel = $this->_categoryFactory->create();
            $categoryModel = $categoryModel->load($category);
            if ($categoryModel->getId()) {
                $params = ['category_ids' => $categoryModel->getId()];
            } else {
                return;
            }
        } else {
            $params = ['category_ids' => $category];
        }

        $collection = $this->createCollection($params);

        return $collection;
    }

    protected function _getIdCollection($params, $limit)
    {
        if (isset($params['category_ids'])) {
            unset($params['category_ids']);
        }
        if (!isset($params['product_ids'])) {
            return;
        }
        if (!is_array($params['product_ids'])) {
            return;
        }
        if (!count($params['product_ids'])) {
            return;
        }

        $collection = $this->createCollection($params);
        $collection->addIdFilter($params['product_ids']);

        $collection->getSelect()
            ->order(new \Zend_Db_Expr('FIELD(e.entity_id,' . implode(',', $params['product_ids']).')'))
            ->limit($limit);
        // $collection->setPageSize($limit);

        return $collection;
    }

    protected function _getNewReleases($params, $limit)
    {
        $collection = $this->createCollection($params);

        $collection->setOrder('created_at', 'DESC');

        $collection->getSelect()->limit($limit);
        // $collection->setPageSize($limit);

        return $collection;
    }

    protected function _getNewArrivals($params, $limit)
    {
        $todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');

        $collection = $this->createCollection($params);
        $collection
            ->addAttributeToFilter('news_from_date', ['date' => true, 'to' => $todayStartOfDayDate])
            ->addAttributeToFilter(
                [
                    ['attribute' => 'news_to_date', 'date' => true, 'from' => $todayStartOfDayDate],
                    ['attribute' => 'news_to_date', 'is' => new \Zend_Db_Expr('null')],
                ],
                '',
                'left'
            )
            ->addAttributeToSort('news_from_date', 'DESC');

        $collection->getSelect()->limit($limit);
        // $collection->setPageSize($limit);

        return $collection;
    }

    protected function _getNewUpdated($params, $limit)
    {
        $collection = $this->createCollection($params);

        $collection->setOrder('updated_at', 'DESC');

        $collection->getSelect()->limit($limit);
        //$collection->setPageSize($limit);

        return $collection;
    }

    protected function _getBestSeller($params, $limit)
    {
        if (isset($params['period'])) {
            $collection = $this->createCollection($params);

            $date = $this->_localeDate->date();
            switch ($params['period']) {
                case 'current_year':
                    $from = $date->format('Y-01-01');
                    $to = $date->modify('+1 year')->format('Y-01-01');

                    break;
                case 'last_year':
                    $from = $date->modify('-1 year')->format('Y-01-01');
                    $to = $date->format('Y-01-01');

                    break;
                case 'current_month':
                    $from = $date->format('Y-m-01');
                    $to = $date->modify('+1 month')->format('Y-m-01');

                    break;
                case 'last_month':
                    $from = $date->modify('-1 month')->format('Y-m-01');
                    $to = $date->format('Y-m-01');

                    break;
                case  'yesterday':
                    $from = $date->modify('-1 day')->format('Y-m-d');
                    $to = $date->format('Y-m-d');

                    break;
                default:
                    $from = null;
                    $to = $date->modify('+1 year')->format('Y-01-01');

                    break;
            }

            if ($from) {
                $joinQuery = "(oi.product_id = e.entity_id AND oi.created_at > '{$from}' AND oi.created_at < '{$to}')";
            } else {
                $joinQuery = "(oi.product_id = e.entity_id AND oi.created_at < '{$to}')";
            }

            $orderItems = $this->_resource->getTableName('sales_order_item');
            $orderMain = $this->_resource->getTableName('sales_order');
            $collection->getSelect()
                ->join(['oi' => $orderItems], $joinQuery, ['count' => 'SUM(oi.qty_ordered)'])
                ->join(['om' => $orderMain], 'oi.order_id = om.entity_id', [])
                ->where('om.status = ?', 'complete')
                ->group('e.entity_id')
                ->order('count DESC')
                ->limit($limit);

            // $collection->setPageSize($limit)->setCurPage(1);

            $collection->getSelect()->limit($limit);

            return $collection;
        }

        return false;
    }

    protected function _getMostViewed($params, $limit)
    {
        $currentStoreId = $this->storeManager->getStore()->getId();

        $collection = $this->_productsFactory->create()
            ->addAttributeToSelect(
                '*'
            )->addViewsCount()->setStoreId(
                $currentStoreId
            )->addStoreFilter(
                $currentStoreId
            );
        if (isset($params['category_ids'])) {
            $catsFilter = ['in' => $params['category_ids']];
            $collection->addCategoriesFilter($catsFilter);
        }

        $collection->getSelect()->limit($limit);

        //$collection->setPageSize($limit);

        return $collection;
    }

    public function createCollection2($params = [])
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();
        //$collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());

        $collection = $this->_addProductAttributesAndPrices($collection)
            ->addStoreFilter();

        if (isset($params['category_ids'])) {
            $catsFilter = ['in' => $params['category_ids']];
            $collection->addCategoriesFilter($catsFilter);
        }

        return $collection;
    }

    protected function _getDiscount($params, $limit)
    {
        $collection = $this->createCollection2($params);
        $collection2 = clone $collection;
        // $collectionDiscount = clone $collection;
        $connection = $this->_resource->getConnection('core_read');
        //$websiteId       = $this->storeManager->getStore(true)->getWebsite()->getId();
        //$customerGroupId = $this->_customerSession->getCustomerGroupId();

        // $select = $connection->select()
        //     ->from($this->_resource->getTableName('catalogrule_product_price'), ['product_id', 'rule_price'])
        //     ->where('website_id = ?', $websiteId)
        //     ->where('customer_group_id = ?', $customerGroupId)
        //     ->distinct('product_id');
        // $collection->getSelect()->join(
        //     ['discount_rule' => $select],
        //     implode(' AND ', ['discount_rule.product_id = e.entity_id AND discount_rule.rule_price < price_index.price']),
        //     []
        // );

        $collection2->getSelect()
            ->where('price_index.final_price < price_index.price')
            ->limit($limit);
        // $collection2->setPageSize($limit);
        $col = $collection2->getAllIds();

        $select = $connection->select()
            ->from($this->_resource->getTableName('catalog_product_super_link'), ['parent_id'])
            ->where('product_id IN (?)', $col)
            ->distinct('parent_id');

        $colId = $connection->fetchCol($select);

        $colAll = array_merge($col, $colId);

        $collection->addIdFilter($colAll);
        $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());
        $collection->setOrder('created_at', 'DESC');
        //$collection->setPageSize($limit);

        $collection->getSelect()->limit($limit);

        unset($connection, $select);

        return $collection;
    }

    /**
     * @return array|mixed|null
     */
    public function getCartProductIds()
    {
        $ids = $this->_coreRegistry->registry('_cart_product_ids');
        if ($ids === null) {
            $ids = [];
            foreach ($this->_checkoutSession->getQuote()->getAllItems() as $item) {
                $product = $item->getProduct();
                if ($product) {
                    $ids[] = $product->getId();
                }
            }
            $this->_coreRegistry->register('_cart_product_ids', $ids);
        }

        return $ids;
    }

    protected function _getRelated($limit)
    {
        $product = $this->_coreRegistry->registry('product');

        if (!$product) {
            return;
        }

        $collection = $product->getRelatedProductCollection()
            ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
            ->setPositionOrder()
            ->addStoreFilter();

        $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());

        if ($limit) {
            $collection->getSelect()->limit($limit);
        }

        // $collection->load();

        foreach ($collection as $product) {
            $product->setDoNotUseCategoryId(true);
        }

        return $collection;
    }

    protected function _getUpSell($limit)
    {
        $product = $this->_coreRegistry->registry('product');

        if (!$product) {
            return;
        }

        $collection = $product->getUpSellProductCollection()
            ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
            ->setPositionOrder()
            ->addStoreFilter();

        $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());

        if ($limit) {
            $collection->getSelect()->limit($limit);
        }

        //$collection->setPage(1, $limit);
        // $collection->load();

        foreach ($collection as $product) {
            $product->setDoNotUseCategoryId(true);
        }

        return $collection;
    }

    protected function _getTopRated($params, $limit)
    {
        $collection = $this->createCollection($params);

        $resource = $this->_resource;
        $connection = $resource->getConnection('core_read');
        $storeId = $this->storeManager->getStore()->getId();

        $select = $connection->select()
            ->from(
                $this->_resource->getTableName('rating_option_vote_aggregated'),
                ['entity_pk_value', 'rating_total' => 'SUM(percent_approved)']
            )
            ->where('store_id = ?', $storeId)
            ->group('entity_pk_value')
            ->order('rating_total DESC');

        $collection->getSelect()->join(
            ['rating_idx' => $select],
            implode(' AND ', ['rating_idx.entity_pk_value = e.entity_id']),
            ['rating_idx.rating_total']
        )->limit($limit);

        //$collection->setPageSize($limit);
        unset($connection, $select);

        return $collection;
    }

    protected function _getRandomCollection($params, $limit)
    {
        $collection = $this->createCollection($params);

        $numberOfItems = $limit;
        $candidateIds = $collection->getAllIds();
        $chosenIds = [];
        $count = count($candidateIds);

        if (!$count) {
            return false;
        }

        if ($count == 1) {
            return $collection;
        }

        $maxKey = $count - 1;

        if ($maxKey < $limit) {
            $numberOfItems = $maxKey;
        }

        while (count($chosenIds) <= $numberOfItems) {
            $randomKey = mt_rand(0, $maxKey);
            $chosenIds[$randomKey] = $candidateIds[$randomKey];
        }

        $collection->getSelect()
            ->order(new \Zend_Db_Expr('FIELD(e.entity_id,' . implode(',', $chosenIds).')'))
            ->limit($limit);

        return $collection;
    }
}
