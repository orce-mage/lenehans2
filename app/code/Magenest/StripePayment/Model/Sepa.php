<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Model;

use Magenest\StripePayment\Helper\Constant;
use Magenest\StripePayment\Exception\StripePaymentException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\Method\AbstractMethod;
use Stripe;

class Sepa extends AbstractMethod
{
    const CODE = 'magenest_stripe_sepa';
    protected $_code = self::CODE;

    protected $_isGateway = true;
    protected $_canAuthorize = false;
    protected $_canCapture = true;
    protected $_canCapturePartial = false;
    protected $_canCaptureOnce = true;
    protected $_canVoid = false;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_isInitializeNeeded = true;
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
        parent::assignData($data);
        $additionalData = $data->getData('additional_data');
        $stripeResponse = isset($additionalData['stripe_response'])?$additionalData['stripe_response']:"";
        $response = json_decode($stripeResponse, true);
        $infoInstance = $this->getInfoInstance();
        if ($response) {
            $infoInstance->setAdditionalInformation('stripe_response', $stripeResponse);
        }
        return $this;
    }

    public function initialize($paymentAction, $stateObject)
    {
        try {
            $this->stripeHelper->initStripeApi();
            $payment = $this->getInfoInstance();
            $order = $payment->getOrder();
            $sourceId = $payment->getAdditionalInformation("stripe_source_id");
            $stripeResponseJson = $payment->getAdditionalInformation('stripe_response');
            $this->setSepaAdditionalInformation($payment, $stripeResponseJson);
            $amount = $order->getBaseGrandTotal();
            $chargeRequest = $this->stripeHelper->createChargeRequest($order, $amount, $sourceId);
            $charge = Stripe\Charge::create($chargeRequest);
            $this->_debug($charge->getLastResponse()->json);
            $chargeId = $charge->id;
            $payment->setAdditionalInformation("stripe_charge_id", $chargeId);
            $chargeStatus = $charge->status;
            $totalDue = $order->getTotalDue();
            $baseTotalDue = $order->getBaseTotalDue();
            $stateObject->setData('state', \Magento\Sales\Model\Order::STATE_PROCESSING);
            if ($chargeStatus == 'pending') {
                $order->setCanSendNewEmailFlag(false);
            }
            if ($chargeStatus == 'succeeded') {
                $transactionId = $charge->balance_transaction;
                $payment->setTransactionId($transactionId)
                    ->setLastTransId($transactionId);
                $payment->setAmountAuthorized($totalDue);
                $payment->setBaseAmountAuthorized($baseTotalDue);
                $payment->capture(null);
            }
            if ($chargeStatus == 'failed') {
                throw new StripePaymentException(
                    __("Payment failed")
                );
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
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        try {
            $this->stripeHelper->initStripeApi();
            $order = $payment->getOrder();
            $chargeId = $payment->getAdditionalInformation("stripe_charge_id");
            if (!$chargeId) {
                throw new LocalizedException(
                    __("Charge doesn't exist. Please try again later.")
                );
            } else {
                $charge = Stripe\Charge::retrieve($chargeId);
            }
            $stripeAmount = $charge->amount;
            $this->stripeHelper->checkTransaction($payment, $stripeAmount);
            $chargeStatus = $charge->status;
            if ($chargeStatus == 'pending') {
                throw new LocalizedException(
                    __("Payment is pending. Cannot capture this payment")
                );
            }
            if ($chargeStatus == 'succeeded') {
                $order->setCanSendNewEmailFlag(true);
                $transactionId = $charge->balance_transaction;
                $payment->setTransactionId($transactionId)
                    ->setLastTransId($transactionId);
                $payment->setIsTransactionClosed(1);
                $payment->setShouldCloseParentTransaction(1);
            }
            if ($chargeStatus == 'failed') {
                throw new LocalizedException(
                    __("Payment failed")
                );
            }

            return parent::capture($payment, $amount);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            if ($e->getStripeCode() == 'idempotency_key_in_use') {
                throw new \Magenest\StripePayment\Exception\StripePaymentDuplicateException($e->getMessage());
            } else {
                throw new LocalizedException(__($e->getMessage()));
            }
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
            $refund = Stripe\Refund::create($request);
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

    public function getConfigPaymentAction()
    {
        return "authorize_capture";
    }

    public function canUseForCurrency($currencyCode)
    {
        if (!in_array(strtolower($currencyCode), $this->getAcceptedCurrencyCodes())) {
            return false;
        }
        return true;
    }

    private function getAcceptedCurrencyCodes()
    {
        return ['eur'];
    }

    /**
     * @param array|string $debugData
     */
    protected function _debug($debugData)
    {
        $this->stripeLogger->debug(var_export($debugData, true));
    }

    protected function setSepaAdditionalInformation($payment, $stripeResponseJson)
    {
        $stripeResponse = json_decode($stripeResponseJson, true);
        $sourceAdditionalInformation = [];
        $sourceAdditionalInformation[] = [
            'label' => "Payment Method",
            'value' => "SEPA Direct Debit"
        ];
        $sourceAdditionalInformation[] = [
            'label' => "Back Code",
            'value' => isset($stripeResponse['sepa_debit']['bank_code'])?$stripeResponse['sepa_debit']['bank_code']:""
        ];
        $sourceAdditionalInformation[] = [
            'label' => "Branch Code",
            'value' => isset($stripeResponse['sepa_debit']['branch_code'])?$stripeResponse['sepa_debit']['branch_code']:""
        ];
        $sourceAdditionalInformation[] = [
            'label' => "Country",
            'value' => isset($stripeResponse['sepa_debit']['country'])?$stripeResponse['sepa_debit']['country']:""
        ];
        $sourceAdditionalInformation[] = [
            'label' => "Fingerprint",
            'value' => isset($stripeResponse['sepa_debit']['fingerprint'])?$stripeResponse['sepa_debit']['fingerprint']:""
        ];
        $sourceAdditionalInformation[] = [
            'label' => "Last 4",
            'value' => isset($stripeResponse['sepa_debit']['last4'])?$stripeResponse['sepa_debit']['last4']:""
        ];
        $sourceAdditionalInformation[] = [
            'label' => "Mandate Reference",
            'value' => isset($stripeResponse['sepa_debit']['mandate_reference'])?$stripeResponse['sepa_debit']['mandate_reference']:""
        ];
        $sourceAdditionalInformation[] = [
            'label' => "Mandate Url",
            'value' => isset($stripeResponse['sepa_debit']['mandate_url'])?$stripeResponse['sepa_debit']['mandate_url']:""
        ];
        $payment->setAdditionalInformation("stripe_source_additional_information", json_encode($sourceAdditionalInformation));
        $payment->setAdditionalInformation("stripe_sepa_mandate_reference", isset($stripeResponse['sepa_debit']['mandate_reference'])?$stripeResponse['sepa_debit']['mandate_reference']:"");
        $payment->setAdditionalInformation("stripe_sepa_mandate_url", isset($stripeResponse['sepa_debit']['mandate_url'])?$stripeResponse['sepa_debit']['mandate_url']:"");
    }
}
