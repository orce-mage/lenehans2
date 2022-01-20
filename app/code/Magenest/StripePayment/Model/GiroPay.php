<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Model;

use Magenest\StripePayment\Helper\Constant;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Framework\Exception\LocalizedException;
use Stripe;

class GiroPay extends AbstractMethod
{
    const CODE = 'magenest_stripe_giropay';
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

    public function getConfigPaymentAction()
    {
        return parent::ACTION_AUTHORIZE_CAPTURE;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return AbstractMethod
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        try {
            $this->stripeHelper->initStripeApi();
            $order = $payment->getOrder();
            $sourceId = $payment->getAdditionalInformation("stripe_source_id");
            $chargeRequest = $this->stripeHelper->createChargeRequest($order, $amount, $sourceId);
            $uid = $payment->getAdditionalInformation("stripe_uid");
            $charge = \Stripe\Charge::create($chargeRequest, [
                "idempotency_key" => $uid
            ]);
            $stripeAmount = $charge->amount;
            $this->stripeHelper->checkTransaction($payment, $stripeAmount);
            $this->_debug($charge->getLastResponse()->json);
            $chargeId = $charge->id;
            $payment->setAdditionalInformation("stripe_charge_id", $chargeId);
            $chargeStatus = $charge->status;
            if ($chargeStatus == 'succeeded') {
                $transactionId = $charge->balance_transaction;
                $payment->setTransactionId($transactionId)
                    ->setLastTransId($transactionId);
                $payment->setIsTransactionClosed(1);
                $payment->setShouldCloseParentTransaction(1);
            } else {
                throw new LocalizedException(
                    __("Payment failed")
                );
            }
            return parent::capture($payment, $amount);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            if ($e->getStripeCode() == 'idempotency_key_in_use') {
                throw new \Magenest\StripePayment\Exception\StripePaymentDuplicateException(__($e->getMessage()));
            } else {
                throw new LocalizedException(__($e->getMessage()));
            }
        }
    }

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

    /**
     * @param array|string $debugData
     */
    protected function _debug($debugData)
    {
        $this->stripeLogger->debug(var_export($debugData, true));
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
}
