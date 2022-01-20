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
use Stripe\Stripe;

class StripeDirectApi extends \Magento\Payment\Model\Method\Cc
{
    const CODE = 'magenest_stripe';
    protected $_code = self::CODE;
    protected $_isGateway = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = true;
    protected $_canAuthorize = true;

    protected $_helper;
    protected $stripeLogger;
    protected $stripeCard;
    protected $stripeConfig;

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
        \Magenest\StripePayment\Helper\Logger $stripeLogger,
        \Magenest\StripePayment\Model\StripePaymentMethod $stripePaymentMethod,
        \Magenest\StripePayment\Helper\Config $stripeConfig,
        $data = []
    ) {
        $this->_helper = $dataHelper;
        $this->stripeLogger = $stripeLogger;
        $this->stripeCard = $stripePaymentMethod;
        $this->stripeConfig = $stripeConfig;
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
    }

    public function assignData(\Magento\Framework\DataObject $data)
    {
        $infoInstance = $this->getInfoInstance();
        $additionalData = $data->getData('additional_data');
        parent::assignData($data);
        if ($this->_appState->getAreaCode() == 'adminhtml') {
            $stripeResponse = isset($additionalData['stripe_response']) ? $additionalData['stripe_response'] : "";
            $response = json_decode($stripeResponse, true);
            $customerId = isset($additionalData['customer_id'])?$additionalData['customer_id']:"";
            $cardId = isset($additionalData['cardId'])?$additionalData['cardId']:"";
            if ($response) {
                $sourceId = isset($response['id']) ? $response['id'] : false;
            } else {
                $sourceId = $this->stripeCard->addPaymentInfoData($this->getInfoInstance(), $cardId, $customerId);
            }
            $customerId = isset($additionalData['customer_id'])?$additionalData['customer_id']:"";
            $infoInstance->setAdditionalInformation('source_id', $sourceId);
            $infoInstance->setAdditionalInformation('customer_id', $customerId);
            if ($sourceId) {
                $infoInstance->setAdditionalInformation('db_source', true);
            }
            return $this;
        }

        return $this;
    }

    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        try {
            $this->_helper->initStripeApi();
            $this->_debug("authorize action");
            /** @var \Magento\Sales\Model\Order $order */
            $order = $payment->getOrder();
            $paymentToken = $this->_helper->getDirectSource($order);
            $dbSource = $payment->getAdditionalInformation('db_source');
            $request = $this->_helper->createChargeRequest(
                $order,
                $amount,
                $paymentToken,
                false,
                $dbSource,
                null
            );
            $charge = \Stripe\Charge::create($request);
            $stripeAmount = $charge->amount;
            $this->_helper->checkTransaction($payment, $stripeAmount);
            $this->_debug($charge->getLastResponse()->json);
            $payment->setTransactionId($charge->id)
                ->setIsTransactionClosed(false)
                ->setShouldCloseParentTransaction(false)
                ->setCcTransId($charge->id);
            $payment->setAdditionalInformation("stripe_charge_id", $charge->id);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        try {
            $this->_helper->initStripeApi();
            $this->_debug("capture action");
            /** @var \Magento\Sales\Model\Order $order */
            $order = $payment->getOrder();
            $chargeId = $payment->getAdditionalInformation("stripe_charge_id");
            if ($chargeId) {
                $charge = \Stripe\Charge::retrieve($chargeId);
                $request = $this->_helper->createCaptureRequest($order, $amount);
                $charge->capture($request);
                $transactionId = $charge->balance_transaction;
                $payment->setStatus(\Magento\Payment\Model\Method\AbstractMethod::STATUS_SUCCESS)
                    ->setShouldCloseParentTransaction(true)
                    ->setIsTransactionClosed(true)
                    ->setTransactionId($transactionId);
            } else {
                $paymentToken = $this->_helper->getDirectSource($order);
                $dbSource = $payment->getAdditionalInformation('db_source');
                $request = $this->_helper->createChargeRequest(
                    $order,
                    $amount,
                    $paymentToken,
                    true,
                    $dbSource,
                    null
                );
                $charge = \Stripe\Charge::create($request);
                $stripeAmount = $charge->amount;
                $this->_helper->checkTransaction($payment, $stripeAmount);
                $payment->setTransactionId($charge->balance_transaction)
                    ->setIsTransactionClosed(false)
                    ->setShouldCloseParentTransaction(false)
                    ->setCcTransId($charge->id);
                $this->_debug($charge->getLastResponse()->json);
                $payment->setAdditionalInformation("stripe_charge_id", $charge->id);
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

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

    /**
     * @param array|string $debugData
     */
    protected function _debug($debugData)
    {
        $this->stripeLogger->debug(var_export($debugData, true));
    }

    public function validate()
    {
        return \Magento\Payment\Model\Method\AbstractMethod::validate();
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

    public function hasVerification()
    {
        return true;
    }
}
