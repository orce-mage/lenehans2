<?php

namespace StripeIntegration\Payments\Block;

use StripeIntegration\Payments\Helper\Logger;

class StripeElements extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'form/stripe-elements.phtml';

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \StripeIntegration\Payments\Helper\Generic $helper,
        \StripeIntegration\Payments\Helper\Address $addressHelper,
        array $data = []
    )
    {
        $this->helper = $helper;
        $this->addressHelper = $addressHelper;

        parent::__construct($context, $data);
    }

    public function isAdmin()
    {
        return $this->helper->isAdmin();
    }

    public function getAdminSourceOwner()
    {
        $quote = $this->helper->getQuote();
        $owner = $this->addressHelper->getStripeAddressFromMagentoAddress($quote->getBillingAddress());
        return json_encode($owner);
    }
}
