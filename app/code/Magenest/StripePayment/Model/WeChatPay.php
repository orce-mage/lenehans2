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

class WeChatPay extends AbstractMethod
{
    const CODE = 'magenest_stripe_wechatpay';
    protected $_code = self::CODE;

    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = false;
    protected $_canCaptureOnce = true;
    protected $_canVoid = false;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_isInitializeNeeded = true;
    protected $_canOrder = false;
    protected $_messageManager;
    protected $stripeHelper;
    protected $stripeLogger;
    protected $stripeConfig;
    protected $request;
    protected $storeManager;
    protected $sourceFactory;

    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magenest\StripePayment\Helper\Config $stripeConfig,
        \Magenest\StripePayment\Helper\Data $stripeHelper,
        \Magenest\StripePayment\Helper\Logger $stripeLogger,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magenest\StripePayment\Model\SourceFactory $sourceFactory,
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
        $this->_messageManager = $messageManager;
        $this->stripeHelper = $stripeHelper;
        $this->stripeLogger = $stripeLogger;
        $this->stripeConfig = $stripeConfig;
        $this->request = $request;
        $this->storeManager = $storeManagerInterface;
        $this->sourceFactory = $sourceFactory;
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
        return \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE;
    }

    public function assignData(\Magento\Framework\DataObject $data)
    {
        $this->_debug("Function: assignData");
        $infoInstance = $this->getInfoInstance();
        $additionalData = $data->getData('additional_data');
        parent::assignData($data);

        $stripeResponse = isset($additionalData['stripe_response']) ? $additionalData['stripe_response'] : "";
        $response = json_decode($stripeResponse, true);
        if ($response) {
            $sourceId = isset($response['id']) ? $response['id'] : false;
            $infoInstance->setAdditionalInformation('stripe_response', $stripeResponse);
            $infoInstance->setAdditionalInformation('stripe_source_id', $sourceId);
        }
        $infoInstance->setAdditionalInformation("stripe_uid", uniqid());
        return $this;
    }

    public function initialize($paymentAction, $stateObject)
    {
        try {
            $payment = $this->getInfoInstance();
            $order = $payment->getOrder();
            $quoteId = $order->getQuoteId();
            $sourceId = $payment->getAdditionalInformation("stripe_source_id");
            $sourceModel = $this->sourceFactory->create();
            $sourceModel->setData("source_id", $sourceId);
            $sourceModel->setData("method", $payment->getMethod());
            $sourceModel->setData("quote_id", $quoteId);
            $sourceModel->isObjectNew(true);
            $sourceModel->save();
            return parent::initialize($paymentAction, $stateObject);
        } catch (\Exception $e) {
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
        return ['aud', 'cad', 'eur', 'gbp', 'hkd', 'jpy', 'sgd', 'usd'];
    }
}
