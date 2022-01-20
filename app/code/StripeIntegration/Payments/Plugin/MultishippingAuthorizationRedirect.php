<?php

namespace StripeIntegration\Payments\Plugin;

use Magento\Framework\Exception\CouldNotSaveException;
use StripeIntegration\Payments\Helper\Logger;

class MultishippingAuthorizationRedirect
{
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\CacheInterface $cache
    ) {
        $this->customerSession = $customerSession;
        $this->cache = $cache;
    }

    public function aroundGetPostActionUrl(
        \Magento\Multishipping\Block\Checkout\Billing $block,
        \Closure $proceed
    ) {
        return $proceed();
    }
}
