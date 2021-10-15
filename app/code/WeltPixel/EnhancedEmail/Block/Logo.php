<?php
/**
 * @category    WeltPixel
 * @package     WeltPixel_EnhancedEmail
 * @copyright   Copyright (c) 2018 Weltpixel
 * @author      Nagy Attila @ Weltpixel TEAM
 */

namespace WeltPixel\EnhancedEmail\Block;

/**
 * Class PreHeader
 * @package WeltPixel\EnhancedEmail\Block
 */
class Logo extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \WeltPixel\EnhancedEmail\Helper\Data
     */
    protected $_wpHelper;

    /**
     * PreHeader constructor.
     * @param \WeltPixel\EnhancedEmail\Helper\Data $wpHelper
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \WeltPixel\EnhancedEmail\Helper\Data $wpHelper,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    )
    {
        $this->_wpHelper = $wpHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed|string
     */
    public function getLightLogo()
    {
        if ($this->_wpHelper->isLightLogoUploaded()) {
            return $this->_wpHelper->getLogoSrc() . $this->_wpHelper->getLightLogo();
        }
        return $this->_wpHelper->getLightLogo();
    }

    /**
     * @return mixed|string
     */
    public function getDarkLogo()
    {
        if ($this->_wpHelper->isDarkLogoUploaded()) {
            return $this->_wpHelper->getLogoSrc() . $this->_wpHelper->getDarkLogo();
        }

        return $this->_wpHelper->getDarkLogo();
    }

    /**
     * @return mixed
     */
    public function getLightLogoWidth()
    {
        return $this->_wpHelper->getLightLogoWidth();
    }

    /**
     * @return mixed
     */
    public function getLightLogoHeight()
    {
        return $this->_wpHelper->getLightLogoHeight();
    }

    /**
     * @return mixed
     */
    public function getLightLogoAlt()
    {
        return $this->_wpHelper->getLightLogoAlt();
    }

    /**
     * @return mixed
     */
    public function getLightLogoLink()
    {
        return $this->_wpHelper->getLightLogoLink();
    }

    /**
     * @return mixed
     */
    public function getDarkLogoWidth()
    {
        return $this->_wpHelper->getDarkLogoWidth();
    }

    /**
     * @return mixed
     */
    public function getDarkLogoHeight()
    {
        return $this->_wpHelper->getDarkLogoHeight();
    }

    /**
     * @return mixed
     */
    public function getDarkLogoAlt()
    {
        return $this->_wpHelper->getDarkLogoAlt();
    }

    /**
     * @return mixed
     */
    public function getDarkLogoLink()
    {
        return $this->_wpHelper->getDarkLogoLink();
    }

}