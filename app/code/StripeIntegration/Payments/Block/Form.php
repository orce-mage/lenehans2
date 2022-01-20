<?php

namespace StripeIntegration\Payments\Block;

use StripeIntegration\Payments\Helper\Logger;

class Form extends \Magento\Payment\Block\Form\Cc
{
    protected $_template = 'form/stripe_payments.phtml';

    public $config;
    public $setupIntent;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Model\Config $paymentConfig,
        \StripeIntegration\Payments\Model\Config $config,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \StripeIntegration\Payments\Helper\Generic $helper,
        \StripeIntegration\Payments\Helper\SetupIntent $setupIntent,
        \Magento\Framework\Data\Form\FormKey $formKey,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $data);
        $this->config = $config;
        $this->stripeCustomer = $helper->getCustomerModel();
        $this->productMetadata = $productMetadata;
        $this->helper = $helper;
        $this->formKey = $formKey;
        $this->setupIntent = $setupIntent;
    }

    public function getFormKey()
    {
         return $this->formKey->getFormKey();
    }

    public function getCustomerCards()
    {
        return $this->stripeCustomer->getCustomerCards();
    }

    public function isSinglePaymentMethod()
    {
        return count($this->getParentBlock()->getMethods()) == 1;
    }

    public function showSaveCardInAdmin()
    {
        return ($this->config->getSaveCards() && $this->helper->getCustomerId());
    }

    public function isNewCustomer()
    {
        if ($this->helper->isAdmin() && !$this->helper->getCustomerId())
            return true;

        return false;
    }

    public function cardType($code)
    {
        return $this->helper->cardType($code);
    }

    public function getSaveCardsChecked()
    {
        if ($this->config->isSaveCardCheckboxChecked())
            return "checked";

        return "";
    }
}
