<?php
/**
 * @category    WeltPixel
 * @package     WeltPixel_EnhancedEmail
 * @copyright   Copyright (c) 2018 Weltpixel
 * @author      Nagy Attila @ Weltpixel TEAM
 */

namespace WeltPixel\EnhancedEmail\Helper;

/**
 * Class Data
 * @package WeltPixel\EnhancedEmail\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const XML_PATH_CUSTOM_LOGO_PATH = 'weltpixel/enhanced_email/';
    const XML_PATH_ENHANCED_EMAIL_SHOW_IMAGE = 'weltpixel_enhancedemail/general/show_image';

    const XML_PATH_ENHANCED_EMAIL_LOGO_LIGHT = 'weltpixel_enhancedemail/light_logo/logo_light';
    const XML_PATH_ENHANCED_EMAIL_LIGHT_LOGO_ALT = 'weltpixel_enhancedemail/light_logo/logo_light_alt';
    const XML_PATH_ENHANCED_EMAIL_LIGHT_LOGO_LINK = 'weltpixel_enhancedemail/light_logo/logo_light_link';
    const XML_PATH_ENHANCED_EMAIL_LIGHT_LOGO_WIDTH = 'weltpixel_enhancedemail/light_logo/logo_light_width';
    const XML_PATH_ENHANCED_EMAIL_LIGHT_LOGO_HEIGHT = 'weltpixel_enhancedemail/light_logo/logo_light_height';

    const XML_PATH_ENHANCED_EMAIL_LOGO_DARK = 'weltpixel_enhancedemail/dark_logo/logo_dark';
    const XML_PATH_ENHANCED_EMAIL_DARK_LOGO_ALT = 'weltpixel_enhancedemail/dark_logo/logo_dark_alt';
    const XML_PATH_ENHANCED_EMAIL_DARK_LOGO_LINK = 'weltpixel_enhancedemail/dark_logo/logo_dark_link';
    const XML_PATH_ENHANCED_EMAIL_DARK_LOGO_WIDTH = 'weltpixel_enhancedemail/dark_logo/logo_dark_width';
    const XML_PATH_ENHANCED_EMAIL_DARK_LOGO_HEIGHT = 'weltpixel_enhancedemail/dark_logo/logo_dark_height';

    const DEFAULT_LIGHT_LOGO_FILE_ID = 'WeltPixel_EnhancedEmail::images/light_logo_sample.png';
    const DEFAULT_DARK_LOGO_FILE_ID = 'WeltPixel_EnhancedEmail::images/dark_logo_sample.png';

    const XML_PATH_ORDER_MARKUP = 'weltpixel/enhancedemail/order_markup';
    const XML_PATH_INVOICE_MARKUP = 'weltpixel/enhancedemail/invoice_markup';
    const XML_PATH_CREDITMEMO_MARKUP = 'weltpixel/enhancedemail/creditmemo_markup';
    const XML_PATH_SHIPMENT_MARKUP = 'weltpixel/enhancedemail/shipment_markup';
    const XML_PATH_SUBSCRIPTION_MARKUP = 'weltpixel/enhancedemail/subscription_markup';
    const XML_PATH_CUSTOMER_MARKUP = 'weltpixel/enhancedemail/customer_action';
    const XML_PATH_CUSTOMER_ACCOUNT_MARKUP = 'weltpixel/enhancedemail/customer_account_action';
    const XML_PATH_CUSTOMER_ACCOUNT_CONFIRM_MARKUP = 'weltpixel/enhancedemail/customer_account_confirm_action';
    const XML_PATH_CUSTOMER_ACCOUNT_NO_PASSWORD_MARKUP = 'weltpixel/enhancedemail/customer_account_no_password_action';
    const XML_PATH_SUBSCRIPTION_CONFIRMATION_MARKUP = 'weltpixel/enhancedemail/subscription_confirmation_action';
    const XML_PATH_FORGOT_ADMIN_PASSWORD_MARKUP = 'weltpixel/enhancedemail/forgot_admin_password_action';
    const XML_PATH_WISHLIST_SHARING_MARKUP = 'weltpixel/enhancedemail/wishlist_sharing_action';

    const ORDER_VAR = 'order';
    const CREDITMEMO_VAR = 'creditmemo';
    const INVOICE_VAR = 'invoice';
    const SHIPMENT_VAR = 'shipment';
    const CUSTOMER_VAR = 'customer';
    const SUBSCRIBER_VAR = 'subscriber';
    const WISHLIST_VAR = 'wishlist';

    /**
     * @var array
     */
    protected $_templates = [];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \WeltPixel\EnhancedEmail\Model\SampleDataProvider
     */
    protected $_sampleDataProvider;

    /**
     * @var \Magento\Email\Model\BackendTemplate
     */
    protected $_template;

    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @var int
     */
    protected $_storeId;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $_design;
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Email\Model\BackendTemplate
     */
    protected $_backendTemplate;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \WeltPixel\EnhancedEmail\Model\SampleDataProvider $sampleDataProvider
     * @param \Magento\Email\Model\BackendTemplate $template
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\View\DesignInterface $design
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \WeltPixel\EnhancedEmail\Model\SampleDataProvider $sampleDataProvider,
        \Magento\Email\Model\BackendTemplate $template,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Email\Model\BackendTemplate $backendTemplate
    )
    {
        $this->_storeManager = $storeManager;
        $this->_sampleDataProvider = $sampleDataProvider;
        $this->_template = $template;
        $this->_coreRegistry = $coreRegistry;
        $this->_assetRepo = $assetRepo;
        $this->_design = $design;
        parent::__construct($context);

        $this->_storeId = $this->getCurrentStoreId();
        $this->backendSession = $backendSession;
        $this->imageHelper = $imageHelper;
        $this->_backendTemplate = $backendTemplate;
    }

    /**
     * @return array
     */
    public function getTemplatesTypes()
    {
        return $this->_templates = [
            'invoice' => [
                'path' => self::XML_PATH_INVOICE_MARKUP,
                'label' => 'WeltPixel Invoice Markup',
                'vars' => self::ORDER_VAR . ',' . self::INVOICE_VAR
            ],
            'order' => [
                'path' => self::XML_PATH_ORDER_MARKUP,
                'label' => 'WeltPixel Order Markup',
                'vars' => self::ORDER_VAR
            ],
            'creditmemo' => [
                'path' => self::XML_PATH_CREDITMEMO_MARKUP,
                'label' => 'WeltPixel Creditmemo Markup',
                'vars' => self::CREDITMEMO_VAR . ',' . self::ORDER_VAR
            ],
            'shipment' => [
                'path' => self::XML_PATH_SHIPMENT_MARKUP,
                'label' => 'WeltPixel Shipment Markup',
                'vars' => self::SHIPMENT_VAR . ',' .self::ORDER_VAR
            ],
            'newsletter' => [
                'path' => self::XML_PATH_SUBSCRIPTION_MARKUP,
                'label' => 'WeltPixel Subscription Markup',
                'vars' => self::SUBSCRIBER_VAR
            ],
            'customer_password' => [
                'path' => self::XML_PATH_CUSTOMER_MARKUP,
                'label' => 'WeltPixel Reset Password Markup',
                'vars' => self::CUSTOMER_VAR
            ],
            'customer_create_account_email_template' => [
                'path' => self::XML_PATH_CUSTOMER_ACCOUNT_MARKUP,
                'label' => 'WeltPixel New Account Markup',
                'vars' => self::CUSTOMER_VAR
            ],
            'customer_new_account_weltpixel' => [
                'path' => self::XML_PATH_CUSTOMER_ACCOUNT_MARKUP,
                'label' => 'WeltPixel New Account Markup',
                'vars' => self::CUSTOMER_VAR
            ],
            'customer_create_account_email_confirmed_template' => [
                'path' => self::XML_PATH_CUSTOMER_ACCOUNT_MARKUP,
                'label' => 'WeltPixel Account Confirmation Markup',
                'vars' => self::CUSTOMER_VAR
            ],
            'customer_create_account_email_confirmation_template' => [
                'path' => self::XML_PATH_CUSTOMER_ACCOUNT_CONFIRM_MARKUP,
                'label' => 'WeltPixel Email Confirmed Markup',
                'vars' => self::CUSTOMER_VAR
            ],
            'customer_new_account_confirmation_weltpixel' => [
                'path' => self::XML_PATH_CUSTOMER_ACCOUNT_CONFIRM_MARKUP,
                'label' => 'WeltPixel Email Confirmed Markup',
                'vars' => self::CUSTOMER_VAR
            ],
            'customer_new_account_confirmed_weltpixel' => [
                'path' => self::XML_PATH_CUSTOMER_ACCOUNT_CONFIRM_MARKUP,
                'label' => 'WeltPixel Email Confirmed Markup',
                'vars' => self::CUSTOMER_VAR
            ],
            'customer_create_account_email_no_password_template' => [
                'path' => self::XML_PATH_CUSTOMER_ACCOUNT_NO_PASSWORD_MARKUP,
                'label' => 'WeltPixel New Account No Password Markup',
                'vars' => self::CUSTOMER_VAR
            ],
            'customer_new_account_no_password_weltpixel' => [
                'path' => self::XML_PATH_CUSTOMER_ACCOUNT_NO_PASSWORD_MARKUP,
                'label' => 'WeltPixel New Account No Password Markup',
                'vars' => self::CUSTOMER_VAR
            ],
            'customer_new_pass_weltpixel' => [
                'path' => '',
                'label' => '',
                'vars' => self::CUSTOMER_VAR
            ],
            'customer_password_reset_confirmation_weltpixel' => [
                'path' => '',
                'label' => '',
                'vars' => self::CUSTOMER_VAR
            ],
            'customer_change_email_weltpixel' => [
                'path' => '',
                'label' => '',
                'vars' => self::CUSTOMER_VAR
            ],
            'customer_change_email_password_weltpixel' => [
                'path' => '',
                'label' => '',
                'vars' => self::CUSTOMER_VAR
            ],
            'newsletter' => [
                'path' => self::XML_PATH_SUBSCRIPTION_CONFIRMATION_MARKUP,
                'label' => 'WeltPixel Subscription Confirmation Markup',
                'vars' => self::SUBSCRIBER_VAR
            ],
            'admin' => [
                'path' => self::XML_PATH_FORGOT_ADMIN_PASSWORD_MARKUP,
                'label' => 'WeltPixel Forgot Admin Password Markup',
                'vars' => ''
            ],
            'wishlist' => [
                'path' => self::XML_PATH_WISHLIST_SHARING_MARKUP,
                'label' => 'WeltPixel Wishlist Sharing Markup',
                'vars' => self::WISHLIST_VAR
            ]
        ];
    }

    /**
     * @param $searchStr
     * @param $templateCode
     * @return bool
     */
    public function getTemplateType($searchStr, $templateCode)
    {
        $pos = strpos($templateCode, $searchStr);
        if ($pos === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param int $storeId
     * @return mixed
     */
    public function isEnabled($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/general/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return mixed
     */
    public function canShowProductImage($storeId = null)
    {
        if(!$this->isEnabled()) {
            return false;
        } else {
            return $this->scopeConfig->getValue(self::XML_PATH_ENHANCED_EMAIL_SHOW_IMAGE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        }
    }

    /**
     * @return string
     */
    public function updateOrderTotalLabelPropertyArgument()
    {
        $html = "colspan='2'";
        if($this->canShowProductImage()) {
            $html = "colspan='3'";
        }

        return $html;
    }

    /**
     * @param $date
     * @return string
     */
    public function getDateIsoFormat($date)
    {
        $objDateTime = new \DateTime($date);
        return $objDateTime->format(\DateTime::ISO8601);
    }

    /**
     * @return array
     */
    public function getEmailSender()
    {
        return $sender = [
            'name' => $this->scopeConfig->getValue('trans_email/ident_general/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            'email' => $this->scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
        ];

    }

    /**
     * @param $templateIdentifier
     * @return array
     */
    public function getEmailSampleData($templateIdentifier)
    {
        $vars = $this->_getTemplateSpecificVars($templateIdentifier);
        return $vars;
    }

    /**
     * @param $identifier
     * @return bool|string
     */
    protected function _getTemplateSpecificVars($identifier)
    {
        $variableArr['store'] = $this->_storeManager->getStore();
        foreach ($this->getTemplatesTypes() as $searchStr => $tplData) {
            if ($this->getTemplateType($searchStr, $identifier)) {
                $varsArr = explode(',', $tplData['vars']);
                foreach ($varsArr as $var) {
                    switch ($var) {
                        case self::ORDER_VAR :
                            $varVal = $this->_sampleDataProvider->fetchOrder();
                            break;
                        case self::INVOICE_VAR :
                            $varVal = $this->_sampleDataProvider->fetchInvoice();
                            break;
                        case self::CREDITMEMO_VAR :
                            $varVal = $this->_sampleDataProvider->fetchCreditmemo();
                            break;
                        case self::SHIPMENT_VAR :
                            $varVal = $this->_sampleDataProvider->fetchShipment();
                            break;
                        case self::CUSTOMER_VAR :
                            $varVal = $this->_sampleDataProvider->fetchCustomer();
                            break;
                        case self::SUBSCRIBER_VAR :
                            $varVal = $this->_sampleDataProvider->fetchSubscriber();
                            break;
                        case self::WISHLIST_VAR :
                            $varVal = $this->_sampleDataProvider->fetchWishlist();
                            break;
                        default :
                            $varVal = '';

                    }
                    $variableArr[$var] = $varVal;
                }

            }
        }

        return $variableArr;

    }

    /**
     * Return current email template
     *
     * @return mixed
     */
    public function getTemplate()
    {
        $templateId = $this->backendSession->getEmailTemplate();
        $template = $this->_backendTemplate->load($templateId);
        return $template;
    }

    /**
     * Return current email template
     *
     * @return mixed
     */
    public function getCurrentEmailTemplate()
    {
        $templateId = $this->backendSession->getCurrentEmailTemplate();
        $template = $this->_backendTemplate->load($templateId);
        return $template;
    }

    /**
     * @param $link
     * @param $params
     * @return string
     */
    public function getFrontendUrl($link, $params)
    {
        $defaultStoreId = $this->_storeManager->getDefaultStoreView()->getId();
        $url = $this->_storeManager->getStore($defaultStoreId)->getUrl($link, $params);
        return $url;
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getLightLogo($storeId = null)
    {
        $configValue = $this->scopeConfig->getValue(self::XML_PATH_ENHANCED_EMAIL_LOGO_LIGHT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        if (!$configValue) {
            $designParams = $this->getDesignParams();
            return $this->_assetRepo->getUrlWithParams(
                self::DEFAULT_LIGHT_LOGO_FILE_ID,
                $designParams
            );
        }
        return $configValue;
    }

    /**
     * @return bool
     */
    public function isLightLogoUploaded($storeId = null) {
        $configValue = $this->scopeConfig->getValue(self::XML_PATH_ENHANCED_EMAIL_LOGO_LIGHT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        if($configValue) {
            return true;
        }

        return false;

    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getDarkLogo($storeId = null)
    {
        $configValue = $this->scopeConfig->getValue(self::XML_PATH_ENHANCED_EMAIL_LOGO_DARK, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        if (!$configValue) {
            $designParams = $this->getDesignParams();
            return $this->_assetRepo->getUrlWithParams(
                self::DEFAULT_DARK_LOGO_FILE_ID,
                $designParams
            );
        }
        return $configValue;
    }

    /**
     * @return bool
     */
    public function isDarkLogoUploaded($storeId = null) {
        $configValue = $this->scopeConfig->getValue(self::XML_PATH_ENHANCED_EMAIL_LOGO_DARK, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        if($configValue) {
            return true;
        }

        return false;
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getLightLogoLink($storeId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ENHANCED_EMAIL_LIGHT_LOGO_LINK, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getLightLogoAlt($storeId = null)
    {

        return $this->scopeConfig->getValue(self::XML_PATH_ENHANCED_EMAIL_LIGHT_LOGO_ALT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getLightLogoWidth($storeId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ENHANCED_EMAIL_LIGHT_LOGO_WIDTH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getLightLogoHeight($storeId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ENHANCED_EMAIL_LIGHT_LOGO_HEIGHT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getDarkLogoLink($storeId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ENHANCED_EMAIL_DARK_LOGO_LINK, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getDarkLogoAlt($storeId = null)
    {

        return $this->scopeConfig->getValue(self::XML_PATH_ENHANCED_EMAIL_DARK_LOGO_ALT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getDarkLogoWidth($storeId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ENHANCED_EMAIL_DARK_LOGO_WIDTH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getDarkLogoHeight($storeId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ENHANCED_EMAIL_DARK_LOGO_HEIGHT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return int
     */
    public function getCurrentStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @return string
     */
    public function getLogoSrc()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . self::XML_PATH_CUSTOM_LOGO_PATH;
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHeaderBackgroundColor($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/email_header/header__bg____color', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getFooterBackgroundColor($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/email_footer/footer__bg____color', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getBodyWrapperBackgroundColor($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/email_body/body_wrapper__bg____color', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getBodyBackgroundColor($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/email_body/body__bg____color', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getBodyFontColor($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/email_body/body__text____color', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getBodyLinkColor($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/email_body/body__link____color', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getBodyOrderTotalBackgroundColor($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/email_body/body__ordertotal_bg____color', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }


    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHeadingOneFontColor($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h1/h1__font____color', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function useHeadingOneGoogleFonts($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h1/h1__use_google_fonts', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHeadingOneWebsafeFont($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h1/h1__font____family_websafe', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHeadingOneFallbackFont($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h1/h1__font____family_fallback', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHeadingOneFontFamily($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h1/h1__font____family', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHeadingOneFontFamilyCharacterset($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h1/h1__font____family_characterset', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHeadingOneFontWeight($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h1/h1__font____weight', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHeadingOneFontSize($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h1/h1__font____size', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHeadingOneFontStyle($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h1/h1__font____style', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHeadingOneFontHeight($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h1/h1__line____height', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }
    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHeadingOneLetterSpacing($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h1/h1__letter____spacing', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }
    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHeadingTwooFontColor($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h2/h2__font____color', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHeadingTwooFontFamily($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h2/h2__font____family', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHeadingTwooFontFamilyCharacterset($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h2/h2__font____family_characterset', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHeadingTwooFontWeight($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h2/h2__font____weight', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHeadingTwooFontSize($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h2/h2__font____size', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHeadingTwooFontStyle($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h2/h2__font____style', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHeadingTwooFontHeight($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h2/h2__line____height', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }
    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHeadingTwooLetterSpacing($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h2/h2__letter____spacing', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function useHeadingTwooGoogleFonts($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h2/h2__use_google_fonts', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHeadingTwooWebsafeFont($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h2/h2__font____family_websafe', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHeadingTwooFallbackFont($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h2/h2__font____family_fallback', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHeadingThreeFontColor($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h3/h3__font____color', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHeadingThreeFontFamily($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h3/h3__font____family', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHeadingThreeFontFamilyCharacterset($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h3/h3__font____family_characterset', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHeadingThreeFontWeight($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h3/h3__font____weight', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHeadingThreeFontSize($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h3/h3__font____size', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHeadingThreeFontStyle($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h3/h3__font____style', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHeadingThreeFontHeight($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h3/h3__line____height', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHeadingThreeLetterSpacing($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h3/h3__letter____spacing', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function useHeadingThreeGoogleFonts($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h3/h3__use_google_fonts', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHeadingThreeWebsafeFont($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h3/h3__font____family_websafe', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getHeadingThreeFallbackFont($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/h3/h3__font____family_fallback', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getPFontColor($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/paragraph/paragraph__font____color', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getPFontFamily($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/paragraph/paragraph__font____family', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getPFontFamilyCharacterset($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/paragraph/paragraph__font____family_characterset', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getPFontWeight($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/paragraph/paragraph__font____weight', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getPFontSize($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/paragraph/paragraph__font____size', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getPFontStyle($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/paragraph/paragraph__font____style', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getPFontHeight($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/paragraph/paragraph__line____height', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getPLetterSpacing($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/paragraph/paragraph__letter____spacing', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function usePGoogleFonts($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/paragraph/paragraph__use_google_fonts', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getPWebsafeFont($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/paragraph/paragraph__font____family_websafe', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getPFallbackFont($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/paragraph/paragraph__font____family_fallback', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getButtonFontColor($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/button/buttons__font____color', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getButtonBgColor($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/button/buttons__bg____color', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getButtonBorderColor($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/button/buttons__border____color', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getButtonHoverFontColor($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/button/buttons__hover__font____color', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getButtonHoverBgColor($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/button/buttons__hover__bg____color', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getButtonHoverBorderColor($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/button/buttons__hover__border____color', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getTopmenuBgColor($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/email_topmenu/topmenu__bg____color', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getTopmenuFontColor($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/email_topmenu/topmenu__font____color', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getTopmenuPaddingTopBottom($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/email_topmenu/topmenu__padding___top_bottom', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getTopmenuPadding($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/email_topmenu/topmenu__padding', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getCommentFontColor($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/email_comment/email_comment__font____color', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getCommentBgColor($storeId = null)
    {
        return $this->scopeConfig->getValue('weltpixel_enhancedemail/email_comment/email_comment__bg____color', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return array
     */
    private function getDesignParams()
    {
        return [
            'area' => $this->_design->getArea(),
            'theme' => $this->_design->getDesignTheme()->getCode(),
            'themeModel' => $this->_design->getDesignTheme(),
            'locale' => $this->_design->getLocale(),
        ];

    }

    /**
     * @param $product
     * @return string
     */
    public function getNonCachedProductImageUrl($product)
    {
        $catalogProductMediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product';

        if ($product->getThumbnail() && $product->getThumbnail() != 'no_selection') {
            return $catalogProductMediaUrl . DIRECTORY_SEPARATOR . ltrim( $product->getThumbnail(), DIRECTORY_SEPARATOR);
        } elseif ($product->getSmallImage() && $product->getSmallImage() != 'no_selection') {
            return $catalogProductMediaUrl .  DIRECTORY_SEPARATOR . ltrim($product->getSmallImage(), DIRECTORY_SEPARATOR);
        } else {
            $plHolder = $this->imageHelper->getDefaultPlaceholderUrl('thumbnail');
            return $plHolder;
        }
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function displaySalesPricesExclTax($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'tax/sales_display/price',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        ) == 1;
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isProductsGridEnabled($storeId = null)
    {
        return (bool) $this->scopeConfig->getValue('weltpixel_enhancedemail/products_grid/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getProductsGridTitle($storeId = null)
    {
        return  trim($this->scopeConfig->getValue('weltpixel_enhancedemail/products_grid/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId));
    }

    /**
     * @param null $storeId
     * @return string
     */
    public function getProductsGridProductsType($storeId = null)
    {
        return  $this->scopeConfig->getValue('weltpixel_enhancedemail/products_grid/products_type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param null $storeId
     * @return int
     */
    public function getProductsGridNumberOfProducts($storeId = null)
    {
        return  (int)$this->scopeConfig->getValue('weltpixel_enhancedemail/products_grid/number_of_products', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }


}
