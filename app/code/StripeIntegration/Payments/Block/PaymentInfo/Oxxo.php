<?php

namespace StripeIntegration\Payments\Block\PaymentInfo;

use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;
use StripeIntegration\Payments\Gateway\Response\FraudHandler;

class Oxxo extends \StripeIntegration\Payments\Block\Info
{
    protected $_template = 'paymentInfo/oxxo.phtml';

    public function getVoucherLink()
    {
        $info = $this->getMethod()->getInfoInstance();
        $link = $info->getAdditionalInformation("voucher_link");
        if ($link)
            return "<a href=\"$link\" target=\"_blank\">Click here</a>";

        return __("N/A");
    }
}
