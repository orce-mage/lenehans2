<?php
namespace Dot\Relatedproducts\Model\ResourceModel\Widget;

use Magento\Customer\Model\Session as CustomerSession;

class Collection extends \MageBig\WidgetPlus\Model\ResourceModel\Widget\Collection
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
     * @var \Magento\Framework\Module\Manager
     */
    // protected $_moduleManager;

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
        // \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        CustomerSession $customerSession,
        \MageBig\WidgetPlus\Model\Rule $rule
    ) {
        $this->_resource                 = $resource;
        $this->_customerSession          = $customerSession;
        $this->storeManager              = $storeManager;
        $this->_coreRegistry             = $registry;
        $this->_checkoutSession          = $checkoutSession;
        $this->catalogProductVisibility  = $catalogProductVisibility;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogConfig            = $catalogConfig;
        $this->_categoryFactory          = $categoryFactory;
        // $this->_moduleManager            = $moduleManager;
        $this->_localeDate               = $localeDate;
        $this->_productsFactory          = $productsFactory;
        $this->_rule                     = $rule;
        parent::__construct($entityFactory,
							$productCollectionFactory,
							$catalogProductVisibility,
							$catalogConfig,
							$productsFactory,
							$resource,
							$storeManager,
							$registry,
							$checkoutSession,
							$categoryFactory,
							// $moduleManager,
							$localeDate,
							$customerSession,
							$rule
							);
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

    protected function _getBestSeller($params, $limit)
    {
        if (isset($params['period'])) {
            $collection = $this->createBestSellerCollection($params);

            $date = $this->_localeDate->date();
            switch ($params['period']) {
                case 'current_year' :
                    $from   = $date->format('Y-01-01');
                    $to     = $date->modify('+1 year')->format('Y-01-01');

                    break;
                case 'last_year' :
                    $from   = $date->modify('-1 year')->format('Y-01-01');
                    $to     = $date->format('Y-01-01');

                    break;
                case 'current_month' :
                    $from   = $date->format('Y-m-01');
                    $to     = $date->modify('+1 month')->format('Y-m-01');

                    break;
                case 'last_month' :
                    $from   = $date->modify('-1 month')->format('Y-m-01');
                    $to     = $date->format('Y-m-01');

                    break;
                case  'yesterday' :
                    $from   = $date->modify('-1 day')->format('Y-m-d');
                    $to     = $date->format('Y-m-d');

                    break;
                default:
                    $from   = null;
                    $to     = $date->modify('+1 year')->format('Y-01-01');

                    break;
            }

            if ($from) {
                $joinQuery = "(oi.product_id = e.entity_id AND oi.created_at > '{$from}' AND oi.created_at < '{$to}')";
            } else {
                $joinQuery = "(oi.product_id = e.entity_id AND oi.created_at < '{$to}')";
            }

            $orderItems = $this->_resource->getTableName('sales_order_item');
            $orderMain  = $this->_resource->getTableName('sales_order');
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

    protected function _getRelated($limit = 12)
    {
        $product = $this->_coreRegistry->registry('product');

        if (!$product) {
            return;
        }

        $collection = $product->getRelatedProductCollection()
            ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
            ->setPositionOrder()
            ->addStoreFilter();

        // if ($this->_moduleManager->isEnabled('Magento_Checkout')) {
        //     $cartProductIds = $this->getCartProductIds();
        //     if (!empty($cartProductIds)) {
        //         $collection->addExcludeProductFilter($cartProductIds);
        //     }
        //     $this->_addProductAttributesAndPrices($collection);
        // }

        $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());

        $collection->getSelect()->limit($limit);

        // $collection->load();

        foreach ($collection as $product) {
            $product->setDoNotUseCategoryId(true);
        }
        $categoryPath = $product->getCategory();
        if(!empty($categoryPath))
        {
			$_categories = $product->getCategory()->getPath();
			//$logger->info($_categories);
			$categoriesids = explode('/', $_categories);

			if((!$collection->getSize()) && (!empty($categoriesids)))
			{
				$currentCatId = end($categoriesids);
				$collection = $this->_categoryFactory->create()->load($currentCatId)->getProductCollection()
							 ->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
							 ->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
							 ->addAttributeToSelect('*')->setPageSize(10)->setCurPage(1);
				$in_stockIDS = array();
				foreach($collection as $item)
				{
					if ($item->isSaleable() && $product->getId()!= $item->getId()) {
					  $in_stockIDS[] =  $item->getId();
					}
				}
				$collection = $this->_productCollectionFactory->create()
										->addAttributeToSelect('*')
										->addAttributeToFilter('entity_id',array('in' => $in_stockIDS));
				return $collection;
			}
		}
		else
		{
			$categaries = $product->getCategoryIds();
			$ct_cat = count($categaries);
			if($ct_cat > 1){
					$loadCt = $ct_cat -1;
			} else {
					$loadCt = 0;
			}
			if((!$collection->getSize()) && count($categaries))
			{
				$collection = $this->_categoryFactory->create()->load($categaries[$loadCt])->getProductCollection()
							 ->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
							 ->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
							 ->addAttributeToSelect('*')->setPageSize(30)->setCurPage(1);
				$in_stockIDS = array();
				foreach($collection as $item)
				{
					if ($item->isSaleable() && $product->getId()!= $item->getId()) {
					  $in_stockIDS[] =  $item->getId();
					}
				}
				if(empty($in_stockIDS)&& $loadCt >0)
				{
					$collection = $this->_categoryFactory->create()->load($categaries[$loadCt-1])->getProductCollection()
							 ->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
							 ->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
							 ->addAttributeToSelect('*')->setPageSize(30)->setCurPage(1);
					foreach($collection as $item)
					{
						if ($item->isSaleable() && $product->getId()!= $item->getId()) {
						  $in_stockIDS[] =  $item->getId();
						}				 
					}
				}
				$in_stockIDS = array_slice($in_stockIDS, 0, $limit, true);
				$collection = $this->_productCollectionFactory->create()->addAttributeToSelect('*')->addAttributeToFilter('entity_id',array('in' => $in_stockIDS));
			}
		}

        return $collection;
    }
    /**
     * @param array $params
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function createBestSellerCollection($params = [])
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());

        $collection = $this->_addProductAttributesAndPrices($collection)
            ->addStoreFilter();
		$category = $this->_coreRegistry->registry('current_category');
		if(!empty($category))
		{
			$categoryId =  $category->getId();
			if (!empty($categoryId)) {
				$catsFilter = ['in' => $categoryId];
				$collection->addCategoriesFilter($catsFilter);
			}
		}
		else
		{
			if (isset($params['category_ids'])) {
				$catsFilter = ['in' => $params['category_ids']];
				$collection->addCategoriesFilter($catsFilter);
			}
		}

        return $collection;
    }
}
?>
