<?php

namespace MageBig\NewsPopup\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_storeManager;
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
    }

    public function getBaseMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    public function isHomePage()
    {
        $currentUrl = $this->_getUrl('', ['_current' => true]);
        $urlRewrite = $this->_getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);

        return $currentUrl == $urlRewrite;
    }

    public function getConfig($path)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
