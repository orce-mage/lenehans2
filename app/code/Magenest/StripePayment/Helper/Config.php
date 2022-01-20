<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Helper;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_encryptor;

    protected $storeManager;

    public function __construct(
        Context $context,
        EncryptorInterface $encryptor,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_encryptor = $encryptor;
        $this->storeManager = $storeManager;
    }

    public function getIsSandboxMode()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe/test',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }

    public function isSave()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe/save',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function isSaveIntents()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_intents/save_card',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getPaymentAction()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe/payment_action',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getPaymentActionIframe()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_iframe/payment_action',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getConfigValue($value)
    {
        $configValue = $this->scopeConfig->getValue(
            'payment/magenest_stripe/' . $value,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );

        return $this->_encryptor->decrypt($configValue);
    }

//    BEGIN IFRAME CONFIG
    public function getCheckoutCanCollectBilling()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_iframe/collect_billing',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getCheckoutCanCollectShipping()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_iframe/collect_shipping',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getCheckoutCanCollectZip()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_iframe/collect_zip',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getDisplayName()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_iframe/display_name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getButtonLabel()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_iframe/button_label',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getAllowRemember()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_iframe/allow_remember',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getCanAcceptBitcoin()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_iframe/allow_bitcoin',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getCanAcceptAlipay()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_iframe/allow_alipay',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getCheckoutImageUrl()
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'stripe/';
        $imageId = $this->scopeConfig->getValue(
            'payment/magenest_stripe_iframe/upload_image_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!!$imageId) {
            return $baseUrl . $imageId;
        } else {
            return null;
        }
    }

    public function isIframeActive()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_iframe/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getLocale()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_iframe/locale',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
//    END IFRAME CONFIG

    public function isDebugMode()
    {
        return 1;
//        return $this->scopeConfig->getValue(
//            'payment/magenest_stripe/debug',
//            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
//        );
    }

    public function getPublishableKey()
    {
        $isTest = $this->getIsSandboxMode();
        if ($isTest) {
            return $this->getConfigValue('test_publishable');
        } else {
            return $this->getConfigValue('live_publishable');
        }
    }

    public function getSecretKey()
    {
        $isTest = $this->getIsSandboxMode();
        if ($isTest) {
            return $this->getConfigValue('test_secret');
        } else {
            return $this->getConfigValue('live_secret');
        }
    }

    public function getInstructions($payment = "")
    {
        if ($payment) {
            $path = 'payment/magenest_stripe' . '_' . $payment . '/instructions';
        } else {
            $path = 'payment/magenest_stripe/instructions';
        }
        return preg_replace('/\s+|\n+|\r/', ' ', $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

    public function sendMailCustomer()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe/email_customer',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getNewOrderStatus()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe/order_status',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getApiVersion()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe/api',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getDisplayPaymentButton()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe/display_payment_button',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getThreeDSecure()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe/three_d_secure'
        );
    }

    public function getForceThreeDSecure()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe/force_d_secure'
        );
    }

    public function getThreeDSecureVerify()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe/three_d_secure_verify'
        );
    }

    //apple pay config
    public function isApplePayActive()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_applepay/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getReplacePlaceOrder()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_applepay/replace_placeorder',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getButtonTheme()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_applepay/paybutton_theme',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getButtonType()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_applepay/paybutton_type',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getApplepayButtonLabel()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_applepay/paybutton_label',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getActiveOnCart()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_applepay/active_on_shopping_cart',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getActiveOnProductDetail()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_applepay/active_on_product_details',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getActiveOnCheckout()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_applepay/active_on_checkout',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    //apple pay config


    /////
    /// /////SOFORT CONFIG//////////////////
    ///
    ///
    public function getWebhooksSecret()
    {
        return $this->getConfigValue('webhook_key');
    }

    public function isSofortAllowSelectLanguage()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_sofort/allow_select_language',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function sofortDefaultLanguage()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_sofort/default_language',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function isSofortAllowSelectBankCountry()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_sofort/allow_select_bank_country',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function sofortDefaultBankCountry()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_sofort/default_bank_country',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    ///
    ///
    /// /////SOFORT CONFIG/////////////////


    ////////////iDEAL CONFIG//////////////////
    ///
    ///
    ///
    public function isUseElementInterface()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_ideal/use_element',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function isIdealAllowSelectBank()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_ideal/allow_select_bank',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getIdealDefaultBank()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_ideal/default_bank',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    ///
    ////////////iDEAL CONFIG//////////////////

    /////////Bancontact CONFIG///////////////
    ///
    ///
    ///
    ///

    public function isBancontactAllowSelectLanguage()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_bancontact/allow_select_language',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function bancontactDefaultLanguage()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_bancontact/default_language',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    ///
    ///
    ///
    ///
    /// /////////Bancontact CONFIG///////////////
    ///

    /////////Stripe checkout CONFIG///////////////
    ///
    ///
    ///
    ///

    public function isStripeCheckoutCollectBilling()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_checkout/collect_billing_address',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getStripeCheckoutPaymentAction()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_checkout/payment_action',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getStripeCheckoutTitle()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_checkout/checkout_title',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getStripeCheckoutDescription()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_checkout/checkout_description',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getStripeCheckoutImageUrl()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_checkout/checkout_image_url',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getStripeCheckoutSubmitType()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_checkout/checkout_submit_type',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    ///
    ///
    ///
    ///
    /// /////////Stripe checkout CONFIG///////////////

    public function getScopeConfig()
    {
        return $this->scopeConfig;
    }

    public function getStatementDescriptor()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe/statement_descriptor',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getStripeIntentPaymentAction()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_paymentintents/payment_action',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getStripeCountrySpecified()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe/country_specified',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    ///
    ///
    ///
    ///
    /// /////////Stripe Intents CONFIG///////////////

    public function getPaymentActionIntents()
    {
        return $this->scopeConfig->getValue(
            'payment/magenest_stripe_intents/payment_action',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
