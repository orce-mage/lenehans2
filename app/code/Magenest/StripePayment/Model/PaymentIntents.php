<?php
namespace Magenest\StripePayment\Model;

use Magenest\StripePayment\Exception\StripePaymentException;
use Magenest\StripePayment\Helper\Config as ConfigHelper;
use Magenest\StripePayment\Helper\Data as DataHelper;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Framework\Exception\LocalizedException;

class PaymentIntents extends AbstractMethod
{
    const CODE                       = 'magenest_stripe_paymentintents';

    protected $_code                        = self::CODE;

    protected $_stripe;

    protected $_isGateway = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canCaptureOnce = true;
    protected $_canAuthorize = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canUseInternal = false;
    protected $_canVoid = true;
    public $_config;
    protected $customerSession;
    protected $_helper;

    protected $stripeHelper;
    protected $stripeLogger;
    protected $stripeConfig;
    protected $request;

    public function __construct(
        \Magenest\StripePayment\Helper\Config $stripeConfig,
        \Magenest\StripePayment\Helper\Data $stripeHelper,
        \Magenest\StripePayment\Helper\Logger $stripeLogger,
        \Magento\Framework\App\RequestInterface $request,
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
        $this->stripeHelper = $stripeHelper;
        $this->stripeLogger = $stripeLogger;
        $this->stripeConfig = $stripeConfig;
        $this->request = $request;
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
    }

    public function assignData(\Magento\Framework\DataObject $data)
    {
        $infoInstance = $this->getInfoInstance();
        $additionalData = $data->getData('additional_data');
        $sourceId = isset($additionalData['source_id']) ? $additionalData['source_id'] : false;
        $infoInstance->setAdditionalInformation('source_id', $sourceId);
        return parent::assignData($data);
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        try {
            $order = $payment->getOrder();
            $this->stripeHelper->initStripeApi();
            $intentId = $payment->getAdditionalInformation('source_id');
            if ($intentId) {
                $intent = \Stripe\PaymentIntent::retrieve($intentId);
                $this->_debug($intent->getLastResponse()->json);
                $this->updatePaymentIntent($intentId, $order);
                $charges = \Stripe\Charge::all([
                    'payment_intent' => $intentId,
                    'limit' => 3,
                ]);
                $chargeFlag = false;
                foreach ($charges as $charge) {
                    $chargeStatus = $charge->status;
                    if ($chargeStatus == 'succeeded') {
                        $chargeId = $charge->id;
                        $payment->setAdditionalInformation("stripe_charge_id", $chargeId);
                        $payment->setTransactionId($chargeId)
                            ->setLastTransId($chargeId);
                        $payment->setIsTransactionClosed(0);
                        $payment->setShouldCloseParentTransaction(0);
                        $chargeFlag = true;
                        $stripeAmount = $charge->amount;
                    }
                }
                $this->stripeHelper->checkTransaction($payment, $stripeAmount);
                if (!$chargeFlag) {
                    throw new LocalizedException(
                        __("Payment failed")
                    );
                }
                return parent::authorize($payment, $amount);
            } else {
                throw new LocalizedException(
                    __("Transaction doesn't existed.")
                );
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        try {
            $order = $payment->getOrder();
            $this->stripeHelper->initStripeApi();
            $intentId = $payment->getAdditionalInformation("source_id");
            if ($intentId) {
                $intent = \Stripe\PaymentIntent::retrieve($intentId);
                $this->_debug($intent->getLastResponse()->json);
                $this->updatePaymentIntent($intentId, $order);
                if ($intent->capture_method == 'manual') {
                    $amount = $this->stripeHelper->getPaymentAmount($order, $amount);
                    $intent->capture(['amount_to_capture' => $amount]);
                    $transactionId = $intent->charges->data[0]->balance_transaction;
                    $payment->setTransactionId($transactionId)
                        ->setIsTransactionClosed(0)
                        ->setShouldCloseParentTransaction(0);
                } else {
                    $charges = \Stripe\Charge::all([
                        'payment_intent' => $intentId,
                        'limit' => 3,
                    ]);

                    $chargeFlag = false;
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
                    }
                    $this->stripeHelper->checkTransaction($payment, $stripeAmount);
                    if (!$chargeFlag) {
                        throw new LocalizedException(
                            __("Payment failed")
                        );
                    }
                }
                return parent::capture($payment, $amount);
            } else {
                throw new LocalizedException(
                    __("Transaction doesn't existed.")
                );
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        try {
            $this->stripeHelper->initStripeApi();
            $chargeId = $payment->getAdditionalInformation("stripe_charge_id");
            $refundReason = $this->request->getParam('refund_reason');
            $request = $this->stripeHelper->createRefundRequest($payment, $chargeId, $amount);
            if ($refundReason) {
                $request['reason'] = $refundReason;
            }
            $refund = \Stripe\Refund::create($request);
            $this->_debug($refund->getLastResponse()->json);
            $transactionId = $refund->balance_transaction;
            if ($transactionId) {
                $payment->setTransactionId($transactionId);
            }
            $payment->setShouldCloseParentTransaction(0);
            return parent::refund($payment, $amount);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        try {
            $order = $payment->getOrder();
            $this->stripeHelper->initStripeApi();
            $chargeId = $payment->getAdditionalInformation("source_id");
            $intent = \Stripe\PaymentIntent::retrieve($chargeId);
            $intent->cancel();
            $payment->setShouldCloseParentTransaction(1);
            $payment->setIsTransactionClosed(1);
            return parent::void($payment);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        try {
            $order = $payment->getOrder();
            $this->stripeHelper->initStripeApi();
            $chargeId = $payment->getAdditionalInformation("source_id");
            $intent = \Stripe\PaymentIntent::retrieve($chargeId);
            $intent->cancel();
            $payment->setShouldCloseParentTransaction(1);
            $payment->setIsTransactionClosed(1);
            return parent::cancel($payment);
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

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    private function updatePaymentIntent($intentId, $order)
    {
        $dataUpdate = [
            'description' => $this->stripeHelper->getPaymentDescription($order),
            'metadata' => $this->stripeHelper->getPaymentMetaData($order),
        ];
        if ($this->stripeConfig->sendMailCustomer()) {
            $dataUpdate['receipt_email'] = $order->getCustomerEmail();
        }
        if ($order->getIsNotVirtual()) {
            $dataUpdate['shipping'] = $this->stripeHelper->getShippingInformation($order);
        }
        \Stripe\PaymentIntent::update(
            $intentId,
            $dataUpdate
        );
    }
}
