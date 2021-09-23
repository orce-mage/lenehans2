<?php

namespace Swissup\CheckoutSuccess\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    /**
     * @var array
     */
    private $modules = ['checkout' => 'onepage/success'];

    /**
     * Get value of Swissup Success Page configuration
     *
     * @param  string $key
     * @return string
     */
    public function getConfigValue($key)
    {
        return $this->scopeConfig->getValue(
            "success_page/" . $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check is it success page
     *
     * @return boolean
     */
    public function isOnSuccessPage()
    {
        $request = $this->_getRequest();
        $name = $request->getModuleName();
        if (isset($this->modules[$name])) {
            $controllerAction = $request->getControllerName()
                . '/'
                . $request->getActionName();
            return $this->modules[$name] == $controllerAction;
        }

        return false;
    }
}
