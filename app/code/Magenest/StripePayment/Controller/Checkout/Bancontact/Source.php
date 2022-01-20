<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Controller\Checkout\Bancontact;

class Source extends \Magenest\StripePayment\Controller\Checkout\Source
{
    protected function getReturnUrl()
    {
        $returnUrl = $this->storeManagerInterface->getStore()->getBaseUrl()."stripe/checkout_bancontact/response";
        return $returnUrl;
    }

    protected function getSourceType()
    {
        return "bancontact";
    }

    protected function getCustomRequest()
    {
        $language = $this->getRequest()->getParam('language');
        $request = [];
        if ($language) {
            $request[$this->getSourceType()] = [
                "preferred_language" => $language
            ];
        }
        return $request;
    }
}
