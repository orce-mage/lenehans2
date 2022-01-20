<?php

namespace StripeIntegration\Payments\Plugin;

use StripeIntegration\Payments\Helper\Logger;

class CsrfValidatorSkip
{
    public function aroundValidate(
        $subject,
        \Closure $proceed,
        $request,
        $action
    ) {
        // stripe is the route name
        if ($request->getModuleName() == 'stripe') {
            return; // Skip CSRF check
        }
        $proceed($request, $action);
    }
}
