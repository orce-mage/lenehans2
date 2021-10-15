<?php
/**
 * @category    WeltPixel
 * @package     WeltPixel_EnhancedEmail
 * @copyright   Copyright (c) 2018 Weltpixel
 * @author      Nagy Attila @ Weltpixel TEAM
 */

namespace WeltPixel\EnhancedEmail\Block\Order\Invoice;

/**
 * Class Totals
 * @package WeltPixel\EnhancedEmail\Block\Order\Invoice
 */
class Totals extends \Magento\Sales\Block\Order\Invoice\Totals
{
    /**
     * @var Data|\WeltPixel\EnhancedEmail\Helper\Data
     */
    protected $_wpHelper;

    /**
     * Totals constructor.
     * @param \WeltPixel\EnhancedEmail\Helper\Data $wpHelper
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \WeltPixel\EnhancedEmail\Helper\Data $wpHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $data['label_properties'] = 'colspan="2"';
        $this->_wpHelper = $wpHelper;
        if($this->_wpHelper->canShowProductImage()) {
            $data['label_properties'] = 'colspan="3"';
        }
        parent::__construct($context, $registry, $data);
    }
}