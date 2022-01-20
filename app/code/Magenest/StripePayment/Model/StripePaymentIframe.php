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
use Magento\Payment\Model\Method\AbstractMethod;

class StripePaymentIframe extends AbstractMethod
{
    const CODE = 'magenest_stripe_iframe';
    protected $_code = self::CODE;

    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canCaptureOnce = true;
    protected $_canVoid = true;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_isInitializeNeeded = true;
    protected $_canOrder = false;

    protected $stripeCard;
    protected $_helper;
    public $_config;
    public $stripeLogger;

    public function __construct(
        \Magenest\StripePayment\Helper\Data $dataHelper,
        StripePaymentMethod $stripePaymentMethod,
        \Magenest\StripePayment\Helper\Logger $stripeLogger,
        \Magenest\StripePayment\Helper\Config $config,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_helper = $dataHelper;
        $this->stripeCard = $stripePaymentMethod;
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
        $this->_config = $config;
        $this->stripeLogger = $stripeLogger;
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

    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);
        $additionalData = $data->getData('additional_data');
        $stripeResponse = isset($additionalData['stripe_response'])?$additionalData['stripe_response']:"";
        $response = json_decode($stripeResponse, true);
        $infoInstance = $this->getInfoInstance();
        if ($response) {
            $thredDSecure = isset($response['card']['three_d_secure'])?$response['card']['three_d_secure']:"";
            $sourceId = isset($response['id']) ? $response['id'] : false;
            $payType = isset($response['type']) ? $response['type'] : "";
            if ($payType != 'card') {
                throw new LocalizedException(
                    __("Operation not allowed")
                );
            }
            $infoInstance->setAdditionalInformation('stripe_response', $stripeResponse);
            $infoInstance->setAdditionalInformation('three_d_secure', $thredDSecure);
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong. Please try again later.')
            );
        }
        $infoInstance->setAdditionalInformation('payment_token', $sourceId);
        $infoInstance->setAdditionalInformation("stripe_uid", uniqid());
        return $this;
    }

    public function initialize($paymentAction, $stateObject)
    {
        try {
            /**
             * @var \Magento\Sales\Model\Order $order
             */
            $payment = $this->getInfoInstance();
            $payment->setAdditionalInformation(Constant::ADDITIONAL_PAYMENT_ACTION, $paymentAction);
            $order = $payment->getOrder();
            $this->_debug("-------Function: initialize: orderid: " . $order->getIncrementId());
            $stateObject->setIsNotified($order->getCustomerNoteNotify());
            $amount = $order->getBaseGrandTotal();
            $threeDSecureAction = $this->_config->getThreedsecure();
            $threeDSecureVerify = $this->_config->getThreeDSecureVerify();
            $threeDSecureVerify = explode(",", $threeDSecureVerify);
            $threeDSecureStatus = $payment->getAdditionalInformation("three_d_secure");
            $orderState = \Magento\Sales\Model\Order::STATE_PROCESSING;
            $orderStatus = $this->getConfigData('order_status');
            //active 3d secure
            if ($threeDSecureAction == 1) {
                if (($threeDSecureStatus == "required") || (in_array($threeDSecureStatus, $threeDSecureVerify))) {
                    $this->stripeCard->perform3dSecure($payment, $amount);
                } else {
                    $this->stripeCard->placeOrder($payment, $amount, $paymentAction);
                    $orderState = $order->getState() ? $order->getState() : $orderState;
                    $orderStatus = $order->getStatus() ? $order->getStatus() : $orderStatus;
                    $stateObject->setData('state', $orderState);
                    $stateObject->setData('status', $orderStatus);
                }
            } else {
                $this->stripeCard->placeOrder($payment, $amount, $paymentAction);
                $orderState = $order->getState() ? $order->getState() : $orderState;
                $orderStatus = $order->getStatus() ? $order->getStatus() : $orderStatus;
                $stateObject->setData('state', $orderState);
                $stateObject->setData('status', $orderStatus);
            }

            return parent::initialize($paymentAction, $stateObject);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this->stripeCard->authorize($payment, $amount);
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this->stripeCard->capture($payment, $amount);
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this->stripeCard->refund($payment, $amount);
    }

    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        return $this->stripeCard->void($payment);
    }

    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        return $this->stripeCard->cancel($payment);
    }

    protected function _debug($debugData)
    {
        $this->stripeLogger->debug(var_export($debugData, true));
    }
}
