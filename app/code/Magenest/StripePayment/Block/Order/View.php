<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\StripePayment\Block\Order;

class View extends \Magento\Sales\Block\Order\View
{
    protected $_config;

    public function __construct(
        \Magenest\StripePayment\Helper\Config $config,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Payment\Helper\Data $paymentHelper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $httpContext, $paymentHelper, $data);
        $this->_config = $config;
    }

    public function getStripePublickey()
    {
        return $this->_config->getPublishableKey();
    }
}
