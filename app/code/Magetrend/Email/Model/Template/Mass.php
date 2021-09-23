<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Model\Template;

use \Magento\Sales\Model\Order\Email\Container\CreditmemoCommentIdentity;

class Mass
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface|null
     */
    public $objectManager = null;

    /**
     * @var \Magetrend\Email\Model\Template|null
     */
    public $template = null;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|null
     */
    public $scopeConfig = null;

    /**
     * @var \Magetrend\Email\Helper\Data|null
     */
    public $helper = null;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config|null
     */
    public $configResourceModel = null;

    /**
     * @var \Magento\Email\Model\ResourceModel\Template\CollectionFactory|null
     */
    public $templateCollection = null;

    /**
     * Magento email transactional templates' map
     * @var array
     */
    public $systemConfigMap = [

        // Magento_Sales
        [
            'path' => \Magento\Sales\Model\Order\Email\Container\OrderIdentity::XML_PATH_EMAIL_GUEST_TEMPLATE,
            'template_code' => 'mt_email_default_new_order_for_guest'
        ],

        [
            'path' => \Magento\Sales\Model\Order\Email\Container\OrderIdentity::XML_PATH_EMAIL_TEMPLATE,
            'template_code' => 'mt_email_default_new_order'
        ],

        [
            'path' => \Magento\Sales\Model\Order\Email\Container\OrderCommentIdentity::XML_PATH_EMAIL_GUEST_TEMPLATE,
            'template_code' => 'mt_email_default_order_update_for_guest'
        ],

        [
            'path' => \Magento\Sales\Model\Order\Email\Container\OrderCommentIdentity::XML_PATH_EMAIL_TEMPLATE,
            'template_code' => 'mt_email_default_order_update'
        ],

        [
            'path' => \Magento\Sales\Model\Order\Email\Container\InvoiceIdentity::XML_PATH_EMAIL_TEMPLATE,
            'template_code' => 'mt_email_default_new_invoice'
        ],

        [
            'path' => \Magento\Sales\Model\Order\Email\Container\InvoiceIdentity::XML_PATH_EMAIL_GUEST_TEMPLATE,
            'template_code' => 'mt_email_default_new_invoice_for_guest'
        ],

        [
            'path' => \Magento\Sales\Model\Order\Email\Container\InvoiceCommentIdentity::XML_PATH_EMAIL_GUEST_TEMPLATE,
            'template_code' => 'mt_email_default_invoice_update_for_guest'
        ],

        [
            'path' => \Magento\Sales\Model\Order\Email\Container\InvoiceCommentIdentity::XML_PATH_EMAIL_TEMPLATE,
            'template_code' => 'mt_email_default_invoice_update'
        ],

        [
            'path' => \Magento\Sales\Model\Order\Email\Container\CreditmemoIdentity::XML_PATH_EMAIL_TEMPLATE,
            'template_code' => 'mt_email_default_new_credit_memo'
        ],

        [
            'path' => \Magento\Sales\Model\Order\Email\Container\CreditmemoIdentity::XML_PATH_EMAIL_GUEST_TEMPLATE,
            'template_code' => 'mt_email_default_new_credit_memo_for_guest'
        ],

        [
            'path' => \Magento\Sales\Model\Order\Email\Container\CreditmemoCommentIdentity::XML_PATH_EMAIL_TEMPLATE,
            'template_code' => 'mt_email_default_credit_memo_update'
        ],

        [
            'path' => CreditmemoCommentIdentity::XML_PATH_EMAIL_GUEST_TEMPLATE,
            'template_code' => 'mt_email_default_credit_memo_update_for_guest'
        ],

        [
            'path' => \Magento\Sales\Model\Order\Email\Container\ShipmentIdentity::XML_PATH_EMAIL_TEMPLATE,
            'template_code' => 'mt_email_default_new_shipment'
        ],

        [
            'path' => \Magento\Sales\Model\Order\Email\Container\ShipmentIdentity::XML_PATH_EMAIL_GUEST_TEMPLATE,
            'template_code' => 'mt_email_default_new_shipment_for_guest'
        ],

        [
            'path' => \Magento\Sales\Model\Order\Email\Container\ShipmentCommentIdentity::XML_PATH_EMAIL_TEMPLATE,
            'template_code' => 'mt_email_default_shipment_update'
        ],

        [
            'path' => \Magento\Sales\Model\Order\Email\Container\ShipmentCommentIdentity::XML_PATH_EMAIL_GUEST_TEMPLATE,
            'template_code' => 'mt_email_default_shipment_update_for_guest'
        ],

        //Magento_Customer
        [
            'path' => \Magento\Customer\Model\AccountManagement::XML_PATH_REGISTER_EMAIL_TEMPLATE,
            'template_code' => 'mt_email_default_new_account'
        ],

        [
            'path' => \Magento\Customer\Model\AccountManagement::XML_PATH_CONFIRM_EMAIL_TEMPLATE,
            'template_code' => 'mt_email_default_new_account_confirmation_key'
        ],

        [
            'path' => \Magento\Customer\Model\AccountManagement::XML_PATH_CONFIRMED_EMAIL_TEMPLATE,
            'template_code' => 'mt_email_default_new_account_confirmed'
        ],

        [
            'path' => \Magento\Customer\Model\AccountManagement::XML_PATH_REGISTER_NO_PASSWORD_EMAIL_TEMPLATE,
            'template_code' => 'mt_email_default_new_account_without_password'
        ],

        [
            'path' => \Magento\Customer\Model\AccountManagement::XML_PATH_FORGOT_EMAIL_TEMPLATE,
            'template_code' => 'mt_email_default_forgot_password'
        ],

        [
            'path' => \Magento\Customer\Model\AccountManagement::XML_PATH_REMIND_EMAIL_TEMPLATE,
            'template_code' => 'mt_email_default_remind_password'
        ],

        [
            'path' => \Magento\Customer\Model\AccountManagement::XML_PATH_RESET_PASSWORD_TEMPLATE,
            'template_code' => 'mt_email_default_reset_password'
        ],

        //Magento_Newsletter
        [
            'path' => \Magento\Newsletter\Model\Subscriber::XML_PATH_CONFIRM_EMAIL_TEMPLATE,
            'template_code' => 'mt_email_default_subscription_confirmation'
        ],

        [
            'path' => \Magento\Newsletter\Model\Subscriber::XML_PATH_SUCCESS_EMAIL_TEMPLATE,
            'template_code' => 'mt_email_default_subscription_success'
        ],

        [
            'path' => \Magento\Newsletter\Model\Subscriber::XML_PATH_UNSUBSCRIBE_EMAIL_TEMPLATE,
            'template_code' => 'mt_email_default_unsubscription_success'
        ],

        //Magento_Sendfriend

        [
            'path' => 'sendfriend/email/template',
            'template_code' => 'mt_email_default_send_product_link_to_friend'
        ],

        [
            'path' => 'wishlist/email/email_template',
            'template_code' => 'mt_email_default_wish_list_sharing'
        ],

        //Magento_ProductAlert
        [
            'path' => \Magento\ProductAlert\Model\Email::XML_PATH_EMAIL_PRICE_TEMPLATE,
            'template_code' => 'mt_email_default_price_alert'
        ],
        [
            'path' => \Magento\ProductAlert\Model\Email::XML_PATH_EMAIL_STOCK_TEMPLATE,
            'template_code' => 'mt_email_default_stock_alert'
        ],

        //Magento_Contact
        [
            'path' => \Magento\Contact\Controller\Index::XML_PATH_EMAIL_TEMPLATE,
            'template_code' => 'mt_email_default_contact_form'
        ]
    ];

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManagerInterface,
        \Magetrend\Email\Model\Template $template,
        \Magento\Email\Model\ResourceModel\Template\CollectionFactory $collectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        \Magetrend\Email\Helper\Data $helper,
        \Magento\Config\Model\ResourceModel\Config $configResourceModel
    ) {
        $this->objectManager = $objectManagerInterface;
        $this->template = $template;
        $this->templateCollection = $collectionFactory;
        $this->scopeConfig = $scopeConfigInterface;
        $this->helper = $helper;
        $this->configResourceModel = $configResourceModel;
    }

    public function createTemplates($storeId = 0, $design = 'default')
    {
        $templateList = $this->template->getTemplateList($design);
        $locale = $this->scopeConfig->getValue(
            'general/locale/code',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
            $storeId
        );

        $collection = $this->templateCollection->create()
            ->addFieldToFilter('is_mt_email', 1)
            ->addFieldToFilter('store_id', $storeId);

        $skipTemplateList = [];
        if ($collection->getSize() > 0) {
            foreach ($collection as $templateItem) {
                $skipTemplateList[$templateItem->getOrigTemplateCode()] = 1;
            }
        }

        foreach ($templateList as $item) {
            if (isset($skipTemplateList[$item['value']])) {
                continue;
            }

            $this->template->createTemplate(
                $item['value'],
                $this->helper->getStoreCode($storeId).' >> '.$item['label'],
                null,
                $storeId,
                $locale
            );
        }
    }

    public function deleteTemplates($storeId = 0)
    {
        $collection = $this->templateCollection->create()
            ->addFieldToFilter('is_mt_email', 1)
            ->addFieldToFilter('store_id', $storeId);

        if ($collection->getSize() > 0) {
            foreach ($collection as $item) {
                $this->template->deleteTemplate($item);
            }
        }
    }

    public function updateTemplates($storeId = 0, $design = null)
    {
        $scope = ($storeId == 0)?'default':\Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        foreach ($this->systemConfigMap as $config) {
            if ($design) {
                $config['template_code'] = str_replace(
                    'mt_email_default_',
                    'mt_email_'.$design.'_',
                    $config['template_code']
                );
            }

            $templateCollection = $this->templateCollection->create()
                ->addFieldToFilter('is_mt_email', 1)
                ->addFieldToFilter('orig_template_code', $config['template_code'])
                ->addFieldToFilter('store_id', $storeId);

            if ($templateCollection->getSize() > 0) {
                //@codingStandardsIgnoreStart
                $template = $templateCollection->getFirstItem();
                //saving default system config value
                $mtEmailConfigPath = 'mtemail/default_value/'.hash('md5', $config['path']);
                //@codingStandardsIgnoreEnd
                $savedConfigValue = $this->scopeConfig->getValue($mtEmailConfigPath, $scope, $storeId);
                if (empty($savedConfigValue)) {
                    $configValue = $this->scopeConfig->getValue($config['path'], $scope, $storeId);
                    $this->objectManager->get('Magento\Config\Model\ResourceModel\Config')
                        ->saveConfig($mtEmailConfigPath, $configValue, $scope, $storeId);
                }

                $this->configResourceModel->saveConfig($config['path'], $template->getId(), $scope, $storeId);
            }
        }
    }

    public function restoreTemplates($storeId = 0)
    {
        $scope = ($storeId == 0)?'default':\Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        foreach ($this->systemConfigMap as $config) {
            //@codingStandardsIgnoreStart
            $mtEmailConfigPath = 'mtemail/default_value/' . hash('md5', $config['path']);
            //@codingStandardsIgnoreEnd
            $defaultValue = $this->scopeConfig->getValue($mtEmailConfigPath, $scope, $storeId);

            if (!empty($defaultValue)) {
                $this->objectManager->get('Magento\Config\Model\ResourceModel\Config')
                    ->saveConfig($config['path'], $defaultValue, $scope, $storeId);
            }
        }
    }
}
