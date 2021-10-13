<?php
/**
 * @author CynoInfotech Team
 * @package Cynoinfotech_DynamicConfigurableProduct
 */
namespace Cynoinfotech\DynamicConfigurableProduct\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
    }
    
    public function getConfig($configPath)
    {
        return $this->scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
	
	public function getRequireJsTemplate()
    {
        if ($this->getConfig('cynoinfotech_dynamicconfigurableproduct/general/enable')) {
             $template =  'Cynoinfotech_DynamicConfigurableProduct::require_js.phtml';
        } else {
             $template =  'Magento_Theme::page/js/require_js.phtml';
        }
        return $template;
    }
}
