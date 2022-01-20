<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Model;

use Magenest\StripePayment\Helper\Constant;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Payment\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magenest\StripePayment\Helper\Data as DataHelper;
use Magenest\StripePayment\Helper\Config as ConfigHelper;

class StripePaymentMethod extends \Magento\Payment\Model\Method\Cc
{
    const CODE = 'magenest_stripe';
    protected $_code = self::CODE;
    protected $_isGateway = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canCaptureOnce = true;
    protected $_canAuthorize = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = true;
    protected $_cardFactory;
    protected $_helper;
    /**
     * @var \Magenest\StripePayment\Helper\Logger $stripeLogger
     */
    public $stripeLogger;
    public $_config;
    protected $customerSession;
    protected $_messageManager;
    protected $storeManagerInterface;
    protected $request;

    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        ModuleListInterface $moduleList,
        TimezoneInterface $localeDate,
        DataHelper $dataHelper,
        ConfigHelper $config,
        \Magenest\StripePayment\Helper\Logger $stripeLogger,
        \Magenest\StripePayment\Model\CardFactory $cardFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Framework\App\RequestInterface $request,
        $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $moduleList,
            $localeDate,
            null,
            null,
            $data
        );

        $this->_cardFactory = $cardFactory;
        $this->_helper = $dataHelper;
        $this->_config = $config;
        $this->request = $request;
        $this->stripeLogger = $stripeLogger;
        $this->customerSession = $customerSession;
        $this->_messageManager = $messageManager;
        $this->storeManagerInterface = $storeManagerInterface;
    }

    public function canUseInternal()
    {
        return $this->getConfigData('active_moto');
    }

    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if (!class_exists(\Stripe\Stripe::class)) {
            return false;
        }
        return \Magento\Payment\Model\Method\AbstractMethod::isAvailable($quote);
    }

    public function validate()
    {
        return \Magento\Payment\Model\Method\AbstractMethod::validate();
    }

    public function isInitializeNeeded()
    {
        if ($this->_appState->getAreaCode() == 'adminhtml') {
            return false;
        } else {
            return true;
        }
    }

    public function hasVerification()
    {
        return true;
    }

    public function assignData(\Magento\Framework\DataObject $data)
    {
        $this->_debug("Function: assignData");
        $infoInstance = $this->getInfoInstance();
        $additionalData = $data->getData('additional_data');
        parent::assignData($data);

        $stripeResponse = isset($additionalData['stripe_response']) ? $additionalData['stripe_response'] : "";
        $response = json_decode($stripeResponse, true);
        $additonalCustomerId = isset($additionalData['customer_id'])?$additionalData['customer_id']:"";
        $customerId = $this->customerSession->getCustomerId()?$this->customerSession->getCustomerId():$additonalCustomerId;
        if ($response) {
            $thredDSecure = isset($response['card']['three_d_secure']) ? $response['card']['three_d_secure'] : "";
            $isSaveOption = isset($additionalData['saved']) ? $additionalData['saved'] : false;
            $sourceId = isset($response['id']) ? $response['id'] : false;
            $infoInstance->setAdditionalInformation('stripe_response', $stripeResponse);
            $infoInstance->setAdditionalInformation('three_d_secure', $thredDSecure);
            $infoInstance->setAdditionalInformation('source_id', $sourceId);
            $this->addPaymentInfoData($infoInstance, null, $customerId);
        } else {
            $cardID = isset($additionalData['cardId']) ? $additionalData['cardId'] : false;
            $sourceId = $this->addPaymentInfoData($infoInstance, $cardID, $customerId);
            $isSaveOption = "0";
        }
        $infoInstance->setAdditionalInformation('customer_id', $customerId);
        $infoInstance->setAdditionalInformation('save_option', $isSaveOption);
        $infoInstance->setAdditionalInformation('payment_token', $sourceId);
        $infoInstance->setAdditionalInformation('origin_source', $sourceId);
        $infoInstance->setAdditionalInformation("stripe_uid", uniqid());
        return $this;
    }

    public function addPaymentInfoData($infoInstance, $_cardID, $_customerId = null)
    {
        if (!$_cardID) {
            //get info data from stripe response obj
            $response = json_decode($infoInstance->getAdditionalInformation('stripe_response'), true);
            $cardData = isset($response['card']) ? $response['card'] : "";
        } else {
            //get info data from database
            $cardData = [];
            $cardModel = $this->_cardFactory->create()->load($_cardID);
            $customerId = $cardModel->getData('magento_customer_id');
            if (!$_customerId) {
                $_customerId = $this->customerSession->getCustomerId();
            }
            if ($customerId == $_customerId) {
                $infoInstance->setAdditionalInformation('three_d_secure', isset($cardData['three_d_secure'])?$cardData['three_d_secure']:"");
                $infoInstance->setAdditionalInformation('db_source', true);
                $_cardID = $cardModel->getData('card_id');
                $cardData = $cardModel->getData();
            }
        }

        $infoInstance->addData(
            [
                'cc_type' => isset($cardData['brand'])?$cardData['brand']:"",
                'cc_last_4' => isset($cardData['last4'])?$cardData['last4']:"",
                'cc_exp_month' => isset($cardData['exp_month'])?$cardData['exp_month']:"",
                'cc_exp_year' => isset($cardData['exp_year'])?$cardData['exp_year']:""
            ]
        );

        return $_cardID;
    }

    public function initialize($paymentAction, $stateObject)
    {
        try {
            /**
             * @var \Magento\Sales\Model\Order $order
             */
            $this->_helper->initStripeApi();
            $payment = $this->getInfoInstance();
            $payment->setAdditionalInformation(Constant::ADDITIONAL_PAYMENT_ACTION, $paymentAction);
            $order = $payment->getOrder();
            $this->_debug("-------Function: initialize orderid: " . $order->getIncrementId());
            $stateObject->setIsNotified($order->getCustomerNoteNotify());
            $amount = $order->getBaseGrandTotal();
            $threeDSecureAction = $this->_config->getThreedsecure();
            $threeDSecureVerify = $this->_config->getThreeDSecureVerify();
            $threeDSecureVerify = explode(",", $threeDSecureVerify);
            $forceThreeDSecure = $this->_config->getForceThreeDSecure();
            $threeDSecureStatus = $payment->getAdditionalInformation("three_d_secure");
            $orderState = \Magento\Sales\Model\Order::STATE_PROCESSING;
            $orderStatus = $this->getConfigData('order_status');
            $_saved = $payment->getAdditionalInformation('save_option');
            if ($_saved == "1") {
                if (($this->customerSession->isLoggedIn())) {
                    $stripeResponse = json_decode($payment->getAdditionalInformation('stripe_response'), true);
                    $this->_helper->saveCard($order->getCustomerId(), $stripeResponse);
                }
            }
            if ($forceThreeDSecure) {
                $this->perform3dSecure($payment, $amount);
            } else {
                if ($threeDSecureAction == 1) {
                    if (($threeDSecureStatus == "required") || (in_array($threeDSecureStatus, $threeDSecureVerify))) {
                        $this->perform3dSecure($payment, $amount);
                    } else {
                        $this->placeOrder($payment, $amount, $paymentAction, $stateObject);
                        $orderState = $order->getState() ? $order->getState() : $orderState;
                        $orderStatus = $order->getStatus() ? $order->getStatus() : $orderStatus;
                        $stateObject->setData('state', $orderState);
                        $stateObject->setData('status', $orderStatus);
                    }
                } else {
                    //not active
                    $this->placeOrder($payment, $amount, $paymentAction);
                    $orderState = $order->getState() ? $order->getState() : $orderState;
                    $orderStatus = $order->getStatus() ? $order->getStatus() : $orderStatus;
                    $stateObject->setData('state', $orderState);
                    $stateObject->setData('status', $orderStatus);
                }
            }
            return parent::initialize($paymentAction, $stateObject);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Function place order for non-3ds payment
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function placeOrder($payment, $amount, $action)
    {
        $this->_debug("Function: placeOrder");
        $payment->setAdditionalInformation(Constant::ADDITIONAL_THREEDS, "false");
        $order = $payment->getOrder();
        $totalDue = $order->getTotalDue();
        $baseTotalDue = $order->getBaseTotalDue();
        switch ($action) {
            case \Magento\Payment\Model\Method\AbstractMethod::ACTION_ORDER:
                break;
            case \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE:
                $payment->authorize(true, $baseTotalDue);
                // base amount will be set inside
                $payment->setAmountAuthorized($totalDue);
                break;
            case \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE_CAPTURE:
                $payment->setAmountAuthorized($totalDue);
                $payment->setBaseAmountAuthorized($baseTotalDue);
                $payment->capture(null);
                break;
            default:
                break;
        }
    }

    /**
     * Function order for 3d secure check
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function perform3dSecure(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $this->_helper->initStripeApi();
        $this->_debug("Function: perform3dSecure");
        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();
        $order->setCanSendNewEmailFlag(false);
        $cardSrc = $payment->getAdditionalInformation('payment_token');
        $returnUrl = $this->storeManagerInterface->getStore()->getBaseUrl() . "stripe/checkout_secure/response";
        $request = $this->_helper->getPaymentSource($order, "three_d_secure");
        $request = array_merge(
            $request,
            [
                "three_d_secure" => [
                    "card" => $cardSrc,
                ],
                "redirect" => [
                    "return_url" => $returnUrl
                ],
            ]
        );
        $source = \Stripe\Source::create($request);
        $this->_debug($source->getLastResponse()->json);
        if ($source->status == 'failed') {
            throw new LocalizedException(__("Cannot process 3D Secure"));
        }
        $clientSecret = $source->client_secret;
        $threeDSecureUrl = $source->redirect->url;
        //3d secure: true
        $payment->setAdditionalInformation(Constant::ADDITIONAL_THREEDS, "true");
        $payment->setAdditionalInformation("threed_secure_url", $threeDSecureUrl);
        $payment->setAdditionalInformation("client_secret", $clientSecret);
        $payment->setAdditionalInformation("stripe_source_id", $source->id);
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $this->_debug("Function: authorize");
        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();

        $paymentToken = $payment->getAdditionalInformation('payment_token');

        try {
            $this->_helper->initStripeApi();
            $dbSource = $payment->getAdditionalInformation('db_source');
            $customerId = $payment->getAdditionalInformation('customer_id');
            $_saved = $payment->getAdditionalInformation('save_option');
            $stripeCustomerId = null;
            if ($_saved == "1") {
                $stripeCustomerId = $this->_helper->getStripeCustomerId($customerId);
            }
            $request = $this->_helper->createChargeRequest($order, $amount, $paymentToken, false, $dbSource, $stripeCustomerId);
            $this->_debug($request);
            $uid = $payment->getAdditionalInformation("stripe_uid");
            $charge = \Stripe\Charge::create($request, [
                "idempotency_key" => $uid
            ]);
            $stripeAmount = $charge->amount;
            $this->_helper->checkTransaction($payment, $stripeAmount);
            $this->_debug($charge->getLastResponse()->json);
            $payment->setAmount($amount);
            $payment->setTransactionId($charge->id)
                ->setIsTransactionClosed(false)
                ->setShouldCloseParentTransaction(false)
                ->setCcTransId($charge->id);
            $payment->setAdditionalInformation("stripe_charge_id", $charge->id);
            $payment->setAdditionalInformation("stripe_source_id", $paymentToken);
            $order->setCanSendNewEmailFlag(true);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            if ($e->getStripeCode() == 'idempotency_key_in_use') {
                throw new \Magenest\StripePayment\Exception\StripePaymentDuplicateException(__($e->getMessage()));
            } else {
                throw new LocalizedException(__($e->getMessage()));
            }
        }

        return $this;
    }

    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        try {
            $this->_helper->initStripeApi();
            $this->_debug("Function: capture");
            /** @var \Magento\Sales\Model\Order $order */
            $order = $payment->getOrder();
            $chargeId = $payment->getAdditionalInformation("stripe_charge_id");
            if ($chargeId) {
                $charge = \Stripe\Charge::retrieve($chargeId);
                $request = $this->_helper->createCaptureRequest($order, $amount);
                $charge->capture($request);
                $this->_debug($charge->getLastResponse()->json);
                $transactionId = $charge->balance_transaction;
                $payment->setStatus(\Magento\Payment\Model\Method\AbstractMethod::STATUS_SUCCESS)
                    ->setShouldCloseParentTransaction(true)
                    ->setIsTransactionClosed(true)
                    ->setTransactionId($transactionId);
            } else {
                //call capture api
                $customerId = $payment->getAdditionalInformation('customer_id');
                $_saved = $payment->getAdditionalInformation('save_option');
                $stripeCustomerId = null;
                if ($_saved == "1") {
                    $stripeCustomerId = $this->_helper->getStripeCustomerId($customerId);
                }
                $paymentToken = $payment->getAdditionalInformation('payment_token');
                $dbSource = $payment->getAdditionalInformation('db_source');
                $request = $this->_helper->createChargeRequest($order, $amount, $paymentToken, true, $dbSource, $stripeCustomerId);
                $this->_debug($request);
                $uid = $payment->getAdditionalInformation("stripe_uid");
                $charge = \Stripe\Charge::create($request, [
                    "idempotency_key" => $uid
                ]);
                $stripeAmount = $charge->amount;
                $this->_helper->checkTransaction($payment, $stripeAmount);
                $this->_debug($charge->getLastResponse()->json);
                $transactionId = $charge->balance_transaction;
                $payment->setAmount($amount);
                $payment->setTransactionId($transactionId)
                    ->setIsTransactionClosed(false)
                    ->setShouldCloseParentTransaction(false)
                    ->setCcTransId($charge->id);
                $payment->setAdditionalInformation("stripe_charge_id", $charge->id);
                $payment->setAdditionalInformation("stripe_source_id", $paymentToken);
                $order->setCanSendNewEmailFlag(true);
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            if ($e->getStripeCode() == 'idempotency_key_in_use') {
                throw new \Magenest\StripePayment\Exception\StripePaymentDuplicateException(__($e->getMessage()));
            } else {
                throw new LocalizedException(__($e->getMessage()));
            }
        }
        return parent::capture($payment, $amount);
    }

    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $this->_debug("Function: refund");
        try {
            $this->_helper->initStripeApi();
            $refundReason = $this->request->getParam('refund_reason');
            /** @var \Magento\Sales\Model\Order $order */
            $order = $payment->getOrder();
            $_amount = $this->_helper->getPaymentAmount($order, $amount);
            $chargeId = $payment->getAdditionalInformation("stripe_charge_id");
            if ($chargeId) {
                $request = [
                    'charge' => $chargeId,
                    'amount' => round($_amount)
                ];
                if ($refundReason) {
                    $request['reason'] = $refundReason;
                }
                $refund = \Stripe\Refund::create($request);
                $this->_debug($refund->getLastResponse()->json);

                $transactionId = $refund->balance_transaction;
                $payment->setTransactionId($transactionId);
                $payment->setShouldCloseParentTransaction(0);
                $this->_messageManager->addSuccessMessage("Balance Transaction: " . $transactionId);
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Charge doesn\'t exist. Please try again later.')
                );
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this;
    }

    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        $this->_debug("Function: void");
        /** @var \Magento\Sales\Model\Order $order */
        try {
            $this->_helper->initStripeApi();
            $chargeId = $payment->getAdditionalInformation("stripe_charge_id");
            if ($chargeId) {
                $request = [
                    'charge' => $chargeId
                ];
                $refund = \Stripe\Refund::create($request);
                $this->_debug($refund->getLastResponse()->json);
                $payment->setShouldCloseParentTransaction(1);
                $payment->setIsTransactionClosed(1);
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Charge doesn\'t exist. Please try again later.')
                );
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this;
    }

    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        $this->_debug("Function: cancel");
        /** @var \Magento\Sales\Model\Order $order */
        try {
            $this->_helper->initStripeApi();
            $chargeId = $payment->getAdditionalInformation("stripe_charge_id");
            if ($chargeId) {
                $request = [
                    'charge' => $chargeId
                ];

                $refund = \Stripe\Refund::create($request);
                $this->_debug($refund->getLastResponse()->json);
                $payment->setShouldCloseParentTransaction(1);
                $payment->setIsTransactionClosed(1);
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Charge doesn\'t exist. Please try again later.')
                );
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this;
    }

    /**
     * @param array|string $debugData
     */
    protected function _debug($debugData)
    {
        $this->stripeLogger->debug(var_export($debugData, true));
    }
}
