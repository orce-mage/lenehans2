<?php

namespace StripeIntegration\Payments\Block\Ach;

use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;
use StripeIntegration\Payments\Gateway\Response\FraudHandler;

class Method extends \StripeIntegration\Payments\Block\Info
{
    protected $_template = 'form/ach.phtml';

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Gateway\ConfigInterface $paymentConfig,
        \StripeIntegration\Payments\Helper\Generic $helper,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $data);
        $this->helper = $helper;
    }

    public function getCountry()
    {
        $billingAddress = $this->helper->getQuote()->getBillingAddress();

        return $billingAddress->getCountryId();
    }

    public function getCurrency()
    {
        $cart = $this->helper->getQuote();

        return strtolower($cart->getQuoteCurrencyCode());
    }
}
