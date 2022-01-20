<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Stripe extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package Magenest_Stripe
 */

namespace Magenest\StripePayment\Model;

use Magenest\StripePayment\Exception\StripePaymentException;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\Method\AbstractMethod;
use Magenest\StripePayment\Helper\Data as DataHelper;
use Magento\Payment\Model\Method\Logger;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\Quote\Payment;
use Magenest\StripePayment\Helper\Logger as stripeHelper;

/**
 * Class Intents
 * @package Magenest\StripePayment\Model
 */
class Intents extends AbstractMethod
{
    /**
     * @var bool
     */
    protected $_canCapture = true;
    /**
     * @var bool
     */
    protected $_canAuthorize = true;
    /**
     * @var bool
     */
    protected $_isInitializeNeeded = true;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var CardFactory
     */
    protected $_cardFactory;
    /**
     * @var bool
     */
    protected $_canRefund = true;
    /**
     * @var stripeHelper
     */
    protected $stripeLogger;
    /**
     *
     */
    const CODE = 'magenest_stripe_intents';
    /**
     * @var string
     */
    protected $_code = self::CODE;
    /**
     * @var DataHelper
     */
    protected $_helper;
    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;
    /**
     * @var \Magenest\StripePayment\Helper\Config
     */
    protected $_stripeConfig;
    /**
     * @var Payment
     */
    protected $_quotePayment;
    /**
     * @var StripePaymentMethod
     */
    protected $stripeCard;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerInterface;
    /**
     * @var \Magento\Framework\Model\Context
     */
    private $context;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Magento\Framework\Api\ExtensionAttributesFactory
     */
    private $extensionFactory;
    /**
     * @var \Magento\Payment\Helper\Data
     */
    private $paymentData;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var \Magento\Framework\Model\ResourceModel\AbstractResource|null
     */
    private $resource;
    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb|null
     */
    private $resourceCollection;
    /**
     * @var array
     */
    private $data;
    /**
     * @var DirectoryHelper|null
     */
    private $directory;

