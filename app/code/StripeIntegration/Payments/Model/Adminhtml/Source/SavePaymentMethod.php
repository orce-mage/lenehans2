<?php

namespace StripeIntegration\Payments\Model\Adminhtml\Source;

class SavePaymentMethod extends \Magento\Config\Block\System\Config\Form\Field
{
    public function __construct(
        \StripeIntegration\Payments\Model\Config $config,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->config = $config;
        parent::__construct($context, $data);
    }

    public function toOptionArray()
    {
        return [
            [
                'value' => 0,
                'label' => __('Disabled')
            ],
            [
                'value' => 1,
                'label' => __('Enabled')
            ]
        ];
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if ($this->config->isAuthorizeOnly() && $this->config->retryWithSavedCard())
        {
            $element->setDisabled(true);
            return "<p>Enabled (via \"Expired authorizations\" setting)</p>";
        }

        return parent::_getElementHtml($element);
    }
}
