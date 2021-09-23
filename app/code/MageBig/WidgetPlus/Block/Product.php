<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace MageBig\WidgetPlus\Block;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Catalog Products List widget block
 * Class ProductsList.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Product extends \Magento\Catalog\Block\Product\AbstractProduct implements \Magento\Widget\Block\BlockInterface
{
    const CACHE_TAGS = 'WIDGETPLUS_PRODUCT';

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \MageBig\WidgetPlus\Model\ResourceModel\Widget\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var
     */
    protected $_productCollection;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $categoryModel;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * Product constructor.
     *
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \MageBig\WidgetPlus\Model\ResourceModel\Widget\CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Model\Category $categoryModel
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \MageBig\WidgetPlus\Model\ResourceModel\Widget\CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\Category $categoryModel,
        array $data = [],
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->httpContext = $httpContext;
        $this->_collectionFactory = $collectionFactory;
        $this->categoryModel = $categoryModel;
        parent::__construct($context, $data);
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\Serialize\Serializer\Json::class);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->addColumnCountLayoutDepend('empty', 6)->addColumnCountLayoutDepend(
            '1column',
            5
        )->addColumnCountLayoutDepend('2columns-left', 4)->addColumnCountLayoutDepend(
            '2columns-right',
            4
        )->addColumnCountLayoutDepend('3columns', 3);
    }

    /**
     * Get block cache life time
     *
     * @return int|bool|null
     */
    protected function getCacheLifetime()
    {
        if (!$this->hasData('cache_lifetime')) {
            return null;
        }

        $cacheLifetime = $this->getData('cache_lifetime');
        if (false === $cacheLifetime || null === $cacheLifetime) {
            return $cacheLifetime;
        }

        return (int)$cacheLifetime;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCacheKeyInfo()
    {
        $key = parent::getCacheKeyInfo();
        $info = [
            'MAGEBIG_WIDGETPLUS_PRODUCT',
            $this->getPriceCurrency()->getCurrencySymbol(),
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP),
            $this->serializer->serialize($this->getRequest()->getParams()),
            $this->getWidgetId(),
        ];

        return array_merge($key, $info);
    }

    /**
     * @return PriceCurrencyInterface|mixed
     */
    private function getPriceCurrency()
    {
        if ($this->priceCurrency === null) {
            $this->priceCurrency = \Magento\Framework\App\ObjectManager::getInstance()->get(PriceCurrencyInterface::class);
        }
        return $this->priceCurrency;
    }

    /**
     * @return int|string
     */
    public function getWidgetId()
    {
        $widgetId = crc32($this->serializer->serialize($this->getData()));
        $widgetId = 'widgetplus-' . $widgetId;

        return $widgetId;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|null|void
     */
    protected function _getProductCollection()
    {
        if ($this->_productCollection === null) {
            $this->_productCollection = $this->initializeProductCollection();
        }

        return $this->_productCollection;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|null|void
     */
    protected function initializeProductCollection()
    {
        $limit = (int)$this->getData('limit');
        $value = $this->getData('product_type');
        $params = [];

        if ($this->getData('period')) {
            $params['period'] = $this->getData('period');
        }
        if ($this->getData('category_ids')) {
            $params['category_ids'] = explode(',', $this->getData('category_ids'));
        }
        if ($this->getData('product_ids')) {
            $params['product_ids'] = explode(',', $this->getData('product_ids'));
        }
        if ($this->getCustomerId()) {
            $params['customer_id'] = $this->getCustomerId();
        }

        $collection = $this->_collectionFactory->create()->getProducts('product', $value, $params, $limit);

        return $collection;
    }

    /**
     * Retrieve loaded category collection
     *
     * @return AbstractCollection
     */
    public function getLoadedProductCollection()
    {
        return $this->_getProductCollection();
    }

    /**
     * @param AbstractCollection $collection
     *
     * @return $this
     */
    public function setCollection($collection)
    {
        $this->_productCollection = $collection;

        return $this;
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];
        foreach ($this->_getProductCollection() as $item) {
            $identities = array_merge($identities, $item->getIdentities());
        }

        return $identities;
    }
}
