<?php

namespace StripeIntegration\Payments\Model\Method\Api;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\InfoInterface;
use StripeIntegration\Payments\Helper;
use StripeIntegration\Payments\Helper\Logger;
use Magento\Framework\Exception\CouldNotSaveException;

abstract class Sources extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $type = '';

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = '';

    /**
     * @var string
     */
    //protected $_formBlockType = 'StripeIntegration\Payments\Block\Form';
    protected $_infoBlockType = 'StripeIntegration\Payments\Block\Info';

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canCaptureOnce = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_isGateway = true;
    protected $_isInitializeNeeded = true;
    protected $_canVoid = true;
    protected $_canUseInternal = false;
    protected $_canFetchTransactionInfo = true;
    protected $_canUseForMultishipping  = false;
    protected $_canCancelInvoice = true;
    protected $_canUseCheckout = true;
    protected $_canSaveCc = false;
    protected $_canInvoiceFromAdmin = false;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \StripeIntegration\Payments\Model\Config
     */
    protected $config;

    /**
     * @var Helper\Generic
     */
    protected $helper;

    /**
     * @var Helper\Api
     */
    protected $api;

    /**
     * @var \StripeIntegration\Payments\Model\StripeCustomer
     */
    protected $customer;

    /**
     * @var \Magento\Payment\Model\Method\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;
    protected $sessionManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    protected $cache;

    public $source = null;

    protected $saveSourceOnCustomer = false;
    protected $canReuseSource = false;
    protected $stripeCustomer = null;

    /**
     * Constructor
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \StripeIntegration\Payments\Model\Config $config,
        \StripeIntegration\Payments\Helper\Generic $helper,
        \StripeIntegration\Payments\Helper\Address $addressHelper,
        \StripeIntegration\Payments\Helper\Api $api,
        \StripeIntegration\Payments\Model\SourceFactory $sourceFactory,
        \StripeIntegration\Payments\Helper\Klarna $klarnaHelper,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );

        $this->cache = $context->getCacheManager();
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;

        $this->config = $config;
        $this->helper = $helper;
        $this->addressHelper = $addressHelper;
        $this->api = $api;
        $this->customer = $helper->getCustomerModel();
        $this->klarnaHelper = $klarnaHelper;
        $this->logger = $logger;
        $this->request = $request;
        $this->checkoutHelper = $checkoutHelper;
        $this->sessionManager = $sessionManager;
        $this->scopeConfig = $scopeConfig;
        $this->sourceFactory = $sourceFactory;
    }

    /**
     * Check whether payment method can be used
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if (!$this->config->initStripe())
            return false;

        if (parent::isAvailable($quote) === false) {
            return false;
        }

        if (!$this->isActive($quote ? $quote->getStoreId() : null)) {
            return false;
        }

        if (!$quote) {
            return false;
        }

        if ($this->config->isRedirectPaymentFlow())
            return false;

        // Check the minimum order amount
        $amount = $this->getConfigData('minimum_order_amount');
        if (is_numeric($amount) && $quote->getBaseGrandTotal() < $amount)
            return false;

        // Check currency is allowed
        $allowCurrencies = $this->getConfigData('allow_currencies');
        if (!$allowCurrencies && in_array($this->type, ['alipay', 'wechat']))
            return true;

        $allowedCurrencies = $this->getConfigData('allowed_currencies');

        // This is the "All currencies" setting
        if (!$allowedCurrencies)
            return true;

        $allowedCurrencies = explode(',', $allowedCurrencies);
        if (!in_array($quote->getQuoteCurrencyCode(), $allowedCurrencies))
        {
            return false;
        }

        return true;
    }

    public function adjustParamsForMethod(&$params, $payment, $order, $quote)
    {
        // Overwrite this method to specify custom params for this method
    }

    public function getRedirectUrlFrom($source)
    {
        if (!empty($source->redirect->url))
            return $source->redirect->url;

        return null;
    }

    /**
     * Method that will be executed instead of authorize or capture
     * if flag isInitializeNeeded set to true
     *
     * @param string $paymentAction
     * @param object $stateObject
     *
     * @return $this
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @api
     */
    public function initialize($paymentAction, $stateObject)
    {
        $this->source = null;
        $session = $this->checkoutHelper->getCheckout();
        $session->setStripePaymentsRedirectUrl(null);
        $session->setStripePaymentsClientSecret(null);
        $session->setStripePaymentsCheckoutSessionId(null);

        /** @var \Magento\Quote\Model\Quote\Payment $info */
        $info = $this->getInfoInstance();
        $info->setAdditionalInformation('source_id', null);

        /** @var \Magento\Sales\Model\Order $order */
        $this->order = $order = $info->getOrder();
        $quote = $this->helper->getQuote();

        // We don't want to send an order email until the payment is collected asynchronously
        $order->setCanSendNewEmailFlag(false);

        $amount = $order->getGrandTotal();
        $currency = $order->getOrderCurrencyCode();

        $cents = $this->helper->isZeroDecimal($currency) ? 1 : 100;

        $params = [
            'amount' => round($amount * $cents),
            'currency' => strtolower($currency),
            'description' => sprintf('Order #%s by %s', $order->getIncrementId(), $order->getCustomerName()),
        ];

        if ($this->order)
            $customerEmail = $this->order->getCustomerEmail();
        else
            $customerEmail = $this->helper->getCustomerEmail();

        if ($customerEmail && $this->config->isReceiptEmailsEnabled())
            $params['receipt_email'] = $customerEmail;

        $params['type'] = $this->type;
        $params['owner'] = $this->getBillingDetails();

        $params['redirect'] = [
            'return_url' => $this->urlBuilder->getUrl('stripe/payment/index', [
                '_secure' => $this->request->isSecure(),
                'payment_method' => $this->type
            ])
        ];
        $params['metadata'] = [
            'Order #' => $order->getIncrementId(),
        ];

        // Add Statement Descriptor
        $statementDescriptor = $this->getConfigData('statement_descriptor');
        if (!empty($statementDescriptor)) {
            $params[$this->type] = [
                'statement_descriptor' => $statementDescriptor
            ];
        }

        $this->adjustParamsForMethod($params, $info, $order, $quote);

        // Clean params
        $this->cleanParams($params);

        // Add payment method to the customer
        $this->stripeCustomer = null;
        if ($this->saveSourceOnCustomer || $this->config->getSaveCards())
        {
            try
            {
                $this->customer->createStripeCustomerIfNotExists(false, $order);
                $this->stripeCustomer = $this->customer->retrieveByStripeID();
                $customerId = $this->customer->getStripeId();
            }
            catch (\Stripe\Exception\CardException $e)
            {
                throw new LocalizedException(__($e->getMessage()));
            }
            catch (\Exception $e)
            {
                $this->helper->dieWithError(__('An error has occurred. Please contact us to complete your order.'), $e);
            }

            $info->setAdditionalInformation('customer_stripe_id', $customerId);
        }

        if ($this->canReuseSource)
            unset($params["amount"]);

        try {
            // Init Stripe Source
            $source = \Stripe\Source::create($params);

            if ($this->stripeCustomer)
                $this->stripeCustomer->sources->create(array('source' => $source));

            $info->setAdditionalInformation('source_id', $source->id);

            // Save values in session
            $redirectUrl = $this->getRedirectUrlFrom($source);
            $session->setStripePaymentsRedirectUrl($redirectUrl);
            $session->setStripePaymentsClientSecret($source->client_secret);
            $this->source = $source;
        }
        catch (\Stripe\Exception\CardException $e)
        {
            throw new LocalizedException(__($e->getMessage()));
        }
        catch (\Exception $e)
        {
            if (strstr($e->getMessage(), 'Invalid country') !== false) {
                throw new LocalizedException(__('Sorry, this payment method is not available in your country.'));
            }
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this;
    }

    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if ($this->helper->isAdmin() && !$this->_canInvoiceFromAdmin)
        {
            throw new LocalizedException(__("Sorry, this order cannot be invoiced or captured from the Magento admin. An paid invoice will automatically be created when the payment is collected."));
        }

        return parent::capture($payment, $amount);
    }

    /**
     * Cancel payment
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function cancel(InfoInterface $payment, $amount = null)
    {
        // Captured
        $creditmemo = $payment->getCreditmemo();
        if (!empty($creditmemo))
        {
            $rate = $creditmemo->getBaseToOrderRate();
            if (!empty($rate) && is_numeric($rate) && $rate > 0)
                $amount *= $rate;
        }
        // Authorized
        $amount = (empty($amount)) ? $payment->getOrder()->getTotalDue() : $amount;

        $currency = $payment->getOrder()->getOrderCurrencyCode();

        $transactionId = $payment->getLastTransId();

        // Case where an invoice is in Pending status, with no transaction ID, receiving a source.failed event which cancels the invoice.
        if (empty($transactionId))
        {
            $humanReadable = $this->helper->addCurrencySymbol($amount, $currency);
            $msg = __("Cannot refund %1 online because the order has no transaction ID. Creating an offline Credit Memo instead.", $humanReadable);
            $this->helper->addWarning($msg);
            $this->helper->addOrderComment($msg, $payment->getOrder());
            return $this;
        }

        if ($amount <= 0)
        {
            $humanReadable = $this->helper->addCurrencySymbol($amount, $currency);
            $msg = __("Cannot refund %1 online. Creating an offline Credit Memo instead.", $humanReadable);
            $this->helper->addWarning($msg);
            $this->helper->addOrderComment($msg, $payment->getOrder());
            return $this;
        }

        $transactionId = preg_replace('/-.*$/', '', $transactionId);

        try {
            $cents = 100;
            if ($this->helper->isZeroDecimal($currency))
                $cents = 1;

            $params = [
                "charge" => $transactionId,
                "amount" => round($amount * $cents)
            ];

            $this->config->getStripeClient()->refunds->create($params);

            $this->cache->save($value = "1", $key = "admin_refunded_" . $transactionId, ["stripe_payments"], $lifetime = 60 * 60);
        }
        catch (\Exception $e)
        {
            $msg = __("Could not refund payment: %1", $e->getMessage());
            $this->helper->addError($msg);
            throw new \Exception(__($e->getMessage()));
        }

        return $this;
    }

    /**
     * Refund specified amount for payment
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function refund(InfoInterface $payment, $amount)
    {
        $this->cancel($payment, $amount);

        return $this;
    }

    /**
     * Void payment method
     *
     * @param \Magento\Framework\DataObject|InfoInterface $payment
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function void(InfoInterface $payment)
    {
        $this->cancel($payment);

        return $this;
    }

    // Fixes https://github.com/magento/magento2/issues/5413 in Magento 2.1
    public function setId($code) { }
    public function getId() { return $this->_code; }

    /**
     * The Sources API throws an error if an unknown parameter is passed.
     * Delete all non-allowed params
     * @param $params
     */
    protected function cleanParams(&$params)
    {
        $allowed = array_flip(['type', 'amount', 'currency', 'owner', 'redirect', 'metadata', $this->type]);
        $params = array_intersect_key($params, $allowed);
    }

    public function getBillingDetails()
    {
        $address = $this->order->getBillingAddress();

        $data = $this->addressHelper->getStripeAddressFromMagentoAddress($address);

        if ($this->getTestEmail())
            $data['email'] = $this->getTestEmail();

        return $data;
    }

    /**
     * For testing multibanco
     * @return bool
     */
    public function getTestEmail()
    {
        return false;
    }

    /**
     * For testing multibanco
     * @return bool
     */
    public function getTestName()
    {
        return false;
    }

    /**
     * For validating Multibanco test emails
     * @param $email
     * @return bool
     */
    public function isEmailValid($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL))
            return true;

        return false;
    }
}
