<?php

namespace StripeIntegration\Payments\Model\Adminhtml\Source;

class PaymentMethodsConfiguration extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $_template = 'StripeIntegration_Payments::config/payment_methods_configuration.phtml';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->storeManager = $context->getStoreManager();

        parent::__construct($context, $data);
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }
}
