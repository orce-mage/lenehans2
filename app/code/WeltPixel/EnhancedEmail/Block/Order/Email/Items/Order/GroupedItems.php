<?php
/**
 * @category    WeltPixel
 * @package     WeltPixel_EnhancedEmail
 * @copyright   Copyright (c) 2018 Weltpixel
 * @author      Nagy Attila @ Weltpixel TEAM
 */

namespace WeltPixel\EnhancedEmail\Block\Order\Email\Items\Order;

/**
 * Class GroupedItems
 * @package WeltPixel\EnhancedEmail\Block\Order\Item\Renderer
 */
class GroupedItems extends \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer
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
     * GroupedItems constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory
     * @param \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder
     * @param \WeltPixel\EnhancedEmail\Helper\Data $wpHelper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \WeltPixel\EnhancedEmail\Helper\Data $wpHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        array $data = []
    )
    {
        parent::__construct($context, $string, $productOptionFactory, $data);
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
    /**
     * Get the html for item price
     *
     * @param OrderItem|InvoiceItem|CreditmemoItem $item
     * @return string
     */
    public function getItemPrice($item)
    {
        $block = $this->getLayout()->getBlock('item_price');
        $block->setItem($item);
        return $block->toHtml();
    }

}