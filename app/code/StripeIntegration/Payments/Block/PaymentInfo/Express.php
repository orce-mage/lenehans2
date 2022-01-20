<?php

namespace StripeIntegration\Payments\Block\PaymentInfo;

use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;
use StripeIntegration\Payments\Gateway\Response\FraudHandler;

class Express extends \StripeIntegration\Payments\Block\Info
{
    protected $_template = 'paymentInfo/express.phtml';
}
