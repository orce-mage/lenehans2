<?php

namespace StripeIntegration\Payments\Block;

use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;
use StripeIntegration\Payments\Gateway\Response\FraudHandler;

class Sepa extends \StripeIntegration\Payments\Block\Info
{
    protected $_template = 'form/sepa.phtml';
}
