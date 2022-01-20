<?php

namespace StripeIntegration\Payments\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use StripeIntegration\Payments\Helper\Logger;
use Magento\Store\Model\ScopeInterface;

class FpxConfigProvider implements ConfigProviderInterface
{

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var PaymentHelper
     */
    protected $_paymentHelper;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface $config
     */
    protected $_config;

    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     */
    protected $methods = [];

    /**
     * @param \Magento\Framework\Model\Context                   $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param PaymentHelper                                      $paymentHelper
     * @param \Magento\Framework\Locale\ResolverInterface        $localeResolver
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        PaymentHelper $paymentHelper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \StripeIntegration\Payments\Helper\Generic $helper
    ) {
        $this->_appState = $context->getAppState();
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_storeManager = $storeManager;
        $this->_paymentHelper = $paymentHelper;
        $this->_localeResolver = $localeResolver;
        $this->_scopeConfig = $scopeConfig;
        $this->_helper = $helper;
    }


    public function getBanks()
    {
        return [
            [ "value" => "affin_bank", "label" => "Affin Bank"],
            [ "value" => "alliance_bank", "label" => "Alliance Bank"],
            [ "value" => "ambank", "label" => "AmBank"],
            [ "value" => "bank_islam", "label" => "Bank Islam"],
            [ "value" => "bank_rakyat", "label" => "Bank Rakyat"],
            [ "value" => "bank_muamalat", "label" => "Bank Muamalat"],
            [ "value" => "bsn", "label" => "BSN"],
            [ "value" => "cimb", "label" => "CIMB Clicks"],
            [ "value" => "hong_leong_bank", "label" => "Hong Leong Bank"],
            [ "value" => "hsbc", "label" => "HSBC BANK"],
            [ "value" => "kfh", "label" => "KFH"],
            [ "value" => "maybank2u", "label" => "Maybank2U"],
            [ "value" => "maybank2e", "label" => "Maybank2E"],
            [ "value" => "ocbc", "label" => "OCBC Bank"],
            [ "value" => "public_bank", "label" => "Public Bank"],
            [ "value" => "rhb", "label" => "RHB Bank"],
            [ "value" => "standard_chartered", "label" => "Standard Chartered"],
            [ "value" => "uob", "label" => "UOB Bank"]
        ];
    }

    public function getConfig()
    {
        $config = [
            'payment' => [
                \StripeIntegration\Payments\Model\Method\Fpx::METHOD_CODE => [
                    'banks' => $this->getBanks()
                ],
            ]
        ];

        return $config;
    }
}
