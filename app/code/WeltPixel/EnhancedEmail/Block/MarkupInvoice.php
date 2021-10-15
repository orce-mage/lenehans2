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
class MarkupInvoice extends \Magento\Sales\Block\Order\Email\Invoice\Items
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
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_priceHelper;

    /**
     * @var \WeltPixel\EnhancedEmail\Helper\Data
     */
    protected $_helper;

    /**
     * MarkupInvoice constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \WeltPixel\EnhancedEmail\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \WeltPixel\EnhancedEmail\Helper\Data $helper,
        array $data = []
    )
    {
        $this->_productRepository = $productRepository;
        $this->_imageHelper = $imageHelper;
        $this->_priceHelper = $priceHelper;
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

    /**
     * Return formated price with currency symbol
     *
     * @param $price
     * @return float|string
     */
    public function getFormatedPrice($price)
    {
        return $this->_priceHelper->currency($price, true, false);
    }

    /**
     * Return date ISO format
     *
     * @param $date
     * @return string
     */
    public function getFormatedDate($date)
    {
        return $this->_helper->getDateIsoFormat($date);
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