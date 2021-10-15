<?php
/**
 * @category    WeltPixel
 * @package     WeltPixel_EnhancedEmail
 * @copyright   Copyright (c) 2018 Weltpixel
 * @author      Nagy Attila @ Weltpixel TEAM
 */

namespace WeltPixel\EnhancedEmail\Block\Order\Email;

use Magento\Framework\View\Element\Template;

/**
 * Class Items
 * @package WeltPixel\EnhancedEmail\Block\Order\Email
 */
class Items extends \Magento\Sales\Block\Order\Email\Items
{
    /**
     * @var \WeltPixel\EnhancedEmail\Helper\Data
     */
    protected $_wpHelper;

    /**
     * Items constructor.
     * @param \WeltPixel\EnhancedEmail\Helper\Data $wpHelper
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        \WeltPixel\EnhancedEmail\Helper\Data $wpHelper,
        Template\Context $context,
        array $data = [])
    {
        $this->_wpHelper = $wpHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function canShowProductImage()
    {
        return $this->_wpHelper->canShowProductImage();
    }
}