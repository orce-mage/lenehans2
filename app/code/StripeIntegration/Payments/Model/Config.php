<?php

namespace StripeIntegration\Payments\Model;

use StripeIntegration\Payments\Helper;
use StripeIntegration\Payments\Helper\Logger;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Exception\LocalizedException;

class Config
{
    public static $moduleName           = "Magento2";
    public static $moduleVersion        = "2.8.3";
    public static $minStripePHPVersion  = "7.100.0";
    public static $moduleUrl            = "https://stripe.com/docs/plugins/magento";
    public static $partnerId            = "pp_partner_Fs67gT2M6v3mH7";
    const STRIPE_API                    = "2020-03-02";
    public $isInitialized               = false;
    public $isSubscriptionsEnabled      = null;
    public static $stripeClient         = null;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Helper\Generic $helper,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \StripeIntegration\Payments\Helper\Locale $localeHelper,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \StripeIntegration\Payments\Model\ResourceModel\StripeCustomer\Collection $stripeCustomerCollection,
        \StripeIntegration\Payments\Helper\SetupIntentFactory $setupIntentFactory,
        \Magento\Tax\Model\Config $taxConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->encryptor = $encryptor;
        $this->localeHelper = $localeHelper;
        $this->resourceConfig = $resourceConfig;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->stripeCustomerCollection = $stripeCustomerCollection;
        $this->setupIntentFactory = $setupIntentFactory;
        $this->taxConfig = $taxConfig;

