<?php

namespace MageBig\WidgetPlus\App\Customer;

class VisitorPlugin {

    protected $_cookieManager;

    public function __construct(
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
    ) {
        $this->_cookieManager = $cookieManager;
    }

    public function afterInitByRequest ($subject, $result, $observer) {
        if ($subject->getId()) {
            $visitorId = (int) $subject->getId();
            $this->_cookieManager->setPublicCookie('visitor_id', $visitorId);
        }
        return $result;
    }
}