<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Plugin\Framework\App\Request;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Area;

class CsrfByPass
{
    const BY_PASS_URI = 'stripe/checkout/webhooks';

    public function aroundValidate(
        \Magento\Framework\App\Request\CsrfValidator $validator,
        callable $proceed,
        RequestInterface $request,
        ActionInterface $action
    ) {
        if (strpos($request->getPathInfo(), self::BY_PASS_URI) !== false) {
            return true;
        } else {
            return $proceed($request, $action);
        }
    }
}
