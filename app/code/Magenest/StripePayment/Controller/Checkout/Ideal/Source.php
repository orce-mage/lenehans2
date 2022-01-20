<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 15:02
 */

namespace Magenest\StripePayment\Controller\Checkout\Ideal;

class Source extends \Magenest\StripePayment\Controller\Checkout\Source
{
    protected function getReturnUrl()
    {
        $returnUrl = $this->storeManagerInterface->getStore()->getBaseUrl()."stripe/checkout_ideal/response";
        return $returnUrl;
    }

    protected function getSourceType()
    {
        return "ideal";
    }

    protected function getCustomRequest()
    {
        $bank = $this->getRequest()->getParam('bankValue');
        $request = [];
        if ($bank) {
            $request[$this->getSourceType()]['bank'] = $bank;
        }
        return $request;
    }
}
