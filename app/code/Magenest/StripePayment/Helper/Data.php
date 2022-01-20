<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Helper;

use Magenest\StripePayment\Exception\StripePaymentException;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magenest\StripePayment\Model\CustomerFactory;
use Magento\Framework\Exception\LocalizedException;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_encryptor;

    protected $_httpClientFactory;

    protected $_customerFactory;

    protected $_config;

    protected $_cardFactory;

    protected $_chargeFactory;

    protected $stripeLogger;

    protected $customerSession;

    protected $sourceFactory;

    protected $orderFactory;

    protected $objectManager;

    protected $orderRepository;

    protected $orderSender;

    public function __construct(
        Context $context,
        EncryptorInterface $encryptorInterface,
        ZendClientFactory $clientFactory,
        CustomerFactory $customerFactory,
        Config $config,
        \Magenest\StripePayment\Model\CardFactory $cardFactory,
        \Magenest\StripePayment\Model\ChargeFactory $chargeFactory,
        \Magenest\StripePayment\Helper\Logger $stripeLogger,
        \Magento\Customer\Model\Session $customerSession,
        \Magenest\StripePayment\Model\SourceFactory $sourceFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
    ) {
        $this->_encryptor = $encryptorInterface;
        $this->_httpClientFactory = $clientFactory;
        $this->_customerFactory = $customerFactory;
        $this->_config = $config;
        parent::__construct($context);
        $this->_cardFactory = $cardFactory;
        $this->_chargeFactory = $chargeFactory;
        $this->stripeLogger = $stripeLogger;
        $this->customerSession = $customerSession;
        $this->sourceFactory = $sourceFactory;
        $this->orderFactory = $orderFactory;
        $this->objectManager = ObjectManager::getInstance();
        $this->orderRepository = $orderRepository;
        $this->orderSender = $orderSender;
    }

    public function sendRequest($requestPost, $url, $requestMethod = null, $key = null)
    {
        if (!$requestMethod) {
            $requestMethod = "post";
        }
        if (!$key) {
            $key = $this->_config->getSecretKey();
        }
        $httpHeaders = new \Zend\Http\Headers();
        $httpHeaders->addHeaders([
            'Authorization' => 'Bearer ' . $key,
        ]);
        $request = new \Zend\Http\Request();
        $request->setHeaders($httpHeaders);
        $request->setUri($url);
        $request->setMethod(strtoupper($requestMethod));

        if (!!$requestPost) {
            $request->getPost()->fromArray($requestPost);
        }

        $client = new \Zend\Http\Client();
        $options = [
            'adapter' => 'Zend\Http\Client\Adapter\Curl',
            'curloptions' => [CURLOPT_FOLLOWLOCATION => true],
            'maxredirects' => 0,
            'timeout' => 30
        ];
        $client->setOptions($options);
        try {
            $response = $client->send($request);
            $responseBody = $response->getBody();
            $responseBody = json_decode($responseBody, true);

            return $responseBody;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Cannot send request to Stripe servers.')
            );
        }
    }

    public function checkStripeCustomerId($cusId)
    {
        $url = 'https://api.stripe.com/v1/customers/' . $cusId;
        $request = $this->sendRequest([], $url, null);
        if (isset($request['error'])) {
            return false;
        }
        return true;
    }

    public function isZeroDecimal($currency)
    {
        return in_array(strtolower($currency), [
            'bif',
            'djf',
            'jpy',
            'krw',
            'pyg',
            'vnd',
            'xaf',
            'xpf',
            'clp',
            'gnf',
            'kmf',
            'mga',
            'rwf',
            'vuv',
            'xof',
            'ugx'
        ]);
    }

    /**
     * @param string $customerId
     * @param $stripeResponse
     */
    public function saveCard($customerId, $stripeResponse)
    {
        try {
            $cardData = isset($stripeResponse['card']) ? $stripeResponse['card'] : [];
            $expMonth = isset($cardData['exp_month']) ? $cardData['exp_month'] : "";
            $expYear = isset($cardData['exp_year']) ? $cardData['exp_year'] : "";
            $brand = isset($cardData['brand']) ? $cardData['brand'] : "";
            $cardLast4 = isset($cardData['last4']) ? $cardData['last4'] : "";
            $sourceId = isset($stripeResponse['id']) ? $stripeResponse['id'] : "";
            $threeDSecureStatus = isset($cardData['three_d_secure']) ? $cardData['three_d_secure'] : "";
            $cardModel = $this->_cardFactory->create();
            $data = [
                'magento_customer_id' => $customerId,
                'card_id' => $sourceId,
                'brand' => $brand,
                'last4' => (string)$cardLast4,
                'exp_month' => (string)$expMonth,
                'exp_year' => (string)$expYear,
                'status' => "active",
                'three_d_secure' => $threeDSecureStatus
            ];

            $stripeCustomerId = $this->getStripeCustomerId();
            if ($stripeCustomerId) {
                if (!$this->checkStripeCustomerId($stripeCustomerId)) {
                    $this->deleteStripeCustomerId($stripeCustomerId);
                    $stripeCustomerId = $this->createCustomer($sourceId);
                } else {
                    $res = $this->addSourceToCustomer($stripeCustomerId, $sourceId);
                }
            } else {
                $stripeCustomerId = $this->createCustomer($sourceId);
            }

            if ($stripeCustomerId) {
                $cardModel->addData($data)->save();
            }
            return $stripeCustomerId;
        } catch (\Exception $e) {
            $this->stripeLogger->critical("save card exception" . $e->getMessage());
            return false;
        }
    }

    public function saveCardBeforePayment($customerId, $payment)
    {
        try {
//            $cardData = isset($stripeResponse['card'])?year$stripeResponse['card']:[];
            $expMonth = $payment->getData('cc_exp_month');
            $expYear = $payment->getData('cc_exp_month');
            $brand = $payment->getData('cc_type');
            $cardLast4 = $payment->getData('cc_last_4');
            $sourceId = $payment->getAdditionalInformation()['source_id'];
            $threeDSecureStatus = $payment->getAdditionalInformation()['three_d_secure'];
            $cardModel = $this->_cardFactory->create();
            $data = [
                'magento_customer_id' => $customerId,
                'card_id' => $sourceId,
                'brand' => $brand,
                'last4' => (string)$cardLast4,
                'exp_month' => (string)$expMonth,
                'exp_year' => (string)$expYear,
                'status' => "active",
                'three_d_secure' => $threeDSecureStatus
            ];

            $stripeCustomerId = $this->getStripeCustomerId();
            if ($stripeCustomerId) {
                if (!$this->checkStripeCustomerId($stripeCustomerId)) {
                    $this->deleteStripeCustomerId($stripeCustomerId);
                    $stripeCustomerId = $this->createCustomer($sourceId);
                } else {
                    $res = $this->addSourceToCustomer($stripeCustomerId, $sourceId);
                }
            } else {
                $this->deleteStripeCustomerId($stripeCustomerId);
                $stripeCustomerId = $this->createCustomer($sourceId);
            }

            if ($stripeCustomerId) {
                $cardModel->addData($data)->save();
            }
            return $stripeCustomerId;
        } catch (\Exception $e) {
            $this->stripeLogger->critical("Save card exception" . $e->getMessage());
            return false;
        }
    }

    public function deleteCard($customerId, $cardId)
    {

        $url = "https://api.stripe.com/v1/customers/" . $customerId . "/sources/" . $cardId;
        return $this->sendRequest([], $url, "delete");
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param $response
     */
    public function saveCharge($order, $response, $status)
    {
        $chargeModel = $this->_chargeFactory->create();
        $data = [
            'charge_id' => isset($response['id'])?$response['id']:'',
            'order_id' => $order->getIncrementId(),
            'customer_id' => $order->getCustomerId(),
            'status' => $status
        ];

        $chargeModel->addData($data)->save();
    }

    /**
     * Create a stripe customer object
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param $token
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createCustomer($source = null)
    {
        try {
            $customerModel = $this->_customerFactory->create();
            /** @var \Magento\Customer\Model\Session $customerSession */
            $customerSession = $this->objectManager->create('\Magento\Customer\Model\Session');
            $customer = $customerSession->getCustomer();
            $this->initStripeApi();

            $request = [
                "description" => $customer->getName(),
                "email" => $customer->getEmail(),
                "metadata" => [
                    "id" => $customer->getId(),
                    "name" => $customer->getName(),
                    "email" => $customer->getEmail()
                ]
            ];
            if ($source) {
                $request['source'] = $source;
            }
            $customerStripe = \Stripe\Customer::create($request);
            $customerModel->getCollection()->addFieldToFilter('magento_customer_id', $customer->getId())->walk('delete');
            $customerModel->addData([
                'magento_customer_id' => $this->customerSession->getCustomerId(),
                'stripe_customer_id' => $customerStripe->id
            ]);
            $customerModel->save();
            return $customerStripe->id;
        } catch (\Exception $e) {
            $this->stripeLogger->critical($e->getMessage());
            return false;
        }
    }

    public function addSourceToCustomer($stripeCustomerId, $source)
    {
        $request = [
            'source' => $source
        ];
        $url = 'https://api.stripe.com/v1/customers/' . $stripeCustomerId . '/sources';
        $response = $this->sendRequest($request, $url, 'post');
        return $response;
    }

    public function getStripeCustomerId($magentoCustomerId = false)
    {
        try {
            $this->initStripeApi();
            if ($magentoCustomerId) {
                $customerId = $magentoCustomerId;
            } else {
                $customerId = $this->customerSession->getCustomerId();
            }
            $customer = $this->_customerFactory->create()->getCollection()
                ->addFieldToFilter('magento_customer_id', $customerId)
                ->getFirstItem();
            $stripeCustomId = $customer->getData('stripe_customer_id');
            \Stripe\Customer::retrieve($stripeCustomId);
            return $stripeCustomId;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function deleteStripeCustomerId($stripeCustomerId, $isOnline = false)
    {
        $customer = $this->_customerFactory->create()->getCollection()
            ->addFieldToFilter('stripe_customer_id', $stripeCustomerId)
            ->getFirstItem();
        if ($customer->getId()) {
            $customer->delete();
            return true;
        }
        return false;
    }


    /**
     * @param \Magento\Sales\Model\Order $order
     * @param string $paymentToken
     * @param bool $isCapture
     */
    public function createChargeRequest($order, $amount, $paymentToken, $isCapture = true, $dbSource = false, $_stripeCustomerId = false)
    {
        $amount = $this->getPaymentAmount($order, $amount);
        $request = [
            "amount" => round($amount),
            "currency" => $order->getBaseCurrencyCode(),
            "capture" => $isCapture ? 'true' : 'false',
            "source" => $paymentToken,
            "description" => $this->getPaymentDescription($order),
            "metadata" => $this->getPaymentMetaData($order),
        ];
        if ($_stripeCustomerId) {
            $request['customer'] = $_stripeCustomerId;
        }
        if ($dbSource) {
            if ($_stripeCustomerId) {
                $stripeCustomer = $_stripeCustomerId;
            } else {
                $payment = $order->getPayment();
                if ($payment) {
                    $customerId = $payment->getAdditionalInformation('customer_id');
                } else {
                    $customerId = false;
                }
                $stripeCustomer = $this->getStripeCustomerId($customerId);
            }
            if ($stripeCustomer) {
                $request['customer'] = $stripeCustomer;
            }
        }
        if ($this->_config->sendMailCustomer()) {
            $request['receipt_email'] = $order->getCustomerEmail();
        }
        if ($order->getIsNotVirtual()) {
            $request['shipping'] = $this->getShippingInformation($order);
        }
        return $request;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function getShippingInformation($order)
    {
        $shippingAddress = $order->getShippingAddress();
        $dataReturn = [];
        $dataReturn['name'] = $shippingAddress->getName();
        $dataReturn['address'] = [
            'line1' => $shippingAddress->getStreetLine(1),
            'line2' => $shippingAddress->getStreetLine(2),
            'city' => $shippingAddress->getCity(),
            'country' => $shippingAddress->getCountryId(),
            'postal_code' => $shippingAddress->getPostcode(),
            'state' => $shippingAddress->getRegion()
        ];
        return $dataReturn;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function createCaptureRequest($order, $amount)
    {
        $amount = $this->getPaymentAmount($order, $amount);
        $request = [
            "amount" => round($amount),
        ];
        if ($this->_config->sendMailCustomer()) {
            $request['receipt_email'] = $order->getCustomerEmail();
        }
        return $request;
    }

    /**
     * @param \Magento\Sales\Model\Order|\Magento\Quote\Model\Quote $order
     * @param $sourceType
     * @param $redirectUrl
     */
    public function getPaymentSource($order, $sourceType)
    {
        $billingAddress = $order->getBillingAddress();
        $amount = $order->getBaseGrandTotal();
        $currency = $order->getBaseCurrencyCode();
        $_amount = $this->getPaymentAmount($order, $amount);
        $request = [
            "amount" => round($_amount),
            "currency" => strtoupper($currency),
            "type" => $sourceType,
            "owner" => [
                "name" => $billingAddress->getName(),
                "email" => $billingAddress->getEmail(),
                "phone" => $billingAddress->getTelephone(),
                "address" => [
                    "city" => $billingAddress->getCity(),
                    "country" => $billingAddress->getCountryId(),
                    "line1" => $billingAddress->getStreetLine(1),
                    "line2" => $billingAddress->getStreetLine(2),
                    "postal_code" => $billingAddress->getPostcode(),
                    "state" => $billingAddress->getRegion()
                ]
            ],
            "statement_descriptor" => $this->_config->getStatementDescriptor()
        ];
        return $request;
    }

    /**
     * @var \Magento\Sales\Model\Order $order
     */
    public function getDirectSource($order)
    {
        /** @var \Magento\Sales\Model\Order\Address $billing */
        $payment = $order->getPayment();
        $sourceId = $payment->getAdditionalInformation("source_id");
        if ($sourceId) {
            return $sourceId;
        }
        $billing = $order->getBillingAddress();
        $request = [
            "card" => [
                "number" => $order->getPayment()->getCcNumber(),
                'exp_month' => $order->getPayment()->getCcExpMonth(),
                'exp_year' => $order->getPayment()->getCcExpYear(),
                'cvc' => $order->getPayment()->getCcCid(),
                'name' => $billing->getName(),
                'address_line1' => $billing->getStreetLine(1),
                'address_line2' => $billing->getStreetLine(2),
                'address_city' => $billing->getCity(),
                'address_zip' => $billing->getCity(),
                'address_state' => $billing->getRegion(),
                'address_country' => $billing->getCountryId()
            ]
        ];
        $response = \Stripe\Token::create($request);
        $source = $response['id'];
        return $source;
    }

    public function getSaveCard($customerId)
    {
        $col = $this->_cardFactory->create()->getCollection();
        $col->addFieldToFilter("magento_customer_id", $customerId);
        return $col;
    }

    public function createRefundRequest($payment, $chargeId, $amount = null)
    {
        if ($amount) {
            $order = $payment->getOrder();
            $amount = $this->getPaymentAmount($order, $amount);
            $request = [
                "charge" => $chargeId,
                "amount" => round($amount),
            ];
        } else {
            $request = [
                "charge" => $chargeId
            ];
        }

        return $request;
    }

    public function getOrderBySource($sourceId)
    {
        /**
         * @var \Magenest\StripePayment\Model\Source $source
         */
        if ($sourceId) {
            $source = $this->sourceFactory->create()->load($sourceId);
            if ($source->getId()) {
                $orderId = $source->getData('order_id');
                return $this->orderFactory->create()->load($orderId);
            }
        }
        return null;
    }

    /**
     * @var \Exception $e
     */
    public function debugException($e)
    {
        $this->stripeLogger->debug($e->getFile() . ":" . $e->getLine() . ":" . $e->getMessage());
    }

    public function getDataCard($customerId = null)
    {
        $objectManager = ObjectManager::getInstance();
        /** @var \Magento\Customer\Model\Session $customerSession */
        $customerSession = $objectManager->create('Magento\Customer\Model\Session');
        try {
            if (!$customerId) {
                $customerId = $customerSession->getCustomerId();
            }
            $model = $this->_cardFactory->create()
                ->getCollection()
                ->addFieldToFilter('magento_customer_id', $customerId)
                ->addFieldToFilter('status', "active");
            $dataOut = [];
            foreach ($model as $instace) {
                $dataOut[] = [
                    'card_id' => $instace->getId(),
                    'last4' => $instace->getData('last4'),
                    'brand' => $instace->getBrand(),
                ];
            }
            return $dataOut;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getStripeConfig()
    {
        return $this->_config;
    }

    public function getPaymentAmount($order, $amount)
    {
        $currencyCode = $order->getBaseCurrencyCode();
        return $this->getPaymentAmountByCurrency($amount, $currencyCode);
    }
    public function getPaymentAmountByCurrency($amount, $currencyCode)
    {
        $multiply = 100;
        if ($this->isZeroDecimal($currencyCode)) {
            $multiply = 1;
        }
        $amount = round($amount * $multiply);
        return $amount;
    }

    public function continueProcessOrder($orderId)
    {
        if ($orderId) {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->orderRepository->get($orderId);
            $payment = $order->getPayment();
            $methodInstance = $payment->getMethodInstance();
            $action = $methodInstance->getConfigPaymentAction();
            $totalDue = $order->getTotalDue();
            $baseTotalDue = $order->getBaseTotalDue();
            $orderState = \Magento\Sales\Model\Order::STATE_PROCESSING;
            $isCustomerNotified = $order->getCustomerNoteNotify();
            $orderStatus = $methodInstance->getConfigData('order_status');
            switch ($action) {
                case \Magento\Payment\Model\Method\AbstractMethod::ACTION_ORDER:
                    break;
                case \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE:
                    $payment->authorize(true, $baseTotalDue);
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
            $orderState = $order->getState() ? $order->getState() : $orderState;
            $orderStatus = $order->getStatus() ? $order->getStatus() : $orderStatus;
            $isCustomerNotified = $isCustomerNotified ?: $order->getCustomerNoteNotify();
            $message = $order->getCustomerNote();
            $order->setState($orderState)
                ->setStatus($orderStatus)
                ->addStatusHistoryComment($message)
                ->setIsCustomerNotified($isCustomerNotified);
            $this->orderRepository->save($order);
            return $order;
        }
        return null;
    }

    public function sendEmailOrderConfirm($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        if ($order && $order->getCanSendNewEmailFlag()) {
            try {
                $this->stripeLogger->debug("Email send for order ".$orderId);
                $this->orderSender->send($order);
            } catch (\Exception $e) {
                $this->stripeLogger->critical($e->getMessage());
            }
        }
    }

    public function initStripeApi()
    {
        if (!class_exists(\Stripe\Stripe::class)) {
            throw new StripePaymentException(
                __("Stripe PHP library was not installed")
            );
        }
        \Stripe\Stripe::setApiKey($this->_config->getSecretKey());
        \Stripe\Stripe::setAppInfo(
            "Magenest Stripe Global Magento Extension",
            "2.4.2",
            "https://store.magenest.com/",
            "pp_partner_FfsXqlDZX6VDGi"
        );
        \Stripe\Stripe::setApiVersion("2019-09-09");
    }

    /**
     * @param \Magento\Sales\Model\Order\Payment $payment
     */
    public function checkTransaction($payment, $stripeAmount)
    {
        $order = $payment->getOrder();
        $amount = $this->getPaymentAmount($order, $order->getBaseGrandTotal());
        if (bccomp($amount, $stripeAmount)) {
            $this->stripeLogger->debug("Order ".$order->getIncrementId()." Fraud Detected ".$amount ." ". $stripeAmount);
            $payment->setIsFraudDetected(true);
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    public function getPaymentDescription($order)
    {
        return __('Magento Order #') . $order->getIncrementId() . " - " . $order->getCustomerEmail();
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getPaymentMetaData($order)
    {
        return [
            'Magento Order Id' => $order->getIncrementId(),
            'Customer Name' => $order->getCustomerName(),
            'Customer Email' => $order->getCustomerEmail()
        ];
    }

    public function createCustomerIntents($paymentMethod)
    {
        try {
            $customerModel = $this->_customerFactory->create();
            /** @var \Magento\Customer\Model\Session $customerSession */
            $customerSession = $this->objectManager->create('\Magento\Customer\Model\Session');
            $customer = $customerSession->getCustomer();
            $this->initStripeApi();

            $request = [
                "description" => $customer->getName(),
                "email" => $customer->getEmail(),
                'payment_method' => $paymentMethod,
                "metadata" => [
                    "id" => $customer->getId(),
                    "name" => $customer->getName(),
                    "email" => $customer->getEmail()
                ]
            ];

            $customerStripe = \Stripe\Customer::create($request);
            $customerModel->getCollection()->addFieldToFilter('magento_customer_id', $customer->getId())->walk('delete');
            $customerModel->addData([
                'magento_customer_id' => $this->customerSession->getCustomerId(),
                'stripe_customer_id' => $customerStripe->id
            ]);
            $customerModel->save();
            return $customerStripe->id;
        } catch (\Exception $e) {
            $this->stripeLogger->critical($e->getMessage());
            return false;
        }
    }

    public function saveCardIntent($customerId, $payment_method)
    {
        try {
            $cardData = isset($payment_method['card']) ? $payment_method['card'] : [];
            $paymentMethodId = isset($payment_method['id']) ? $payment_method['id'] : "";
            $brand = isset($payment_method->type) ? $cardData['brand'] : "";
            $expMonth = isset($cardData['exp_month']) ? $cardData['exp_month'] : "";
            $expYear = isset($cardData['exp_year']) ? $cardData['exp_year'] : "";
            $cardLast4 = isset($cardData['last4']) ? $cardData['last4'] : "";
            $cardModel = $this->_cardFactory->create();
            $data = [
                'magento_customer_id' => $customerId,
                'card_id' => $paymentMethodId,
                'brand' => $brand,
                'last4' => (string)$cardLast4,
                'exp_month' => (string)$expMonth,
                'exp_year' => (string)$expYear,
                'status' => "active",
                'three_d_secure' => 'intents_auto'
            ];

            $stripeCustomerId = $this->getStripeCustomerId();
            if ($stripeCustomerId) {
                if (!$this->checkStripeCustomerId($stripeCustomerId)) {
                    $this->deleteStripeCustomerId($stripeCustomerId);
                    $stripeCustomerId = $this->createCustomerIntents($paymentMethodId);
                } else {
                    $res = $this->addSourceToCustomer($stripeCustomerId, $paymentMethodId);
                }
            } else {
                $stripeCustomerId = $this->createCustomerIntents($paymentMethodId);
            }

            if ($stripeCustomerId) {
                $cardModel->addData($data)->save();
            }
            return $stripeCustomerId;
        } catch (\Exception $e) {
            $this->stripeLogger->critical("save card exception" . $e->getMessage());
            return false;
        }
    }
}
