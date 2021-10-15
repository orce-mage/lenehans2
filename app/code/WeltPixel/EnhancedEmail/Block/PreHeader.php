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
class PreHeader extends \Magento\Framework\View\Element\Template
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
     * @return $this|bool
     */
    public function getPreHeader()
    {
        $preheader = '';
        $template = $this->_wpHelper->getTemplate();
        if($template) {
            $preheader = $template->getTemplatePreheader();
        }
        return $preheader;
    }

}