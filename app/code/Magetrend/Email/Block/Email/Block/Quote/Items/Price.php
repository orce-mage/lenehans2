<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Block\Email\Block\Quote\Items;

class Price extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magetrend\Email\Helper\Data
     */
    public $moduleHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magetrend\Email\Helper\Data $moduleHelper,
        array $data = []
    ) {
        $this->moduleHelper = $moduleHelper;
        parent::__construct($context, $data);
    }

    public function getModuleHelper()
    {
        return $this->moduleHelper;
    }

    public function formatPrice($price)
    {
        $item = $this->getItem();
        if ($item instanceof \Magento\Quote\Api\Data\CartItemInterface) {
            $currency = $item->getQuote()->getCurrencyCode();
            return $this->getModuleHelper()->formatPrice($currency, $price);
        }

        return $this->getOrder()->formatPrice($price);
    }

    public function getOrder()
    {
        $item = $this->getItem();
        if ($item instanceof \Magento\Sales\Model\Order\Creditmemo\Item) {
            return $item->getCreditmemo()->getOrder();
        } elseif ($item instanceof \Magento\Sales\Model\Order\Invoice\Item) {
            return $item->getInvoice()->getOrder();
        }

        return $item->getOrder();
    }
}
