<?php
declare(strict_types=1);

namespace Swissup\SoldTogether\Model\Resolver\DataProvider;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;

class Products
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    private $catalogVisibility;

    /**
     * @var \Swissup\SoldTogether\Helper\Stock
     */
    private $stockHelper;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var array
     */
    private $productIds = false;

    /**
     * @var integer
     */
    private $currentProductId;

    /**
     *
     * @var integer
     */
    private $pageSize = 20;

    /**
     *
     * @var integer
     */
    private $currentPage = 1;

    /**
     * @var bool
     */
    private $showOnlySimple = false;

    /**
     * @var bool
     */
    private $showOutOfStock = false;

    /**
     * @var integer
     */
    private $limit;

    /**
     * @var string
     */
    private $resourceType = 'order';

    /**
     * @var bool
     */
    private $canUseRandom = false;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;

    /**
     * @var \Magento\Catalog\Model\Product|bool
     */
    private $currentProduct = false;

    /**
     * @param \Magento\Checkout\Model\Session                                $checkoutSession
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility                      $catalogVisibility
     * @param \Swissup\SoldTogether\Helper\Stock                             $stockHelper
     * @param \Swissup\SoldTogether\Model\ResourceModel\Order                $orderResource
     * @param \Swissup\SoldTogether\Model\ResourceModel\Customer             $customerResource
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface               $categoryRepository
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogVisibility,
        \Swissup\SoldTogether\Helper\Stock $stockHelper,
        \Swissup\SoldTogether\Model\ResourceModel\Order $orderResource,
        \Swissup\SoldTogether\Model\ResourceModel\Customer $customerResource,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->collectionFactory = $collectionFactory;
        $this->catalogVisibility = $catalogVisibility;
        $this->stockHelper = $stockHelper;
        $this->categoryRepository = $categoryRepository;
        $this->catalogConfig = $catalogConfig;
        $this->productRepository = $productRepository;

        $this->resource = [
            'order' => $orderResource,
            'customer' => $customerResource
        ];
    }

    /**
     *
     * @param int $pageSize
     * @return Messages
     */
    public function setPageSize(int $pageSize)
    {
        $this->pageSize = $pageSize;
        return $this;
    }

    /**
     *
     * @param int $currentPage
     * @return Messages
     */
    public function setCurrentPage(int $currentPage)
    {
        $this->currentPage = $currentPage;
        return $this;
    }

    /**
     * @param int $productId
     * @return $this
     */
    public function setCurrentProductId($productId)
    {
        $this->currentProductId = $productId;
        return $this;
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    private function getProduct()
    {
        if ($this->currentProduct === false) {
            $this->currentProduct = $this->productRepository->getById($this->currentProductId);
        }
        return $this->currentProduct;
    }

    /**
     * Get product ids that will be used to retrieve related products collection
     *
     * @return array
     */
    private function getProductIds()
    {
        if ($this->productIds !== false) {
            return $this->productIds;
        }
        $ids = [];

        if ($this->getProduct()) {
            $ids[] = $this->getProduct()->getId();
        } elseif ($this->checkoutSession->getLastRealOrder()) {
            $items = $this->checkoutSession->getLastRealOrder()->getAllVisibleItems();
            foreach ($items as $item) {
                $ids[] = $item->getProductId();
            }
        } else {
            $items = $this->checkoutSession->getQuote()->getAllItems();
            foreach ($items as $item) {
                $ids[] = $item->getProductId();
            }
        }
        $this->productIds = $ids;

        return $ids;
    }

    /**
     * @param bool $status
     * @return $this
     */
    public function setShowOnlySimple($status = true)
    {
        $this->showOnlySimple = (bool) $status;
        return $this;
    }

    /**
     * @param bool $status
     * @return $this
     */
    public function setShowOutOfStock($status = true)
    {
        $this->showOutOfStock = (boolean) $status;
        return $this;
    }

    /**
     * @param bool $status
     * @return $this
     */
    public function setCanUseRandom($status = true)
    {
        $this->canUseRandom = (boolean) $status;
        return $this;
    }

    /**
     * @return bool
     */
    private function canUseRandom()
    {
        return $this->canUseRandom;
    }

    /**
     * @param int $limit
     * @return $this
     */
    public function setLimit(int $limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return mixed
     */
    private function getProductsCount()
    {
        return $this->limit;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setResourceType(string $type)
    {
        if (!array_key_exists($type, $this->resource)) {
            throw new InputException("Resource type '{$type}' is wrong");
        }

        $this->resourceType = $type;
        return $this;
    }

    /**
     * @return string
     */
    private function getRelationResourceTable()
    {
        $relation = $this->resourceType;

        return isset($this->resource[$relation])
            ? $this->resource[$relation]->getMainTable()
            : '';
    }

    /**
     * @return mixed
     */
    private function getCollection()
    {
        $collection = $this->collectionFactory->create();
        $collection = $this->prepareCollection($collection);

        $relationTable = $this->getRelationResourceTable();
        if ($relationTable) {
            $collection->getSelect()
                ->joinInner(
                    ['soldtogether' => $relationTable],
                    'soldtogether.related_id = e.entity_id',
                    []
                )
                ->where('soldtogether.product_id IN (?)', $this->getProductIds())
                ->order('soldtogether.weight DESC');
        }

        if ($collection->count() === 0 && $this->canUseRandom()) {
            if ($_collection = $this->getRandomCollection()){
                $collection = $this->prepareCollection($_collection);
            }
        }
//        $collection = $this->prepareCollection($collection);

        return $collection;
    }

    /**
     * Prepare random collection of products from same category
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|false
     */
    protected function getRandomCollection()
    {
        $product = $this->getProduct();

        if (!$product) {
            return false;
        }

        if ($product->hasCategory()) {
            $category = $product->getCategory();
        } elseif ($product->hasCategoryIds()) {
            $categoryIds = $product->getCategoryIds();
            try {
                $category = $this->categoryRepository->get(reset($categoryIds));
            } catch (NoSuchEntityException $e) {
                return false;
            }
        } else {
            return false;
        }

        $collection = $category->getProductCollection();
//        $collection = $this->prepareCollection($collection);
        $collection->getSelect()->order('rand()');

        return $collection;
    }

    /**
     * @param $collection
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function prepareCollection($collection)
    {
        $collection->distinct(true)
            ->addAttributeToSelect('required_options')
            ->addStoreFilter()
            ->setVisibility(
                $this->catalogVisibility->getVisibleInCatalogIds()
            )
            ->addAttributeToFilter('entity_id', [
                'nin' => $this->getProductIds()
            ]);

        if ($this->stockHelper->isModuleOutputEnabled('Magento_Checkout')) {
//            $this->_addProductAttributesAndPrices($collection);
            $collection
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
                ->addUrlRewrite();
        }

        if ($this->showOnlySimple) {
            $collection->getSelect()
                ->where('e.type_id IN (?)', ['simple' /*, 'virtual'*/]);
        }

        if (!$this->showOutOfStock) {
            $this->stockHelper->addInStockFilterToCollection($collection);
        }

        $collection->getSelect()->limit(
            $this->getProductsCount()
        );

        return $collection;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getData(): array
    {
        $pageSize = $this->pageSize;
        $currentPage = $this->currentPage;

        $collection = $this->getCollection()
            ->setCurPage($currentPage)
            ->setPageSize($pageSize)
        ;

        $totalCount = $collection->getSize();
        $totalPages = ceil($totalCount / $pageSize);

        $items = [];

        foreach ($collection as $itemObject) {
//            throw new \Magento\Framework\GraphQl\Exception\GraphQlInputException(
//                new \Magento\Framework\Phrase(
//                    $itemObject->getId()
//    //                (string)$collection->getSelect()
//    ////                $this->currentProductId . $this->getProduct()->getId()
//                )
//            );
            $items[$itemObject->getId()] = $itemObject->getData();
            $items[$itemObject->getId()]['model'] = $itemObject;
        }

        $data = [
            'total_count' => $totalCount,
            'items' => $items,
            'page_info' => [
                'page_size' => $pageSize,
                'current_page' => $currentPage,
                'total_pages' => $totalPages,
            ]
        ];

        return $data;
    }
}
