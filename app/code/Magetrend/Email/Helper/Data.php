<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Helper;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\ResourceModel\Quote\Collection;

class Data
{
    const XML_PATH_DIRECTION = 'mtemail/general/direction';

    const XML_PATH_TEXT_VERSION = 'mtemail/general/text_version';

    const XML_PATH_SINGLE_TEMPLATE_MODE = 'mtemail/general/single_template_mode';

    const XML_PATH_EMAIL_HIDE_SKU = 'mtemail/email/hide_sku';

    const XML_PATH_EMAIL_GLOBAL_CSS = 'mtemail/email/css';

    const XML_PATH_EMAIL_TEST_EMAIL_ADDRESS = 'mtemail/email/test_email';

    const XML_PATH_EMAIL_TRACKING_PARAMS = 'mtemail/email/tracking_params';

    const XML_PATH_EMAIL_SHIPPING_TRACKING_LINK = 'mtemail/email/track_link';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var \Magento\Newsletter\Model\Subscriber
     */
    public $subscriber;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\Track
     */
    public $track;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface $orderInterface
     */
    public $orderInterface;

    /**
     * @var \Magento\Sales\Api\Data\InvoiceInterface
     */
    public $invoiceInterface;

    /**
     * @var \Magento\Sales\Api\Data\ShipmentInterface
     */
    public $shipmentInterface;

    /**
     * @var \Magento\Sales\Api\Data\CreditmemoInterface
     */
    public $creditmemoInterface;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    public $cartRepository;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    public $currencyFactory;

    /**
     * @var \Magento\Sales\Model\Order\Address\Renderer
     */
    public $addressRenderer;

    public $templateVarManager;

    public $quoteCollectionFactory;

