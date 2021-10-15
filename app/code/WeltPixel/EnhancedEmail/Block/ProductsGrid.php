<?php

namespace WeltPixel\EnhancedEmail\Block;

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product\Link;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Link\CollectionFactory as LinkCollectionFactory;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Api\OrderRepositoryInterface;
use WeltPixel\EnhancedEmail\Helper\Data as EnhancedEmailHelper;
use WeltPixel\EnhancedEmail\Model\Config\Source\GridProductsType;

/**
 * Class ProductsGrid
 * @package WeltPixel\EnhancedEmail\Block
 */
class ProductsGrid extends AbstractProduct
{

    /**
     * @var EnhancedEmailHelper
     */
    protected $_wpHelper;

    /**
     * @var Visibility
     */
    protected $_productVisibility;

    /**
     * @var CollectionFactory|null
     */
    protected $productCollectionFactory;

    /**
     * @var LinkCollectionFactory
     */
    protected $linkCollectionFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * ProductsGrid constructor.
     * @param Context $context
     * @param Visibility $productVisibility
     * @param EnhancedEmailHelper $wpHelper
     * @param array $data
     * @param LinkCollectionFactory $linkCollectionFactory
     * @param OrderRepositoryInterface|null $orderRepository
     * @param CollectionFactory|null $productCollectionFactory
     */
    public function __construct(
        Context $context,
        Visibility $productVisibility,
        EnhancedEmailHelper $wpHelper,
        array $data = [],
        LinkCollectionFactory $linkCollectionFactory = null,
        ?OrderRepositoryInterface $orderRepository = null,
        ?CollectionFactory $productCollectionFactory = null
    ) {
        $this->_productVisibility = $productVisibility;
        $this->_wpHelper = $wpHelper;
        parent::__construct(
            $context,
            $data
        );
        $this->linkCollectionFactory = $linkCollectionFactory ?? ObjectManager::getInstance()->get(LinkCollectionFactory::class);
        $this->orderRepository = $orderRepository ?: ObjectManager::getInstance()->get(OrderRepositoryInterface::class);
        $this->productCollectionFactory = $productCollectionFactory
            ?? ObjectManager::getInstance()->get(CollectionFactory::class);
    }

    /**
     * @return array|mixed|null
     */
    protected function _getOrder()
    {
        $order = $this->getData('order');

        if ($order !== null) {
            return $order;
        }
        $orderId = (int)$this->getData('order_id');
        if ($orderId) {
            $order = $this->orderRepository->get($orderId);
            $this->setData('order', $order);
        }

        return $this->getData('order');
    }

    /**
     * Get ids of products that are in cart
     *
     * @return array
     */
    protected function _getOrderProductIds()
    {
        $ids = $this->getData('_order_product_ids');
        if (($ids === null) && ($this->_getOrder())) {
            $ids = [];
            foreach ($this->_getOrder()->getAllVisibleItems() as $orderItem) {
                $ids[] = $orderItem->getProduct()->getId();
            }
            $this->setData('_order_product_ids', $ids);
        }
        return $ids;
    }

    /**
     * @return int
     */
    protected function _getRelationType()
    {
        $relationType = Link::LINK_TYPE_RELATED;
        $productGridRelationType = $this->_wpHelper->getProductsGridProductsType();
        switch ($productGridRelationType) {
            case GridProductsType::TYPE_RELATED:
                $relationType = Link::LINK_TYPE_RELATED;
                break;
            case GridProductsType::TYPE_UPSELL:
                $relationType = Link::LINK_TYPE_UPSELL;
                break;
            case GridProductsType::TYPE_CROSSELL:
                $relationType = Link::LINK_TYPE_CROSSSELL;
                break;
        }
        return $relationType;
    }

    /**
     * @return int
     */
    protected function _getPageSize()
    {
        return $this->_wpHelper->getProductsGridNumberOfProducts();
    }

    /**
     * @return array|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getItems()
    {
        $items = $this->getData('items');
        if ($items === null) {
            $items = [];
            $ninProductIds = $this->_getOrderProductIds();
            if ($ninProductIds) {
                $linkCollection = $this->linkCollectionFactory->create()
                    ->addFieldToFilter('link_type_id', $this->_getRelationType())
                    ->addFieldToFilter('product_id', ['in' => $ninProductIds])
                    ->distinct(true)
                    ->getColumnValues('linked_product_id');

                if (count($linkCollection)) {
                    $collection = $this->productCollectionFactory->create()
                        ->addAttributeToFilter('entity_id', ['in' => $linkCollection])
                        ->setVisibility($this->_productVisibility->getVisibleInCatalogIds())
                        ->addStoreFilter($this->_storeManager->getStore()->getId())
                        ->setCurPage(1)
                        ->distinct(true);
                    if ($this->_getPageSize()) {
                        $collection->setPageSize($this->_getPageSize());
                    }
                    $collection->getSelect()->order('RAND()');
                    $this->_addProductAttributesAndPrices($collection);
                    foreach ($collection as $item) {
                        $items[] = $item;
                    }
                }
            }
            $this->setData('items', $items);
        }
        return $items;
    }

    public function getProductPriceHtml(
        \Magento\Catalog\Model\Product $product,
        $priceType,
        $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }
        $arguments['price_id'] = isset($arguments['price_id'])
            ? $arguments['price_id']
            : 'old-price-' . $product->getId() . '-' . $priceType;
        $arguments['include_container'] = isset($arguments['include_container'])
            ? $arguments['include_container']
            : true;
        $arguments['display_minimal_price'] = isset($arguments['display_minimal_price'])
            ? $arguments['display_minimal_price']
            : true;

        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');
        if (!$priceRender) {
            $priceRender = $this->getLayout()->createBlock(
                \Magento\Framework\Pricing\Render::class,
                'product.price.render.default',
                ['data' => ['price_render_handle' => 'catalog_product_prices']]
            );
        }

        $price = $priceRender->render(
            FinalPrice::PRICE_CODE,
            $product,
            $arguments
        );

        return $price;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->_wpHelper->getProductsGridTitle();
    }
}
