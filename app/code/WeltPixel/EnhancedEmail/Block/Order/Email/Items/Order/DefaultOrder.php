<?php
/**
 * @category    WeltPixel
 * @package     WeltPixel_EnhancedEmail
 * @copyright   Copyright (c) 2018 Weltpixel
 * @author      Nagy Attila @ Weltpixel TEAM
 */

namespace WeltPixel\EnhancedEmail\Block\Order\Email\Items\Order;

use Magento\Framework\View\Element\Template;

/**
 * Class DefaultItems
 * @package WeltPixel\EnhancedEmail\Block\Order\Email\Items
 */
class DefaultOrder extends \Magento\Sales\Block\Order\Email\Items\Order\DefaultOrder
{

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
     * DefaultOrder constructor.
     * @param \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder
     * @param \WeltPixel\EnhancedEmail\Helper\Data $wpHelper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \WeltPixel\EnhancedEmail\Helper\Data $wpHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Helper\Image $imageHelper,
        Template\Context $context,
        array $data = []
    )
    {
        $this->_imageBuilder = $imageBuilder;
        $this->_wpHelper = $wpHelper;
        $this->_productRepository = $productRepository;
        $this->_imageHelper = $imageHelper;
        parent::__construct($context, $data);
    }


    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProduct()
    {
        if ($this->getItem()->getProductOptionByCode('simple_sku')) {
            $product = $this->_productRepository->get($this->getItem()->getProductOptionByCode('simple_sku'));
            if($this->_productHasImage($product)) {
                return $product;
            } else {
                $configProduct = $this->_productRepository->get($this->getItem()->getProduct()->getSku());
                return $configProduct;
            }
        } elseif($this->getItem()->getProductType() == 'grouped') {
            $groupedProduct = $this->_productRepository->get($this->getItem()->getSku());
            return $groupedProduct;

        } else {
            $configProduct = $this->_productRepository->get($this->getItem()->getProduct()->getSku());
            return $configProduct;
        }
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