    /**
     * Data constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManagerInterface
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @param \Magento\Sales\Model\Order\Shipment\Track $track
     * @param \Magento\Sales\Api\Data\OrderInterface $orderInterface
     * @param \Magento\Sales\Api\Data\InvoiceInterface $invoiceInterface
     * @param \Magento\Sales\Api\Data\ShipmentInterface $shipmentInterface
     * @param \Magento\Sales\Api\Data\CreditmemoInterface $creditmemoInterface
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \Magetrend\Email\Model\TemplateVarManager $templateVarManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManagerInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        \Magento\Newsletter\Model\Subscriber $subscriber,
        \Magento\Sales\Model\Order\Shipment\Track $track,
        \Magento\Sales\Api\Data\OrderInterface $orderInterface,
        \Magento\Sales\Api\Data\InvoiceInterface $invoiceInterface,
        \Magento\Sales\Api\Data\ShipmentInterface $shipmentInterface,
        \Magento\Sales\Api\Data\CreditmemoInterface $creditmemoInterface,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magetrend\Email\Model\TemplateVarManager $templateVarManager
    ) {
        $this->objectManager = $objectManagerInterface;
        $this->storeManager = $storeManagerInterface;
        $this->scopeConfig = $scopeConfigInterface;
        $this->subscriber = $subscriber;
        $this->track = $track;
        $this->orderInterface = $orderInterface;
        $this->invoiceInterface = $invoiceInterface;
        $this->shipmentInterface = $shipmentInterface;
        $this->creditmemoInterface = $creditmemoInterface;
        $this->cartRepository = $cartRepository;
        $this->currencyFactory = $currencyFactory;
        $this->addressRenderer = $addressRenderer;
        $this->templateVarManager = $templateVarManager;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
    }

    public function isActive($storeId = null)
    {
        return true;
    }

    public function getHash($key, $blockName, $blockId, $templateId)
    {
        $hash = hash('md5', 'var_'.$key.'_'.$blockName.'_'.$blockId.'_'.$templateId);
        return $hash;
    }

    public function getUniqueBlockId()
    {
        return time();
    }

    public function getDemoVars($template)
    {
        $store = $this->storeManager->getStore($template->getStoreId());
        $order = $this->getDemoOrder($store);
        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();
        $customer = $this->getDemoCustomer();
        $payment = $order->getPayment();
        $paymentMethod = __('Payment method is not available');
        if ($payment) {
            $paymentMethod = $payment->getMethodInstance()->getTitle();
        }

        $vars = [
            'customer' => $customer,
            'checkoutType' => (string)__('One Step Checkout'),
            'reason' => (string)__('Suspected Fraud'),
            'customerEmail' => $customer->getEmail(),
            'comment' => 'Lorem ipsum dolor sit, consectetuer adipiscing elit. Aenean commodo ligula eget dolor.',
            'subscriber' => $this->getDemoSubscriber(),
            'store' => $store,
            'order' => $order,
            'quote' => $this->getDemoQuote($order->getQuoteId()),
            'billing' => $billingAddress,
            'billingAddress' => $billingAddress,
            'shippingAddress' => $shippingAddress,
            'shippingMethod' => $order->getShippingDescription(),
            'paymentMethod' => $paymentMethod,
            'dateAndTime' => $order->getCreatedAt(),
            'creditmemo' => $this->getDemoCreditMemo($store),
            'invoice' => $this->getDemoInvoice($store),
            'shipment' => $this->getDemoShipment($store),
            'data' => $this->getDemoContactRequest(),
            'product_name' => (string)__('Product Name'),
            'email' => 'john@doe.com',
            'name' => (string)__('John Doe'),
            'message' => (string)__('Lorem ipsum dolor sit, consectetuer adipiscing elit.'),
            'sender_email' => (string)__('sender@doe.com'),
            'sender_name' => (string)__('Sender Name'),
            'product_url' => 'http://store.demo.store.com/product.url.html',
            'product_image' => '',
            'customerName' => (string)__('John Doe'),
            'viewOnSiteLink' => 'http://store.demo.store.com/product.url.html',
            'items' => '*ITEMS*',
            'total' => '*TOTALS*',
            'alertGrid' => '*GRID*',
            'is_pickup_order' => 1,
            'shipping_msg' => __('Custom shipping message'),
            'pickupAddress' => __('Pickup Address 13 street. IL, US'),
        ];

        if ($shippingAddress) {
            $vars['formattedShippingAddress'] = $this->addressRenderer->format($shippingAddress, 'html');
        }

        if ($billingAddress) {
            $vars['formattedBillingAddress'] = $this->addressRenderer->format($billingAddress, 'html');
        }

        $this->templateVarManager->reset();
        $this->templateVarManager->setVariables($vars);
        $vars['mtVar'] = $this->templateVarManager;
        return $vars;
    }

    public function getDemoSubscriber()
    {
        $subscriber = $this->objectManager->create('Magento\Newsletter\Model\Subscriber')
            ->setSubscriberEmail('jd1@ex.com');
        return $subscriber;
    }

    public function getDemoCustomer()
    {
        $customer = $this->objectManager->create('Magento\Customer\Model\Customer')
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setEmail('jd1@ex.com')
            ->setPassword('soMepaSswOrd');

        return $customer;
    }

    public function getDemoOrder($store)
    {
        $id = $this->scopeConfig
            ->getValue('mtemail/demo/order_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId());

        $order = $this->orderInterface->loadByIncrementId((string)$id);
        if (!$order->getId()) {
            $order = $this->orderInterface->load($id);
        }
        return $order;
    }

    public function getDemoInvoice($store)
    {
        $id = $this->scopeConfig
            ->getValue('mtemail/demo/invoice_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId());

        $invoice = $this->invoiceInterface->loadByIncrementId((string)$id);
        if (!$invoice->getId()) {
            $invoice = $this->invoiceInterface->load($id);
        }
        return $invoice;
    }

    public function getDemoShipment($store)
    {
        $id = $this->scopeConfig
            ->getValue('mtemail/demo/shipment_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId());

        $shipment = $this->shipmentInterface->loadByIncrementId((string)$id);
        if (!$shipment->getId()) {
            $shipment = $this->shipmentInterface->load($id);
        }

        if (!$shipment->getAllTracks()) {
            $track = $this->track->setData([
                    'title' => 'DHL',
                    'track_number' => '2040RR89S1'
                ]);
            $shipment->addTrack($track);
        }

        return $shipment;
    }

    public function getDemoCreditMemo($store)
    {
        $id = $this->scopeConfig
            ->getValue('mtemail/demo/creditmemo_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId());

        $creditMemo = $this->creditmemoInterface->load((string)$id, 'increment_id');
        if (!$creditMemo->getId()) {
            $creditMemo = $this->creditmemoInterface->load($id);
        }

        return $creditMemo;
    }

    public function getDemoContactRequest()
    {
        $postObject = new \Magento\Framework\DataObject();
        $postObject->setData([
            'name' => 'John Smith',
            'email'=> 'john.smith@magetrend.com',
            'telephone' => '0037060000001',
            'comment' => 'Hello, I need help with order process. Can you help?'
        ]);
        return $postObject;
    }

    /**
     * Return store code by using store id
     *
     * @param int $storeId
     * @return string
     */
    public function getStoreCode($storeId = 0)
    {
        $storeCode = $this->storeManager->getStore($storeId)->getCode();
        if ($storeCode == 'admin') {
            $storeCode = 'Default';
        }
        $storeCode = ucfirst($storeCode);

        return $storeCode;
    }

    /**
     * Returns email template code
     *
     * @param $template
     */
    public function getTheme($template)
    {
        $origCode = $template->getOrigTemplateCode();
        $origCode = explode('_', $origCode);
        return $origCode[2];
    }

