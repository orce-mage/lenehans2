<?php
/**
 * @category    WeltPixel
 * @package     WeltPixel_EnhancedEmail
 * @copyright   Copyright (c) 2018 Weltpixel
 * @author      Nagy Attila @ Weltpixel TEAM
 */

namespace WeltPixel\EnhancedEmail\Block\Order\Email\Items;

/**
 * Class GroupedItems
 * @package WeltPixel\EnhancedEmail\Block\Order\Item\Renderer
 */
class GroupedItems extends \Magento\Sales\Block\Order\Email\Items\Order\DefaultOrder
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
     * DefaultItems constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder
     * @param \WeltPixel\EnhancedEmail\Helper\Data $wpHelper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \WeltPixel\EnhancedEmail\Helper\Data $wpHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_imageBuilder = $imageBuilder;
        $this->_wpHelper = $wpHelper;
        $this->_productRepository = $productRepository;
    }


    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProduct()
    {
        if($this->getItem()->getSku()) {
            return  $this->_productRepository->get($this->getItem()->getSku());
        } else {
            return $this->getItem()->getProduct();
        }
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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
        if(!$this->_wpHelper->canShoWProductImage()) {
            return false;
        }
        return $this->_imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
    }

}