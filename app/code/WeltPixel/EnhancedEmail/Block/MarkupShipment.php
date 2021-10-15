<?php
/**
 * @category    WeltPixel
 * @package     WeltPixel_EnhancedEmail
 * @copyright   Copyright (c) 2018 Weltpixel
 * @author      Nagy Attila @ Weltpixel TEAM
 */

namespace WeltPixel\EnhancedEmail\Block;


/**
 * Class MarkupShipment
 * @package WeltPixel\EnhancedEmail\Block
 */
class MarkupShipment extends \Magento\Sales\Block\Order\Email\Shipment\Items
{
    /**
     * @var \Magento\Directory\Model\Region
     */
    protected $_region;
    /**
     * @var \Magento\Shipping\Model\Order\TrackFactory
     */
    protected $_trackFactory;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var \WeltPixel\EnhancedEmail\Helper\Data
     */
    protected $_helper;

    /**
     * MarkupShipment constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Directory\Model\Region $region
     * @param \Magento\Shipping\Model\Order\TrackFactory $trackFactory
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \WeltPixel\EnhancedEmail\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Directory\Model\Region $region,
        \Magento\Shipping\Model\Order\TrackFactory $trackFactory,
        \Magento\Catalog\Helper\Image $imageHelper,
        \WeltPixel\EnhancedEmail\Helper\Data $helper,
        array $data = []
    )
    {
        $this->_region = $region;
        $this->_trackFactory = $trackFactory;
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
     * return last tracking info
     *
     * @return bool|\Magento\Framework\Phrase|string
     */
    public function getTrackingData()
    {
        $trackData = false;
        $shipment = $this->getShipment();

        if(!$shipment) {
            return $trackData;
        }

        if(!$shipment->getTracks()) {
            return $trackData;
        }

        $trackObj = $this->_trackFactory->create();
        foreach($shipment->getTracks() as $track) {
            /** @var \Magento\Shipping\Model\Order\Track $track */
            $trackObj->load($track->getId());
            $trackData = $trackObj->getNumberDetail();

        }

        return $trackData;

    }

    /**
     * @return array
     */
    public function getShippingOrigin() {

        $shippingOrigin = [];

        $storeId = $this->getStoreData()->getId();
        $shippingOrigin['name'] = $this->getStoreData()->getName();
        $shippingOrigin['addressCountry'] = $this->_scopeConfig->getValue(
            \Magento\Shipping\Model\Config::XML_PATH_ORIGIN_COUNTRY_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $shippingOrigin['addressRegion'] = '';
        $regionId = $this->_scopeConfig->getValue(
            \Magento\Shipping\Model\Config::XML_PATH_ORIGIN_REGION_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if($regionId) {
            $region = $this->_region->load($regionId);
            $shippingOrigin['addressRegion'] =  $region->getCode();
        }

        $shippingOrigin['addressLocality'] = $this->_scopeConfig->getValue(
            \Magento\Shipping\Model\Config::XML_PATH_ORIGIN_CITY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $shippingOrigin['postalCode'] = $this->_scopeConfig->getValue(
            \Magento\Shipping\Model\Config::XML_PATH_ORIGIN_POSTCODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $streetOne = $this->_scopeConfig->getValue(
            'shipping/origin/street_line1',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $streetTwo = $this->_scopeConfig->getValue(
            'shipping/origin/street_line2',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $shippingOrigin['streetAddress'] = $streetOne . ' ' . $streetTwo;

        return $shippingOrigin;
    }


    /**
     * @return false|string
     */
    public function getExpectedArrivalUntil() {
        return date('c', strtotime('+5 days'));
    }



}