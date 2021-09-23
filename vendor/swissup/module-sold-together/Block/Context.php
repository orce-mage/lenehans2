<?php

namespace Swissup\SoldTogether\Block;

class Context
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $catalogVisibility;

    /**
     * @var \Swissup\SoldTogether\Helper\Stock
     */
    protected $stockHelper;

    /**
     * @var \Swissup\SoldTogether\Model\ResourceModel\Order
     */
    protected $orderResource;

    /**
     * @var \Swissup\SoldTogether\Model\ResourceModel\Customer
     */
    protected $customerResource;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Catalog\Block\Product\Context
     */
    protected $context;

    /**
     * @param \Magento\Checkout\Model\Session                                $checkoutSession
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility                      $catalogVisibility
     * @param \Swissup\SoldTogether\Helper\Stock                             $stockHelper
     * @param \Swissup\SoldTogether\Model\ResourceModel\Order                $orderResource
     * @param \Swissup\SoldTogether\Model\ResourceModel\Customer             $customerResource
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface               $categoryRepository
     * @param \Magento\Catalog\Block\Product\Context                         $context
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogVisibility,
        \Swissup\SoldTogether\Helper\Stock $stockHelper,
        \Swissup\SoldTogether\Model\ResourceModel\Order $orderResource,
        \Swissup\SoldTogether\Model\ResourceModel\Customer $customerResource,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Block\Product\Context $context
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->collectionFactory = $collectionFactory;
        $this->catalogVisibility = $catalogVisibility;
        $this->stockHelper = $stockHelper;
        $this->orderResource = $orderResource;
        $this->customerResource = $customerResource;
        $this->categoryRepository = $categoryRepository;
        $this->productContext = $context;
    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckoutSession()
    {
        return $this->checkoutSession;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    public function getCollectionFactory()
    {
        return $this->collectionFactory;
    }

    /**
     * @return \Magento\Catalog\Model\Product\Visibility
     */
    public function getCatalogVisibility()
    {
        return $this->catalogVisibility;
    }

    /**
     * @return \Swissup\SoldTogether\Helper\Stock
     */
    public function getStockHelper()
    {
        return $this->stockHelper;
    }

    /**
     * @return \Swissup\SoldTogether\Model\ResourceModel\Order
     */
    public function getOrderResource()
    {
        return $this->orderResource;
    }

    /**
     * @return \Swissup\SoldTogether\Model\ResourceModel\Customer
     */
    public function getCustomerResource()
    {
        return $this->customerResource;
    }

    /**
     * @return \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    public function getCategoryRepository()
    {
        return $this->categoryRepository;
    }

    /**
     * @return \Magento\Catalog\Block\Product\Context
     */
    public function getProductContext()
    {
        return $this->productContext;
    }
}
