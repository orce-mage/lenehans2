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
class MarkupCreditmemo extends \Magento\Sales\Block\Order\Email\Creditmemo\Items
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $_productRepository;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var \WeltPixel\EnhancedEmail\Helper\Data
     */
    protected $_helper;

    /**
     * ProductItem constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Helper\Image $imageHelper,
        \WeltPixel\EnhancedEmail\Helper\Data $helper,
        array $data = []
    )
    {
        $this->_productRepository = $productRepository;
        $this->_imageHelper = $imageHelper;
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @param $product
     * @return bool|string
     */
    public function getProductImgUrl($product)
    {
        return $this->_helper->getNonCachedImageUrl($product);
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getStoreData()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * @param $link
     * @param array $params
     * @return string
     */
    public function getFrontUrl($link, $params = [])
    {
        return $this->_helper->getFrontendUrl($link, $params);
    }

}