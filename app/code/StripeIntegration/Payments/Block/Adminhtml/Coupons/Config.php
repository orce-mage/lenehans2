<?php

namespace StripeIntegration\Payments\Block\Adminhtml\Coupons;

use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;
use StripeIntegration\Payments\Gateway\Response\FraudHandler;
use StripeIntegration\Payments\Helper\Logger;

class Config extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Ui\Component\Layout\Tabs\TabInterface
{
    protected $_nameInLayout = 'stripe_subscriptions_coupons';

    public function __construct(
        \StripeIntegration\Payments\Model\Coupon $coupon,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        array $data = [],
        \Magento\SalesRule\Model\RuleFactory $ruleFactory = null
    ) {
        $this->coupon = $coupon;
        $this->_rendererFieldset = $rendererFieldset;
        $this->_conditions = $conditions;
        $this->ruleFactory = $ruleFactory ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\SalesRule\Model\RuleFactory::class);
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getTabClass()
    {
        return null;
    }

    public function getTabUrl()
    {
        return null;
    }

    public function isAjaxLoaded()
    {
        return false;
    }

    public function getTabLabel()
    {
        return __('Subscriptions by Stripe');
    }

    public function getTabTitle()
    {
        return __('Subscriptions by Stripe');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $ruleId = $this->_coreRegistry
            ->registry(\Magento\SalesRule\Model\RegistryConstants::CURRENT_SALES_RULE)
            ->getId();

        $this->coupon->load($ruleId, 'rule_id');

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('stripe_subscriptions_');
        $renderer = $this->_rendererFieldset->setTemplate('Magento_CatalogRule::promo/fieldset.phtml');
        $fieldset = $form->addFieldset('coupons_config', ['legend' => "Coupon Duration"])->setRenderer($renderer);;

        $fieldset->addField('coupon_duration', 'select', [
            'label' => __('Applies'),
            'name' => 'coupon_duration',
            'options' => $this->getDurations(),
            'required' => false,
            'value' => $this->coupon->duration(),
            'note' => __('DEPRECATION WARNING - Expiring discount coupons only apply with the embedded payment flow (Stripe Elements).'),
            'data-form-part' => 'sales_rule_form'
        ]);

        $fieldset->addField('coupon_months', 'text', [
            'label' => __('Number of months'),
            'name' => 'coupon_months',
            'required' => false,
            'value' => $this->coupon->months(),
            'note' => __('This value is only used if the coupon duration is "Multiple months".'),
            'data-form-part' => 'sales_rule_form'
        ]);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getDurations()
    {
        return [
            'forever' => __('Forever'),
            'once' => __('Once'),
            'repeating' => __('Multiple months')
        ];
    }
}
