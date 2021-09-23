<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Block\Email\Block\Quote;

class Totals extends \Magento\Checkout\Block\Cart\Totals
{
    private $mainNode = null;

    public $moduleHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Config $salesConfig,
        \Magetrend\Email\Helper\Data $moduleHelper,
        array $layoutProcessors = [],
        array $data = []
    ) {
        $this->moduleHelper = $moduleHelper;
        parent::__construct($context, $customerSession, $checkoutSession, $salesConfig, $layoutProcessors, $data);
    }

    public function getVarModel()
    {
        return $this->getMainNode()->getVarModel();
    }

    public function getMainNode()
    {
        if ($this->mainNode == null) {
            $this->mainNode = $this->getParentBlock()->getParentBlock();
        }

        return $this->mainNode;
    }

    public function formatValue($price)
    {
        return $this->moduleHelper->formatPrice($this->getQuote()->getCurrencyCode(), $price);
    }

    public function getCustomQuote()
    {
        return $this->getMainNode()->getQuote();
    }
}
