<?php
namespace Data8\DataCaptureValidation\Helper;

class SettingsData extends \Magento\Framework\App\Helper\AbstractHelper
{

	/**
	 * @var \Magento\Framework\Escaper
	 */
	protected $_escaper;
    
	/**
	 * @param \Magento\Framework\App\Helper\Context $context
	 * @param \Magento\Framework\Escaper $_escaper
	 */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Framework\Escaper $escaper
	) {
		$this->_escaper = $escaper;
		parent::__construct($context);
	}
	
	public function getAjaxKey() {
		return $this->scopeConfig->getValue('Data8/d8_General/key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	public function getLicense() {
		return $this->scopeConfig->getValue('Data8/d8_AddressCaptureGroup/license', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	public function getUsePredictiveAddress() {
		return $this->scopeConfig->getValue('Data8/d8_AddressCaptureGroup/usePredictiveAddress', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}

	public function getPredictiveAddressOptions() {
		return $this->scopeConfig->getValue('Data8/d8_AddressCaptureGroup/predictiveAddress_options', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}

	public function getUseSalaciousName() {
		return $this->scopeConfig->getValue('Data8/d8_ValidationServicesGroup/useSalaciousName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}

	public function getUseOnBlur() {
		return $this->scopeConfig->getValue('Data8/d8_ValidationServicesGroup/useOnBlur', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}

	public function getEmailValidationLevel() {
		return $this->scopeConfig->getValue('Data8/d8_ValidationServicesGroup/emailValidationLevel', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	public function getUseTelephoneValidation() {
		return $this->scopeConfig->getValue('Data8/d8_ValidationServicesGroup/telephoneValidation', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	public function getDefaultCountryCode() {
		$defcc = $this->scopeConfig->getValue('Data8/d8_ValidationServicesGroup/defaultCountryCode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
		if ($defcc == '')
			return '44';
		
		return $defcc;
	}

}
