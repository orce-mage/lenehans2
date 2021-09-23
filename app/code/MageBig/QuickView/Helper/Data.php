<?php
/**
 * Copyright © 2016 MageBig . All rights reserved.
 */

namespace MageBig\QuickView\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_storeManager;
    protected $_scopeConfig;
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $context->getScopeConfig();
    }

    public function getQuickViewUrl($_product)
    {
        $id = $_product->getId();

        return $this->getBaseUrl().'quickview/view/index/id/'.$id;
    }

    public function getQuickViewButton($_product, $class = '')
    {
        if ($this->getConfig('quickview/general/active')) {
            $quickViewUrl = $this->getQuickViewUrl($_product);
            $quickViewTitle = __('Quick View');
            $quickViewLabel = "<i class='mbi mbi-eye'></i>";
            $html = "<button type='button' class=\"btn-quickview {$class}\" data-mfp-src=\"{$quickViewUrl}\" title=\"{$quickViewTitle}\">";
            $html .= "{$quickViewLabel}";
            if ($_product->getHasOptions()) {
                $html .= '<span class="has-option d-none"></span>';
            }
            $html .= '</button>';

            return $html;
        }

        return;
    }

    public function getConfig($fullPath)
    {
        return $this->_scopeConfig->getValue($fullPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getQuickViewLabel()
    {
        return $this->_scopeConfig->getValue('quickview/general/label', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }
}