    /**
     * Intents constructor.
     * @param Payment $quotePayment
     * @param CheckoutSession $checkoutSession
     * @param DataHelper $dataHelper
     * @param StripePaymentMethod $stripePaymentMethod
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magenest\StripePayment\Helper\Config $stripeConfig
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param DirectoryHelper|null $directory
     */
    public function __construct(
        Payment $quotePayment,
        CheckoutSession $checkoutSession,
        DataHelper $dataHelper,
        stripeHelper $stripeLogger,
        StripePaymentMethod $stripePaymentMethod,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magenest\StripePayment\Helper\Config $stripeConfig,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Logger $logger,
        \Magento\Customer\Model\Session $customerSession,
        \Magenest\StripePayment\Model\CardFactory $cardFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        DirectoryHelper $directory = null
    )
    {
        $this->_quotePayment = $quotePayment;
        $this->_checkoutSession = $checkoutSession;
        $this->_helper = $dataHelper;
        $this->_stripeConfig = $stripeConfig;
        $this->stripeCard = $stripePaymentMethod;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->stripeLogger = $stripeLogger;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig, $logger,
            $resource,
            $resourceCollection,
            $data,
            $directory
        );
        $this->context = $context;
        $this->registry = $registry;
        $this->extensionFactory = $extensionFactory;
        $this->customAttributeFactory = $customAttributeFactory;
        $this->paymentData = $paymentData;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->resource = $resource;
        $this->resourceCollection = $resourceCollection;
        $this->data = $data;
        $this->directory = $directory;
        $this->customerSession = $customerSession;
        $this->_cardFactory = $cardFactory;
    }

    /**
     * @param \Magento\Framework\DataObject $data
     * @return $this|AbstractMethod
     * @throws LocalizedException
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        $this->_debug("Function: assignData");
        $infoInstance = $this->getInfoInstance();
        $additionalData = $data->getData('additional_data');
        parent::assignData($data);

        $additionalCustomerId = isset($additionalData['customer_id']) ? $additionalData['customer_id'] : "";
        $customerId = $this->customerSession->getCustomerId() ? $this->customerSession->getCustomerId() : $additionalCustomerId;
        $isSaveOption = isset($additionalData['saved']) ? $additionalData['saved'] : "";
        $cardID = isset($additionalData['cardId']) ? $additionalData['cardId'] : false;
        if ($cardID) {
            $intentId = $this->addPaymentInfoData($infoInstance, $cardID, $customerId);
            $infoInstance->setAdditionalInformation('payment_token', $intentId);
            $isSaveOption = "0";
        }

        $infoInstance->setAdditionalInformation('customer_id', $customerId);
        $infoInstance->setAdditionalInformation('save_option', $isSaveOption);
        return $this;
    }

    /**
     * @param $infoInstance
     * @param $_cardID
     * @param null $_customerId
     * @return mixed
     */
    public function addPaymentInfoData($infoInstance, $_cardID, $_customerId = null)
    {
        //get info data from database
        $cardData = [];
        $cardModel = $this->_cardFactory->create()->load($_cardID);
        $customerId = $cardModel->getData('magento_customer_id');
        if (!$_customerId) {
            $_customerId = $this->customerSession->getCustomerId();
        }
        if ($customerId == $_customerId) {
            $_cardID = $cardModel->getData('card_id');
            $infoInstance->setAdditionalInformation('three_d_secure', isset($cardData['three_d_secure']) ? $cardData['three_d_secure'] : "");
            $infoInstance->setAdditionalInformation('db_source', true);
            $infoInstance->setAdditionalInformation('card_id', $_cardID);
            $cardData = $cardModel->getData();
        }

        $infoInstance->addData(
            [
                'cc_type' => isset($cardData['brand']) ? $cardData['brand'] : "",
                'cc_last_4' => isset($cardData['last4']) ? $cardData['last4'] : "",
                'cc_exp_month' => isset($cardData['exp_month']) ? $cardData['exp_month'] : "",
                'cc_exp_year' => isset($cardData['exp_year']) ? $cardData['exp_year'] : ""
            ]
        );

        return $_cardID;
    }

    /**
     * @param string $paymentAction
     * @param object $stateObject
     * @return AbstractMethod
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function initialize($paymentAction, $stateObject)
    {
        try {
            if (!class_exists(\Stripe\Stripe::class)) {
                throw new StripePaymentException(
                    __("Stripe PHP library was not installed")
                );
            }
            $payment = $this->getInfoInstance();
            $order = $payment->getOrder();
            $quote = $this->_checkoutSession->getQuote();
            $grandTotal = $quote->getBaseGrandTotal();
            $currency = $quote->getBaseCurrencyCode();

            $card_id = $payment->getAdditionalInformation("card_id");
            if ($card_id) {
                $paymentIntent = $this->chargeSaveCard($grandTotal, $currency, $order, $card_id);
            } else {
                $paymentIntent = $this->getPaymentIntent($grandTotal, $currency, $order);
            }

            $payment->setAdditionalInformation('intent_id', $paymentIntent->id);
            $payment->setAdditionalInformation('client_secret', $paymentIntent->client_secret);
            return parent::initialize($paymentAction, $stateObject);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new LocalizedException(__($e->getMessage()));
        } catch (StripePaymentException $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * @param $grandtotal
     * @param $currency
     * @return \Stripe\PaymentIntent
     * @throws StripePaymentException
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function getPaymentIntent($grandtotal, $currency, $order)
    {
        $paymentAction = $this->_stripeConfig->getPaymentActionIntents();
        $amount = $this->_helper->getPaymentAmountByCurrency($grandtotal, $currency);
        $this->_helper->initStripeApi();
        $intent = \Stripe\PaymentIntent::create([
            'amount' => $amount,
            'currency' => $currency,
            'setup_future_usage' => 'off_session',
            'payment_method_types' => ["card"],
            'description' => $this->_helper->getPaymentDescription($order),
            'metadata' => $this->_helper->getPaymentMetaData($order),
            'capture_method' => ($paymentAction == 'authorize_capture') ? 'automatic' : 'manual',
        ]);

        return $intent;
    }

    /**
     * @param $grandtotal
     * @param $currency
     * @param $order
     * @param $card_id
     * @return \Stripe\PaymentIntent
     * @throws LocalizedException
     * @throws StripePaymentException
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function chargeSaveCard($grandtotal, $currency, $order, $card_id)
    {
        $paymentAction = $this->_stripeConfig->getPaymentActionIntents();
        $amount = $this->_helper->getPaymentAmountByCurrency($grandtotal, $currency);
        $this->_helper->initStripeApi();
        $stripeCustomerId = $this->_helper->getStripeCustomerId();
        $paymentMethod = \Stripe\PaymentMethod::all([
            'customer' => $stripeCustomerId,
            'type' => 'card',
        ]);
        foreach ($paymentMethod->data as $item) {
            if ($item->id == $card_id) {
                try {
                    $intent = \Stripe\PaymentIntent::create([
                        'amount' => $amount,
                        'currency' => $currency,
                        'customer' => $stripeCustomerId,
                        'payment_method' => $card_id,
                        'off_session' => true,
                        'confirm' => true,
                        'description' => $this->_helper->getPaymentDescription($order),
                        'metadata' => $this->_helper->getPaymentMetaData($order),
                        'capture_method' => ($paymentAction == 'authorize_capture') ? 'automatic' : 'manual'
                    ]);
                    return $intent;
                } catch (\Stripe\Exception\CardException $e) {
                    $payment_intent_id = $e->getError()->payment_intent->id;
                    $payment_intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);
                    throw new LocalizedException(__('Error code is:' . $e->getError()->code));
                }
            }
        }
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return AbstractMethod
     * @throws LocalizedException
     * @throws StripePaymentException
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        try {
            $this->_helper->initStripeApi();
            $intentId = $payment->getAdditionalInformation("intent_id");
            if ($intentId) {
                $intent = \Stripe\PaymentIntent::retrieve($intentId);
                $this->_debug($intent->getLastResponse()->json);
                $charges = \Stripe\Charge::all([
                    'payment_intent' => $intentId,
                    'limit' => 3,
                ]);

                foreach ($charges as $charge) {
                    $chargeStatus = $charge->status;
                    if ($chargeStatus == 'succeeded') {
                        $chargeId = $charge->id;
                        $payment->setAdditionalInformation("stripe_charge_id", $chargeId);
                        $transactionId = $charge->balance_transaction;
                        $payment->setTransactionId($transactionId)
                            ->setLastTransId($transactionId);
                        $payment->setIsTransactionClosed(0);
                        $payment->setShouldCloseParentTransaction(0);
                        $chargeFlag = true;
                        $stripeAmount = $charge->amount;
                    }
                    $this->_helper->checkTransaction($payment, $stripeAmount);
                    if (!$chargeFlag) {
                        throw new LocalizedException(
                            __("Payment failed")
                        );
                    }
                }

                return parent::capture($payment, $amount);
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return AbstractMethod
     * @throws LocalizedException
     * @throws StripePaymentException
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        try {
            $this->_helper->initStripeApi();
            $intentId = $payment->getAdditionalInformation('intent_id');
            if ($intentId) {
                $intent = \Stripe\PaymentIntent::retrieve($intentId);
                $this->_debug($intent->getLastResponse()->json);
                $charges = \Stripe\Charge::all([
                    'payment_intent' => $intentId,
                    'limit' => 3,
                ]);

                foreach ($charges as $charge) {
                    $chargeStatus = $charge->status;
                    if ($chargeStatus == 'succeeded') {
                        $chargeId = $charge->id;
                        $payment->setAdditionalInformation("stripe_charge_id", $chargeId);
                        $transactionId = $charge->balance_transaction;
                        $payment->setTransactionId($transactionId)
                            ->setLastTransId($transactionId);
                        $payment->setIsTransactionClosed(0);
                        $payment->setShouldCloseParentTransaction(0);
                        $chargeFlag = true;
                        $stripeAmount = $charge->amount;
                    }
                    $this->_helper->checkTransaction($payment, $stripeAmount);
                    if (!$chargeFlag) {
                        throw new LocalizedException(
                            __("Payment failed")
                        );
                    }
                }
                return parent::authorize($payment, $amount);
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return AbstractMethod
     * @throws LocalizedException
     * @throws StripePaymentException
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        try {
            $this->_helper->initStripeApi();
            $chargeId = $payment->getAdditionalInformation("stripe_charge_id");
            $request = $this->_helper->createRefundRequest($payment, $chargeId, $amount);

            $refund = \Stripe\Refund::create($request);
            $this->_debug($refund->getLastResponse()->json);
            $transactionId = $refund->balance_transaction;
            if ($transactionId) {
                $payment->setTransactionId($transactionId);
            }
            $this->_debug($refund->getLastResponse()->json);
            $payment->setShouldCloseParentTransaction(0);
            return parent::refund($payment, $amount);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * @param array|string $debugData
     */
    protected function _debug($debugData)
    {
        $this->stripeLogger->debug(var_export($debugData, true));
    }
}