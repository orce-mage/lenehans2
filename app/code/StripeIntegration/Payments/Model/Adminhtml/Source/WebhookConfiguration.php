<?php

namespace StripeIntegration\Payments\Model\Adminhtml\Source;

class WebhookConfiguration extends \Magento\Config\Block\System\Config\Form\Field
{
    public $webhooksSetup;
    protected $_template = 'StripeIntegration_Payments::config/webhooks_configuration.phtml';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \StripeIntegration\Payments\Helper\WebhooksSetup $webhooksSetup,
        \StripeIntegration\Payments\Model\Config $config,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    ) {
        $this->webhooksSetup = $webhooksSetup;
        $this->storeManager = $context->getStoreManager();
        $this->config = $config;
        $this->request = $request;
        $this->storeId = $this->getAdminConfigStoreId();
        $key = $this->config->getSecretKey(null, $this->storeId);
        if (empty($key))
            $this->_template = 'StripeIntegration_Payments::config/webhooks_configuration_disabled.phtml';

        parent::__construct($context, $data);
    }

    public function getAdminConfigStoreId()
    {
        $storeId = (int)$this->request->getParam('store', 0);
        $websiteId = (int)$this->request->getParam('website', 0);

        if ($storeId)
            return $storeId;
        else if ($websiteId)
            return $this->storeManager->getWebsite($websiteId)->getDefaultStore()->getId();

        return 0; // Default store
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

    public function getAjaxUrl()
    {
        return $this->getUrl('stripe/configure/webhooks');
    }

    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'stripe_configure_webhooks',
                'label' => __('Configure'),
            ]
        );

        return $button->toHtml();
    }

    public function getDisabledButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'stripe_configure_webhooks',
                'label' => __('Configure'),
                'disabled' => true
            ]
        );

        return $button->toHtml();
    }
}
