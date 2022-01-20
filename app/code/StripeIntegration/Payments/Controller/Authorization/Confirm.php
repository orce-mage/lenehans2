<?php

namespace StripeIntegration\Payments\Controller\Authorization;

use Magento\Framework\Exception\LocalizedException;
use StripeIntegration\Payments\Helper\Logger;
use Magento\Framework\Controller\ResultFactory;

class Confirm extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \StripeIntegration\Payments\Helper\Generic $helper,
        \StripeIntegration\Payments\Helper\Multishipping $multishippingHelper
    )
    {
        parent::__construct($context);

        $this->helper = $helper;
        $this->multishippingHelper = $multishippingHelper;
    }

    public function execute()
    {
        $outcomes = $this->multishippingHelper->confirmPaymentsForSessionOrders();

        if (!empty($outcomes["hasErrors"]))
        {
            foreach ($outcomes["orders"] as $outcome)
            {
                if ($outcome["success"])
                    $this->helper->addSuccess($outcome["message"]);
                else
                    $this->helper->addError($outcome["message"]);
            }
            $this->_redirect('multishipping/checkout/addresses');
        }
        else
            $this->_redirect('multishipping/checkout/success');
    }
}
