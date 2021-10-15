<?php
/**
 * @category    WeltPixel
 * @package     WeltPixel_EnhancedEmail
 * @copyright   Copyright (c) 2018 Weltpixel
 * @author      Nagy Attila @ Weltpixel TEAM
 */

namespace WeltPixel\EnhancedEmail\Block;

/**
 * Class Markup
 * @package WeltPixel\EnhancedEmail\Block
 */
class MarkupOrder extends \Magento\Sales\Block\Order\Email\Items
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $_productRepository;

    /**
     * @var \WeltPixel\EnhancedEmail\Helper\Data
     */
    protected $_helper;

    /**
     * MarkupOrder constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \WeltPixel\EnhancedEmail\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \WeltPixel\EnhancedEmail\Helper\Data $helper,
        array $data = []
    )
    {
        $this->_productRepository = $productRepository;
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @param $product
     * @return bool|string
     */
    public function getProductImgUrl($product)
    {
        return $this->_helper->getNonCachedProductImageUrl($product);
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getStoreData()
    {
        return $this->_storeManager->getStore();
    }

}