    /**
     * Get block list from template content
     *
     * @param \Magento\Email\Model\Template $template
     * @return array
     */
    public function parseBlockList($template)
    {
        $content = $template->getTemplateText();
        if (substr_count($content, '{{layout handle="') == 0) {
            return [];
        }

        $result = [];
        $blockList = explode('{{layout handle="', $content);
        foreach ($blockList as $block) {
            $blockTmp = explode('}}', $block);
            if (isset($blockTmp[0]) && isset($blockTmp[1])) {
                $result[] = '{{layout handle="'.$blockTmp[0].'}}';
            }
        }

        return $result;
    }

    /**
     * get block data from string format
     *
     * @param $block
     * @return array
     */
    public function parseBlockData($block)
    {
        $blockTmp = str_replace(['{{', '}}', 'layout', "'", '"'], '', $block);
        $blockTmp = explode(' ', $blockTmp);
        $result = [];
        foreach ($blockTmp as $attribute) {
            if (substr_count($attribute, 'block_name') == 1) {
                $result['block_name'] = str_replace(['block_name', ' ', '='], '', $attribute);
            }

            if (substr_count($attribute, 'block_id') == 1) {
                $result['block_id'] = str_replace(['block_id', ' ', '='], '', $attribute);
            }
        }

        return $result;
    }

    /**
     * Get Block Name List
     *
     * @param $blockList
     * @return array
     */
    public function getBlockNameList($blockList)
    {
        if (count($blockList) == 0) {
            return [];
        }

        $result = [];
        foreach ($blockList as $block) {
            $blockData = $this->parseBlockData($block);
            $result[] = $blockData['block_name'];
        }

        return $result;
    }

    public function isSingleTemplateMode()
    {
        return $this->scopeConfig->getValue(
            \Magetrend\Email\Helper\Data::XML_PATH_SINGLE_TEMPLATE_MODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            0
        );
    }

    public function isPlainTextVersionEnabled()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_TEXT_VERSION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            0
        );
    }

    public function hideSku($storeId = 0)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_HIDE_SKU,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getGlobalCss($storeId = 0)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_GLOBAL_CSS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getTrackingParams($storeId = 0)
    {
        $trackingParams = $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_TRACKING_PARAMS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (empty($trackingParams)) {
            return [];
        }

        $trackingParams = explode('&', $trackingParams);
        $results = [];
        foreach ($trackingParams as $param) {
            if (strpos($param, '=') === null) {
                continue;
            }

            $param = explode('=', $param);
            $results[$param[0]] = $param[1];
        }

        return $results;
    }

    public function getSubtotalDisplayType($store = null)
    {
        return $this->scopeConfig->getValue(
            \Magento\Tax\Model\Config::XML_PATH_DISPLAY_SALES_SUBTOTAL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getPriceDisplayType($store = null)
    {
        return $this->scopeConfig->getValue(
            \Magento\Tax\Model\Config::XML_PATH_DISPLAY_SALES_PRICE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    public function getDemoQuote($quoteId)
    {
        try {
            return $this->cartRepository->get($quoteId);
        } catch (NoSuchEntityException $e) {
            $quoteCollection = $this->quoteCollectionFactory->create()
                ->setPageSize(1)
                ->setCurPage(1)
                ->setOrder('entity_id', 'ASC');

            if ($quoteCollection->getSize() == 0) {
                return false;
            }

            return $quoteCollection->getFirstItem();
        }
    }

    public function formatPrice($currecyCode, $price)
    {
        return $this->getCurrency($currecyCode)->formatPrecision($price, 2, [], true, false);
    }

    public function formatPriceByStoreId($price, $storeId = null)
    {
        $currecyCode = $this->storeManager->getStore($storeId)->getCurrentCurrency()->getCode();
        return $this->getCurrency($currecyCode)->formatPrecision($price, 2, [], true, false);
    }

    public function getCurrency($currecyCode)
    {
        if (!isset($this->currency[$currecyCode])) {
            $currency = $this->currencyFactory->create();
            $currency->load($currecyCode);
            $this->currency[$currecyCode] = $currency;
        }

        return $this->currency[$currecyCode];
    }

    public function showFPT($storeId = 0)
    {
        $isActive = $this->scopeConfig->getValue(
            \Magento\Weee\Model\Config::XML_PATH_FPT_ENABLED,
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $storeId
        );

        return $isActive;
    }

    public function getDesignName($template)
    {
        if (!$template || !$template->getId()) {
            return '';
        }

        $origTemplateCode = $template->getOrigTemplateCode();
        if (strpos($origTemplateCode, 'mt_email_') === false) {
            return '';
        }

        $theme = explode('_', $origTemplateCode);
        $theme = $theme[2];

        return $theme;
    }

    public function getTestEmailAddress($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_TEST_EMAIL_ADDRESS,
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
    }
}
