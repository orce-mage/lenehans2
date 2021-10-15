<?php

namespace WeltPixel\EnhancedEmail\Block\Sales\Order\Email\Items\Order;

use Magento\Downloadable\Model\Link;
use Magento\Downloadable\Model\Link\Purchased\Item;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;

/**
 * Downloadable Sales Order Email items renderer
 *
 * @api
 * @since 100.0.2
 */
class Downloadable extends \Magento\Sales\Block\Order\Email\Items\Order\DefaultOrder
{
    /**
     * @var \Magento\Downloadable\Model\Link\Purchased
     */
    protected $_purchased;

    /**
     * @var \Magento\Downloadable\Model\Link\PurchasedFactory
     */
    protected $_purchasedFactory;

    /**
     * @var \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory
     */
    protected $_itemsFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $frontendUrlBuilder;

    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    protected $_imageBuilder;

    /**
     * @var \WeltPixel\EnhancedEmail\Helper\Data
     */
    protected $_wpHelper;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * Downloadable constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Link\PurchasedFactory $purchasedFactory
     * @param \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory $itemsFactory
     * @param \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder
     * @param \WeltPixel\EnhancedEmail\Helper\Data $wpHelper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Downloadable\Model\Link\PurchasedFactory $purchasedFactory,
        \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory $itemsFactory,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \WeltPixel\EnhancedEmail\Helper\Data $wpHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Helper\Image $imageHelper,
        array $data = []
    ) {
        $this->_purchasedFactory = $purchasedFactory;
        $this->_itemsFactory = $itemsFactory;
        $this->_imageBuilder = $imageBuilder;
        $this->_wpHelper = $wpHelper;
        $this->_productRepository = $productRepository;
        $this->_imageHelper = $imageHelper;
        parent::__construct($context, $data);
    }

    /**
     * Enter description here...
     *
     * @return \Magento\Downloadable\Model\Link\Purchased
     */
    public function getLinks()
    {
        $this->_purchased = $this->_purchasedFactory->create()->load(
            $this->getItem()->getId(),
            'order_item_id'
        );
        $purchasedLinks = $this->_itemsFactory->create()->addFieldToFilter('order_item_id', $this->getItem()->getId());
        $this->_purchased->setPurchasedItems($purchasedLinks);

        return $this->_purchased;
    }

    /**
     * @return null|string
     */
    public function getLinksTitle()
    {
        return $this->getLinks()->getLinkSectionTitle() ?: $this->_scopeConfig->getValue(
            Link::XML_PATH_LINKS_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param Item $item
     * @return string
     */
    public function getPurchasedLinkUrl($item)
    {
        $url = $this->getFrontendUrlBuilder()->getUrl(
            'downloadable/download/link',
            [
                'id' => $item->getLinkHash(),
                '_scope' => $this->getOrder()->getStore(),
                '_secure' => true,
                '_nosid' => true
            ]
        );
        return $url;
    }

    /**
     * Get frontend URL builder
     *
     * @return \Magento\Framework\UrlInterface
     * @deprecated 100.1.0
     */
    private function getFrontendUrlBuilder()
    {
        if (!$this->frontendUrlBuilder) {
            $this->frontendUrlBuilder = ObjectManager::getInstance()->get(\Magento\Framework\Url::class);
        }
        return $this->frontendUrlBuilder;
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProduct()
    {
        if($productId = $this->getItem()->getProductId()) {
            $product = $this->_productRepository->getById($productId);
        } else {
            $product = $this->_productRepository->get($this->getItem()->getSku());
        }

        return $product;
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProductForThumbnail()
    {
        return $this->getProduct();
    }

    /**
     * @param $product
     * @param $imageId
     * @param array $attributes
     * @return bool|\Magento\Catalog\Block\Product\Image
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        if (!$this->_wpHelper->canShoWProductImage()) {
            return false;
        }
        //$img = $this->_getCachedImage($product, $imageId, $attributes );
        $img = $this->_getNonCachedImage($product);

        return $img;
    }

    /**
     * @param $product
     * @return bool|string
     */
    protected function _getNonCachedImage($product)
    {
        $catalogProductMediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product';

        if ($product->getThumbnail() && $product->getThumbnail() != 'no_selection') {
            return $catalogProductMediaUrl . DIRECTORY_SEPARATOR . ltrim( $product->getThumbnail(), DIRECTORY_SEPARATOR);
        } elseif ($product->getSmallImage() && $product->getSmallImage() != 'no_selection') {
            return $catalogProductMediaUrl .  DIRECTORY_SEPARATOR . ltrim($product->getSmallImage(), DIRECTORY_SEPARATOR);
        } else {

            $plHolder = $this->_imageHelper->getDefaultPlaceholderUrl('thumbnail');
            return $plHolder;
        }

    }

    /**
     * @param $product
     * @return bool
     */
    protected function _productHasImage($product)
    {
        if ($product->getThumbnail() && $product->getThumbnail() != 'no_selection') {
            return true;
        } elseif ($product->getSmallImage() && $product->getSmallImage() != 'no_selection') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $product
     * @param $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     */
    protected function _getCachedImage($product, $imageId, $attributes = [])
    {
        return $image = $this->_imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
    }
}
