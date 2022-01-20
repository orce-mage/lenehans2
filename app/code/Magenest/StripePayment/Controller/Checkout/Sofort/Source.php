<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Controller\Checkout\Sofort;

class Source extends \Magenest\StripePayment\Controller\Checkout\Source
{
    protected function getReturnUrl()
    {
        $returnUrl = $this->storeManagerInterface->getStore()->getBaseUrl()."stripe/checkout_sofort/response";
        return $returnUrl;
    }

    protected function getSourceType()
    {
        return "sofort";
    }

    protected function getCustomRequest()
    {
        $country = $this->getRequest()->getParam('country');
        $language = $this->getRequest()->getParam('language');
        $request[$this->getSourceType()] = [
            "country" => $country
        ];
        if ($language) {
            $request[$this->getSourceType()]["preferred_language"] = $language;
        }
        return $request;
    }
}
