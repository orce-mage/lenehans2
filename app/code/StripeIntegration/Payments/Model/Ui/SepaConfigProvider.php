<?php

namespace StripeIntegration\Payments\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use StripeIntegration\Payments\Helper\Logger;
use Magento\Store\Model\ScopeInterface;

class SepaConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     */
    protected $methods = [];

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \StripeIntegration\Payments\Helper\Generic $helper,
        \StripeIntegration\Payments\Model\Config $config
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->config = $config;
    }


    public function getConfig()
    {
        // Get Stripe Account info
        try
        {
            $storeId = $this->helper->getStoreId();
            $businessName = $this->scopeConfig->getValue("payment/stripe_payments_sepa/business_name", ScopeInterface::SCOPE_STORE, $storeId);

            if (empty($businessName) && $this->config->canInitialize())
            {
                $account = \Stripe\Account::retrieve();

                if (empty($account->business_name))
                    $businessName = $this->storeManager->getStore()->getName();
                else
                    $businessName = $account->business_name;

                if (!empty($businessName))
                    $this->config->setConfigData("business_name", $businessName, "sepa");
            }
        }
        catch (\Exception $e)
        {
            $businessName = "Our Business";
        }

        $config = [
            'payment' => [
                \StripeIntegration\Payments\Model\Method\Sepa::METHOD_CODE => [
                    'iban' => '',
                    'company' => $businessName
                ],
            ]
        ];

        return $config;
    }
}
