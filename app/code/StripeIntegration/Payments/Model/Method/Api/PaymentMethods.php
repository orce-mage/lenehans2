<?php

namespace StripeIntegration\Payments\Model\Method\Api;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\InfoInterface;
use StripeIntegration\Payments\Helper;
use StripeIntegration\Payments\Helper\Logger;

abstract class PaymentMethods extends \Magento\Payment\Model\Method\AbstractMethod
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

    protected $localeHelper;

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

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

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
        \StripeIntegration\Payments\Helper\Locale $localeHelper,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Checkout\Helper\Data $checkoutHelper,
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

        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;

        $this->config = $config;
        $this->helper = $helper;
        $this->addressHelper = $addressHelper;
        $this->api = $api;
        $this->customer = $helper->getCustomerModel();;
        $this->logger = $logger;
        $this->request = $request;
        $this->checkoutHelper = $checkoutHelper;
        $this->localeHelper = $localeHelper;

        $this->scopeConfig = $scopeConfig;
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

        // Check currency is allowed
        $allowCurrencies = $this->config->getConfigData('allow_currencies', $this->type);
        if (!$allowCurrencies && in_array($this->type, ['alipay', 'wechat']))
            return true;

        $allowedCurrencies = $this->config->getConfigData('allowed_currencies', $this->type);

        // This is the "All currencies" setting
        if (!$allowedCurrencies)
            return true;

        $allowedCurrencies = explode(',', $allowedCurrencies);
        if (!in_array($quote->getQuoteCurrencyCode(), $allowedCurrencies))
            return false;

        return true;
    }

    public function getMetadata()
    {
        return [
            'Order #' => $this->order->getIncrementId()
        ];
    }

    public function adjustParamsForPaymentIntent(&$params, $order)
    {
        // Overwrite this method to specify custom params for this payment intent
    }

    // As we already have an order object, we pass more params here than the regular PaymentIntent singleton does
    // Uses automatic PI confirmation
    // Reduces API calls, allows for method overwrites
    public function createPaymentIntent($order = null)
    {
        $amount = $this->order->getGrandTotal();
        $currency = $this->order->getOrderCurrencyCode();

        $cents = $this->helper->isZeroDecimal($currency) ? 1 : 100;

        $params = [
            'amount' => round($amount * $cents),
            'currency' => $currency,
            'description' => sprintf('Order #%s by %s', $this->order->getIncrementId(), $this->order->getCustomerName()),
            'payment_method_types' => [ $this->type ],
            'metadata' => $this->getMetadata()
        ];

        $paymentMethodOptions = $this->getPaymentMethodOptions();
        if (!empty($paymentMethodOptions))
            $params["payment_method_options"] = [$this->type => $paymentMethodOptions];

        if ($order)
            $customerEmail = $order->getCustomerEmail();
        else
            $customerEmail = $this->helper->getCustomerEmail();

        if ($customerEmail && $this->config->isReceiptEmailsEnabled())
            $params['receipt_email'] = $customerEmail;

        $statementDescriptor = $this->getConfigData('statement_descriptor');
        if (!empty($statementDescriptor))
            $params["statement_descriptor"] = $statementDescriptor;

        $this->adjustParamsForPaymentIntent($params, $this->order);

        return \Stripe\PaymentIntent::create($params);
    }

    abstract public function createPaymentMethod();

    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);

        if ($this->config->getIsStripeAPIKeyError())
            $this->helper->dieWithError("Invalid API key provided");

        return $this;
    }

    public function getBillingDetails()
    {
        $address = $this->order->getBillingAddress();

        $data = $this->addressHelper->getStripeAddressFromMagentoAddress($address);

        if ($this->getTestEmail())
            $data['email'] = $this->getTestEmail();

        return $data;
    }

    protected function onPaymentIntentConfirmed($paymentIntent, $order)
    {
        // Overwrite
    }

    protected function getPaymentMethodOptions()
    {
        // Overwrite
        return [];
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
        $session = $this->checkoutHelper->getCheckout();
        $session->setStripePaymentsRedirectUrl(null);
        $session->setStripePaymentsClientSecret(null);
        $session->setStripePaymentsCheckoutSessionId(null);

        /** @var \Magento\Quote\Model\Quote\Payment $info */
        $info = $this->getInfoInstance();

        /** @var \Magento\Sales\Model\Order $order */
        $this->order = $info->getOrder();
        $quote = $this->order->getQuote();

        // We don't want to send an order email until the payment is collected asynchronously
        $this->order->setCanSendNewEmailFlag(false);

        $paymentIntent = $this->createPaymentIntent($this->order);
        $paymentMethod = $this->createPaymentMethod();

        $paymentIntent->confirm([
            "payment_method" => $paymentMethod->id,
            "return_url" => $this->urlBuilder->getUrl('stripe/payment/index', [
                '_secure' => $this->request->isSecure(),
                'payment_method' => $this->type
            ])
        ]);

        $this->onPaymentIntentConfirmed($paymentIntent, $this->order);

        // Handle error_code and error_message
            // Save values in session

        // FPX has a redirect, Oxxo doesn't
        if (isset($paymentIntent->next_action->redirect_to_url->url))
        {
            $session->setStripePaymentsRedirectUrl($paymentIntent->next_action->redirect_to_url->url);
            $session->setStripePaymentsClientSecret($paymentIntent->client_secret);

            $comment = __("The customer was redirected externally for payment processing.");
            $this->order->addStatusToHistory($status = false, $comment, $isCustomerNotified = false);
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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        /** @var \Magento\Payment\Helper\Data $helper */
        $helper = $objectManager->get('Magento\Payment\Helper\Data');

        /** @var \StripeIntegration\Payments\Model\PaymentMethod $method */
        $method = $helper->getMethodInstance('stripe_payments');

        $method->cancel($payment, $amount);
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
