<?php
/**
 * @category    WeltPixel
 * @package     WeltPixel_EnhancedEmail
 * @copyright   Copyright (c) 2018 Weltpixel
 * @author      Nagy Attila @ Weltpixel TEAM
 */

namespace WeltPixel\EnhancedEmail\Plugin;

/**
 * Class TemplateVariablesPlugin
 * @package WeltPixel\EnhancedEmail\Plugin
 */
class TemplateVariablesPlugin
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

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

    const XML_PATH_SOCIAL_MEDIA_BLOCK = 'weltpixel/enhancedemail/social_media_email_block';
    const XML_PATH_CUSTOM_BLOCK_ONE = 'weltpixel/enhancedemail/custom_block_1';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var array
     */
    protected $templateTypes = [];

    /**
     * @var \WeltPixel\EnhancedEmail\Helper\Data
     */
    protected $_helper;

    /**
     * TemplateVariablesPlugin constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Request\Http $request
     * @param \WeltPixel\EnhancedEmail\Helper\Data $helper
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Request\Http $request,
        \WeltPixel\EnhancedEmail\Helper\Data $helper
    ) {
        $this->_storeManager = $storeManager;
        $this->request = $request;
        $this->_scopeConfig = $scopeConfig;
        $this->_helper = $helper;
    }

    /**
     * @param \Magento\Email\Model\Template $subject
     * @param string $result
     * @return array|string
     */
    public function afterGetVariablesOptionArray(\Magento\Email\Model\Template $subject, $result = '')
    {
        $templateCode = $subject->getOrigTemplateCode();
        $templateTypes = $this->_helper->getTemplatesTypes();
        $stores = $this->_storeManager->getStores();

        $optionArray = [];
        $socialMediaBlock = $this->_scopeConfig->getValue(self::XML_PATH_SOCIAL_MEDIA_BLOCK, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $customBlockOne = $this->_scopeConfig->getValue(self::XML_PATH_CUSTOM_BLOCK_ONE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $wpEeVars = [
            [
                'value' => '{{layout handle="menu_line" area="frontend"}}', 'label' => __('%1', 'WeltPixel Top Menu')
            ],
            [
                'value' => '{{' . $socialMediaBlock . '}}', 'label' => __('%1', 'WeltPixel Social Media Block')
            ],
            [
                'value' => '{{' . $customBlockOne . '}}', 'label' => __('%1', 'WeltPixel Custom Block')
            ],
            [
                'value' => '{{layout handle="light_logo" area="frontend"}}', 'label' => __('%1', 'WeltPixel LIGHT Logo')
            ],
            [
                'value' => '{{layout handle="dark_logo" area="frontend"}}', 'label' => __('%1', 'WeltPixel DARK Logo')
            ],
            [
                'value' => '{{layout handle="weltpixel_products_grid" order=$order area="frontend"}}', 'label' => __('%1', 'WeltPixel Products Grid')
            ]
        ];

        foreach ($wpEeVars as $var) {
            array_push($optionArray, $var);
        }

        foreach ($templateTypes as $searchStr => $tplData) {
            if ($this->_helper->getTemplateType($searchStr, $templateCode)) {
                $markup = ($tplData['path']) ? $this->_scopeConfig->getValue($tplData['path'], \Magento\Store\Model\ScopeInterface::SCOPE_STORE) : '';
                if ($markup) {
                    $optionData = [
                        'value' => '{{' . $markup . '}}', 'label' => __('%1', $tplData['label'])
                    ];
                    array_push($optionArray, $optionData);
                }

                if ($searchStr == 'order') {
                    $optionData = [
                        'value' => '{{layout handle="weltpixel_sales_email_order_items" order=$order}}', 'label' => __('%1', 'WeltPixel Order Item Grid')
                    ];
                    array_push($optionArray, $optionData);
                }

                if ($searchStr == 'invoice') {
                    $optionData = [
                        'value' => '{{layout handle="weltpixel_sales_email_order_invoice_items" incoice=$invoice order=$order}}', 'label' => __('%1', 'WeltPixel Order Invoice Item Grid')
                    ];
                    array_push($optionArray, $optionData);
                }

                if ($searchStr == 'creditmemo') {
                    $optionData = [
                        'value' => '{{layout handle="weltpixel_sales_email_order_creditmemo_items" creditmemo=$creditmemo order=$order}}', 'label' => __('%1', 'WeltPixel Order Creditmemo Item Grid')
                    ];
                    array_push($optionArray, $optionData);
                }
            }
        }

        foreach ($optionArray as $newOption) {
            if (isset($result[0])) {
                if (isset($result[0]['value'])) {
                    array_unshift($result[0]['value'], $newOption);
                } else {
                    if (!isset($result[0]['label'])) {
                        $result[0]['label'] = __('Template Variables');
                    }
                    $result[0]['value'][0] = $newOption;
                }
            } else {
                if (isset($result['value'])) {
                    array_unshift($result['value'], $newOption);
                } else {
                    if (!isset($result['label'])) {
                        $result['label'] = __('Template Variables');
                    }
                    $result['value'][0] = $newOption;
                }
            }
        }

        return $result;
    }
}