        $this->isInitialized = $this->initStripe();
    }

    public function getComposerRequireVersion()
    {
        $version = explode(".", \StripeIntegration\Payments\Model\Config::$minStripePHPVersion);
        array_pop($version);
        return implode(".", $version);
    }

    public function canInitialize()
    {
        if (!class_exists('Stripe\Stripe'))
        {
            $this->logger->critical("The Stripe PHP library dependency has not been installed. Please follow the installation instructions at https://stripe.com/docs/plugins/magento/install#manual");
            return false;
        }

        if (version_compare(\Stripe\Stripe::VERSION, \StripeIntegration\Payments\Model\Config::$minStripePHPVersion) < 0)
        {
            $version = \StripeIntegration\Payments\Model\Config::$moduleVersion;
            $libVersion = $this->getComposerRequireVersion();
            $this->logger->critical("Stripe Payments v$version now depends on Stripe PHP library v$libVersion or newer. Please upgrade your installed Stripe PHP library with the command: composer require stripe/stripe-php:^$libVersion");
            return false;
        }

        return true;
    }

    public function initStripe($mode = null)
    {
        if ($this->isInitialized)
            return true;

        if (!$this->canInitialize())
            return false;

        if ($this->getSecretKey($mode) && $this->getPublishableKey($mode))
        {
            $key = $this->getSecretKey($mode);
            return $this->initStripeFromSecretKey($key);
        }

        return false;
    }

    protected function initStripeFromSecretKey($key)
    {
        if (!$this->canInitialize())
            return false;

        if (empty($key))
            return false;

        try
        {
            \Stripe\Stripe::setApiKey($key);
            \Stripe\Stripe::setAppInfo($this::$moduleName, $this::$moduleVersion, $this::$moduleUrl, $this::$partnerId);

            $api = \StripeIntegration\Payments\Model\Config::STRIPE_API;

            \Stripe\Stripe::setApiVersion($api);
            $this::$stripeClient = new \Stripe\StripeClient([
                "api_key" => $key,
                "stripe_version" => $api
            ]);
        }
        catch (\Exception $e)
        {
            $this->helper->logError($e->getMessage());
            $this->helper->logError($e->getTraceAsString());
        }

        return true;
    }

    protected function initStripeFromPublicKey($key)
    {
        $secretKey = null;
        $stores = $this->storeManager->getStores();
        $configurations = array();

        foreach ($stores as $storeId => $store)
        {
            $testKeys = $this->getStoreViewAPIKey($store, 'test');
            if (!empty($testKeys['api_keys']['pk']) && $testKeys['api_keys']['pk'] == $key)
            {
                $secretKey = $testKeys['api_keys']['sk'];
                break;
            }

            $liveKeys = $this->getStoreViewAPIKey($store, 'live');
            if (!empty($liveKeys['api_keys']['pk']) && $liveKeys['api_keys']['pk'] == $key)
            {
                $secretKey = $liveKeys['api_keys']['sk'];
                break;
            }
        }

        return $this->initStripeFromSecretKey($secretKey);
    }

    public function reInitStripe($storeId, $currencyCode, $mode)
    {
        $this->isInitialized = false;
        $this->storeManager->setCurrentStore($storeId);
        $this->storeManager->getStore()->setCurrentCurrencyCode($currencyCode);
        return $this->isInitialized = $this->initStripe($mode);
    }

    public function reInitStripeFromCustomerId($customerId)
    {
        $customer = $this->stripeCustomerCollection->getByStripeCustomerId($customerId);
        if (!$customer)
            throw new LocalizedException(__("Could not find customer with ID %1", $customerId));

        if (!$customer->getPk())
            throw new LocalizedException(__("Could not find Stripe account for customer with ID %1", $customerId));

        $this->isInitialized = false;
        return $this->isInitialized = $this->initStripeFromPublicKey($customer->getPk());
    }

    public static function module()
    {
        return self::$moduleName . " v" . self::$moduleVersion;
    }

    public function getConfigData($field, $method = null, $storeId = null)
    {
        if (empty($storeId))
            $storeId = $this->helper->getStoreId();

        $section = "";
        if ($method)
            $section = "_$method";

        $data = $this->scopeConfig->getValue("payment/stripe_payments$section/$field", ScopeInterface::SCOPE_STORE, $storeId);

        return $data;
    }

    public function setConfigData($field, $value, $method = null, $scope = null, $storeId = null)
    {
        if (empty($storeId))
            $storeId = $this->helper->getStoreId();

        if (empty($scope))
            $scope = ScopeInterface::SCOPE_STORE;

        $section = "";
        if ($method)
            $section = "_$method";

        $data = $this->resourceConfig->saveConfig("payment/stripe_payments$section/$field", $value, $scope, $storeId);

        return $data;
    }

    public function getPRAPIDescription()
    {
        $seller = $this->getConfigData('seller_name', 'express');
        if (empty($seller))
            return __("Order Total");
        else
            return $seller;
    }

    public function getPRAPIButtonSettings()
    {
        return \Zend_Json::encode([
            'type' => $this->getConfigData('button_type', "express"),
            'theme' => $this->getConfigData('button_theme', "express"),
            'height' => $this->getConfigData('button_height', "express") . "px"
        ]);
    }

    public function isSubscriptionsEnabled($storeId = null)
    {
        if ($this->isSubscriptionsEnabled !== null)
            return $this->isSubscriptionsEnabled;

        $this->isSubscriptionsEnabled = ((bool)$this->getConfigData('active', 'subscriptions', $storeId)) && $this->initStripe();
        return $this->isSubscriptionsEnabled;
    }

    public function isLevel3DataEnabled()
    {
        return (bool)$this->getConfigData("level3_data");
    }

    public function isPaymentFailedEmailsEnabled()
    {
        return ((bool)$this->getConfigData('payment_failed_emails'));
    }

    public function isEnabled($method = null)
    {
        $enabled = ((bool)$this->getConfigData('active', $method)) && $this->initStripe();

        if ($enabled)
        {
            if ($method == "checkout")
                return $this->isRedirectPaymentFlow();
            else
            {
                if ($this->helper->isAdmin())
                    return true;

                return !$this->isRedirectPaymentFlow();
            }
        }

        return false;
    }

    public function isReceiptEmailsEnabled()
    {
        return ((bool)$this->getConfigData('receipt_emails'));
    }

    public function getStripeMode($storeId = null)
    {
        return $this->getConfigData('stripe_mode', 'basic', $storeId);
    }

    public function getSecretKey($mode = null, $storeId = null)
    {
        if (empty($mode))
            $mode = $this->getStripeMode($storeId);

        $key = $this->getConfigData("stripe_{$mode}_sk", "basic", $storeId);

        return $this->decrypt($key);
    }

    public function decrypt($key)
    {
         if (!preg_match('/^[A-Za-z0-9_]+$/', $key))
            $key = $this->encryptor->decrypt($key);

        return trim($key);
    }

    public function getPublishableKey($mode = null)
    {
        if (empty($mode))
            $mode = $this->getStripeMode();

        return trim($this->getConfigData("stripe_{$mode}_pk", "basic"));
    }

    public function getStripeParams()
    {
        return \Zend_Json::encode([
            "apiKey" => $this->getPublishableKey(),
            "locale" => $this->localeHelper->getStripeJsLocale(),
            "useSetupIntents" => $this->setupIntentFactory->create()->shouldUseSetupIntents()
        ]);
    }

    public function getWebhooksSigningSecret()
    {
        $mode = $this->getStripeMode();
        $key = $this->getConfigData("stripe_{$mode}_wss", "basic");

        // The following is due to a magento bug causing the key to need to be saved more than once to be decrypted correctly
        if (!preg_match('/^[A-Za-z0-9_]+$/',$key))
            $key = $this->encryptor->decrypt($key);

        return trim($key);
    }

    public function getWebhooksSigningSecretFor($store, $mode)
    {
        $key = $this->scopeConfig->getValue("payment/stripe_payments_basic/stripe_{$mode}_pk", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);

        // The following is due to a magento bug causing the key to need to be saved more than once to be decrypted correctly
        if (!preg_match('/^[A-Za-z0-9_]+$/',$key))
            $key = $this->encryptor->decrypt($key);

        return trim($key);
    }

    public function isAutomaticInvoicingEnabled()
    {
        if ($this->isRedirectPaymentFlow())
            $method = "checkout";
        else
            $method = null;

        return (bool)$this->getConfigData("automatic_invoicing", $method);
    }

    // If the module is unconfigured, payment_action will be null, defaulting to authorize & capture, so this would still return the correct value
    public function isAuthorizeOnly($method = null)
    {
        return ($this->getPaymentAction($method) == \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE);
    }

    public function getPaymentAction($method = null)
    {
        if (empty($method) && $this->isRedirectPaymentFlow())
            $method = "checkout";

        return $this->getConfigData('payment_action', $method);
    }

    public function isStripeRadarEnabled()
    {
        return ($this->getConfigData('radar_risk_level') > 0);
    }

    public function isApplePayEnabled()
    {
        return $this->getConfigData('apple_pay_checkout', 'express')
            && !$this->helper->isAdmin()
            && $this->storeManager->getStore()->isCurrentlySecure()
            && $this->canCheckout();
    }

    public function canCheckout()
    {
        if ($this->helper->isCustomerLoggedIn())
            return true;

        $allowGuestCheckout = $this->scopeConfig->getValue("checkout/options/guest_checkout", ScopeInterface::SCOPE_STORE, $this->helper->getStoreId());

        return (bool)$allowGuestCheckout;
    }

    public function isInstallmentPlansEnabled()
    {
        return (bool)$this->getConfigData('installment_plans')
            && !$this->helper->isAdmin()
            && !$this->helper->hasSubscriptions()
            && !$this->helper->isMultiShipping();
    }

    public function useStoreCurrency($order = null)
    {
        if ($order && $order->getPayment() && $this->helper->isStripeCheckoutMethod($order->getPayment()->getMethod()))
            return true;

        return (bool)$this->getConfigData('use_store_currency');
    }

    public function getSaveCards()
    {
        if ($this->isRedirectPaymentFlow())
            return $this->getConfigData('save_payment_method', "checkout");
        else
            return $this->getConfigData('ccsave');
    }

    public function getStatementDescriptor()
    {
        return $this->getConfigData('statement_descriptor');
    }

    public function retryWithSavedCard()
    {
        if ($this->isRedirectPaymentFlow())
            $method = "checkout";
        else
            $method = null;

        return $this->getConfigData('expired_authorizations', $method) == 1;
    }

    public function displayCardIcons()
    {
        if ($this->isRedirectPaymentFlow())
            $method = "checkout";
        else
            $method = null;

        return (bool)$this->getConfigData("card_icons", $method);
    }

    public function getCardIcons()
    {
        if ($this->isRedirectPaymentFlow())
            $method = "checkout";
        else
            $method = null;

        return $this->getConfigData("card_icons_specific", $method);
    }

    public function setIsStripeAPIKeyError($isError)
    {
        $this->isStripeAPIKeyError = $isError;
    }

    public function alwaysSaveCards()
    {
        return ($this->getSaveCards() == 2 ||
            ($this->isRedirectPaymentFlow() && $this->getSaveCards() == 1) ||
            $this->helper->hasSubscriptions() ||
            ($this->isAuthorizeOnly() && $this->retryWithSavedCard()) ||
            $this->helper->isMultiShipping());
    }

    public function isMOTOExemptionsEnabled()
    {
        return (bool)$this->getConfigData('moto_exemptions');
    }

    public function getIsStripeAPIKeyError()
    {
        if (isset($this->isStripeAPIKeyError))
            return $this->isStripeAPIKeyError;

        return false;
    }

    public function isRedirectPaymentFlow($storeId = null)
    {
        return ($this->getConfigData('payment_flow', null, $storeId) == 1);
    }

    public function getApplePayLocation()
    {
        return 2;
    }

    public function getAmountCurrencyFromQuote($quote, $useCents = true)
    {
        $params = array();
        $items = $quote->getAllItems();

        if ($this->useStoreCurrency())
        {
            $amount = $quote->getGrandTotal();
            $currency = $quote->getQuoteCurrencyCode();
        }
        else
        {
            $amount = $quote->getBaseGrandTotal();;
            $currency = $quote->getBaseCurrencyCode();
        }

        if ($useCents)
        {
            $cents = 100;
            if ($this->helper->isZeroDecimal($currency))
                $cents = 1;

            $fields["amount"] = round($amount * $cents);
        }
        else
        {
            // Used for Apple Pay only
            $fields["amount"] = number_format($amount, 2, '.', '');
        }

        $fields["currency"] = $currency;

        return $fields;
    }

    // Overwrite this based on business needs
    public function getMetadata($order)
    {
        $metadata = [
            "Module" => Config::module(),
            "Order #" => $order->getIncrementId()
        ];

        if ($order->getCustomerIsGuest())
            $metadata["Guest"] = "Yes";

        if ($order->getPayment()->getAdditionalInformation("prapi_title"))
            $metadata["Payment Method"] = $order->getPayment()->getAdditionalInformation("prapi_title");

        if ($order->getPayment()->getAdditionalInformation("prapi_location"))
            $metadata["Payment Location"] = $this->helper->getPaymentLocation($order->getPayment()->getAdditionalInformation("prapi_location"));

        return $metadata;
    }

    public function getStripeParamsFrom($order)
    {
        if ($this->useStoreCurrency())
        {
            $amount = $order->getGrandTotal();
            $currency = $order->getOrderCurrencyCode();
        }
        else
        {
            $amount = $order->getBaseGrandTotal();
            $currency = $order->getBaseCurrencyCode();
        }

        $cents = 100;
        if ($this->helper->isZeroDecimal($currency))
            $cents = 1;

        $params = array(
          "amount" => round($amount * $cents),
          "currency" => $currency,
          "description" => $this->helper->getOrderDescription($order),
          "metadata" => $this->getMetadata($order)
        );

        $customerEmail = $order->getCustomerEmail();
        if ($customerEmail && $this->isReceiptEmailsEnabled())
            $params["receipt_email"] = $customerEmail;

        return $params;
    }

    public function getAllStripeConfigurations()
    {
        $storeManagerDataList = $this->storeManager->getStores();
        $configurations = array();

        foreach ($storeManagerDataList as $storeId => $store)
        {
            $testModeConfig = $this->getStoreViewAPIKey($store, 'test');

            if (!empty($testModeConfig['api_keys']['sk']))
                $configurations[$testModeConfig['api_keys']['sk']] = $testModeConfig;

            $liveModeConfig = $this->getStoreViewAPIKey($store, 'live');

            if (!empty($liveModeConfig['api_keys']['sk']))
                $configurations[$liveModeConfig['api_keys']['sk']] = $liveModeConfig;
        }

        return $configurations;
    }

    public function getStoreViewAPIKey($store, $mode)
    {
        $secretKey = $this->scopeConfig->getValue("payment/stripe_payments_basic/stripe_{$mode}_sk", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store['code']);
        if (empty($secretKey))
            return null;

        return array_merge($store->getData(), [
            'api_keys' => [
                'pk' => $this->scopeConfig->getValue("payment/stripe_payments_basic/stripe_{$mode}_pk", \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store['code']),
                'sk' => $this->decrypt($secretKey),
                'wss' => $this->getWebhooksSigningSecretFor($store['code'], $mode)
            ],
            'mode' => $mode,
            'mode_label' => ucfirst($mode) . " Mode",
            'default_currency' => $store->getDefaultCurrency()->getCurrencyCode()
        ]);
    }

    public function isSaveCardCheckboxChecked()
    {
        $saveCards = $this->getSaveCards();

        return ($saveCards == 1 || $saveCards == 2);
    }

    public function getStripeClient()
    {
        return $this::$stripeClient;
    }

    public function shippingIncludesTax($store = null)
    {
        return $this->taxConfig->shippingPriceIncludesTax($store);
    }

    public function priceIncludesTax($store = null)
    {
        return $this->taxConfig->priceIncludesTax($store);
    }
}
