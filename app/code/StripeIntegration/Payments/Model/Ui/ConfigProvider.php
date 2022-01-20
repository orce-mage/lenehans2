<?php

namespace StripeIntegration\Payments\Model\Ui;

use Magento\Framework\Exception\LocalizedException;
use Magento\Checkout\Model\ConfigProviderInterface;
use StripeIntegration\Payments\Gateway\Http\Client\ClientMock;
use Magento\Framework\Locale\Bundle\DataBundle;
use StripeIntegration\Payments\Helper\Logger;
use StripeIntegration\Payments\Model\PaymentMethod;
use StripeIntegration\Payments\Model\Config;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'stripe_payments';
    const YEARS_RANGE = 15;

    public function __construct(
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \StripeIntegration\Payments\Model\Config $config,
        \Magento\Customer\Model\Session $session,
        \StripeIntegration\Payments\Helper\Generic $helper,
        \StripeIntegration\Payments\Model\PaymentIntent $paymentIntent,
        \StripeIntegration\Payments\Model\Adminhtml\Source\CardIconsSpecific $cardIcons,
        \StripeIntegration\Payments\Helper\SetupIntent $setupIntent,
        \StripeIntegration\Payments\Helper\Subscriptions $subscriptionsHelper
    )
    {
        $this->localeResolver = $localeResolver;
        $this->_date = $date;
        $this->request = $request;
        $this->assetRepo = $assetRepo;
        $this->config = $config;
        $this->session = $session;
        $this->helper = $helper;
        $this->saveCards = $config->getSaveCards();
        $this->customer = $helper->getCustomerModel();
        $this->paymentIntent = $paymentIntent;
        $this->cardIcons = $cardIcons;
        $this->setupIntent = $setupIntent;
        $this->subscriptionsHelper = $subscriptionsHelper;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $data = [
            'payment' => [
                self::CODE => [
                    'enabled' => $this->config->isEnabled(),
                    'months' => $this->getMonths(),
                    'years' => $this->getYears(),
                    'cvvImageUrl' => $this->getCvvImageUrl(),
                    'useStoreCurrency' => $this->config->useStoreCurrency(),
                    'initParams' => \Zend_Json::decode($this->config->getStripeParams(), \Zend\Json\Json::TYPE_ARRAY),
                    'showSaveCardOption' => $this->getShowSaveCardOption(),
                    'alwaysSaveCard' => $this->getAlwaysSaveCard(),
                    'isSaveCardCheckboxChecked' => $this->config->isSaveCardCheckboxChecked(),
                    'savedCards' => $this->customer->getCustomerCards(),
                    'isApplePayEnabled' => (bool)$this->config->isApplePayEnabled(),
                    'isInstallmentPlansEnabled' => $this->config->isInstallmentPlansEnabled(),
                    'applePayLocation' => $this->config->getApplePayLocation(),
                    'icons' => $this->getIcons(),
                    'apmIcons' => $this->getApmIcons(),
                    'iconsLocation' => $this->config->getConfigData("icons_location"),
                    'hasTrialSubscriptions' => false,
                    'trialingSubscriptions' => null,
                    'prapi_description' => null,
                    'prapiTitle' => $this->helper->getPRAPIMethodType(),
                    'prapiButtonConfig' => $this->config->getPRAPIButtonSettings(),
                    'module' => Config::module()
                ],
                self::CODE . "_sepa_credit" => [
                    'customer_bank_account' => $this->config->getConfigData("customer_bank_account", "sepa_credit")
                ]
            ]
        ];

        if ($this->config->isEnabled() || $this->config->isEnabled("checkout"))
        {
            // These are a bit more resource intensive, so we only want to run them if the module is enabled
            $data['payment'][self::CODE]['hasTrialSubscriptions'] = $this->helper->hasTrialSubscriptions();
            $data['payment'][self::CODE]['trialingSubscriptions'] = ($this->config->isSubscriptionsEnabled() ? $this->subscriptionsHelper->getTrialingSubscriptionsAmounts() : null);
            $data['payment'][self::CODE]['prapi_description'] = $this->config->getPRAPIDescription();
        }

        return $data;
    }

    public function getShowSaveCardOption()
    {
        return $this->config->getSaveCards() && $this->session->isLoggedIn();
    }

    public function getAlwaysSaveCard()
    {
        return $this->config->alwaysSaveCards();
    }

    /**
     * Retrieve list of months translation
     *
     * @return array
     * @api
     */
    public function getMonths()
    {
        $data = [];
        $months = (new DataBundle())->get(
            $this->localeResolver->getLocale()
        )['calendar']['gregorian']['monthNames']['format']['wide'];
        foreach ($months as $key => $value) {
            $monthNum = ++$key < 10 ? '0' . $key : $key;
            $data[$key] = $monthNum . ' - ' . $value;
        }
        return $data;
    }

    /**
     * Retrieve array of available years
     *
     * @return array
     * @api
     */
    public function getYears()
    {
        $years = [];
        $first = (int)$this->_date->date('Y');
        for ($index = 0; $index <= self::YEARS_RANGE; $index++) {
            $year = $first + $index;
            $years[$year] = $year;
        }
        return $years;
    }

    /**
     * Retrieve CVV tooltip image url
     *
     * @return string
     */
    public function getCvvImageUrl()
    {
        return $this->getViewFileUrl('Magento_Checkout::cvv.png');
    }

    /**
     * Retrieve url of a view file
     *
     * @param string $fileId
     * @param array $params
     * @return string
     */
    public function getViewFileUrl($fileId, array $params = [])
    {
        try {
            $params = array_merge(['_secure' => $this->request->isSecure()], $params);
            return $this->assetRepo->getUrlWithParams($fileId, $params);
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
            return $this->urlBuilder->getUrl('', ['_direct' => 'core/index/notFound']);
        }
    }

    public function getIcons()
    {
        $icons = [];
        $displayIcons = $this->config->displayCardIcons();
        switch ($displayIcons)
        {
            // All
            case 0:
                $options = $this->cardIcons->toOptionArray();
                foreach ($options as $option)
                {
                    $code = $option["value"];
                    $icons[] = [
                        'code' => $code,
                        'name' => $option["label"],
                        'path' => $this->getViewFileUrl("StripeIntegration_Payments::img/cards/$code.svg")
                    ];
                }
                return $icons;
            // Specific
            case 1:
                $specific = explode(",", $this->config->getCardIcons());
                foreach ($specific as $code)
                {
                    $icons[] = [
                        'code' => $code,
                        'name' => null,
                        'path' => $this->getViewFileUrl("StripeIntegration_Payments::img/cards/$code.svg")
                    ];
                }
                return $icons;
            // Disabled
            default:
                return [];
        }
    }

    public function getApmIcons()
    {
        return [
            'acss_debit' => $this->getViewFileUrl("StripeIntegration_Payments::img/methods/bank.svg"),
            'afterpay_clearpay' => $this->getViewFileUrl("StripeIntegration_Payments::img/methods/afterpay_clearpay.svg"),
            'alipay' => $this->getViewFileUrl("StripeIntegration_Payments::img/methods/alipay.svg"),
            'bacs_debit' => $this->getViewFileUrl("StripeIntegration_Payments::img/methods/bacs_debit.svg"),
            'au_becs_debit' => $this->getViewFileUrl("StripeIntegration_Payments::img/methods/bank.svg"),
            'bancontact' => $this->getViewFileUrl("StripeIntegration_Payments::img/methods/bancontact.svg"),
            'boleto' => $this->getViewFileUrl("StripeIntegration_Payments::img/methods/boleto.svg"),
            'eps' => $this->getViewFileUrl("StripeIntegration_Payments::img/methods/eps.svg"),
            'fpx' => $this->getViewFileUrl("StripeIntegration_Payments::img/methods/fpx.svg"),
            'giropay' => $this->getViewFileUrl("StripeIntegration_Payments::img/methods/giropay.svg"),
            'grabpay' => $this->getViewFileUrl("StripeIntegration_Payments::img/methods/grabpay.svg"),
            'ideal' => $this->getViewFileUrl("StripeIntegration_Payments::img/methods/ideal.svg"),
            'klarna' => $this->getViewFileUrl("StripeIntegration_Payments::img/methods/klarna.svg"),
            'paypal' => $this->getViewFileUrl("StripeIntegration_Payments::img/methods/paypal.svg"),
            'multibanco' => $this->getViewFileUrl("StripeIntegration_Payments::img/methods/multibanco.svg"),
            'p24' => $this->getViewFileUrl("StripeIntegration_Payments::img/methods/p24.svg"),
            'sepa' => $this->getViewFileUrl("StripeIntegration_Payments::img/methods/sepa_debit.svg"),
            'sepa_debit' => $this->getViewFileUrl("StripeIntegration_Payments::img/methods/sepa_debit.svg"),
            'sepa_credit' => $this->getViewFileUrl("StripeIntegration_Payments::img/methods/sepa_credit.svg"),
            'sofort' => $this->getViewFileUrl("StripeIntegration_Payments::img/methods/klarna.svg"),
            'wechat' => $this->getViewFileUrl("StripeIntegration_Payments::img/methods/wechat.svg"),
            'ach' => $this->getViewFileUrl("StripeIntegration_Payments::img/methods/ach.svg"),
            'oxxo' => $this->getViewFileUrl("StripeIntegration_Payments::img/methods/oxxo.svg"),
            'bank' => $this->getViewFileUrl("StripeIntegration_Payments::img/methods/bank.svg")
        ];
    }
}
