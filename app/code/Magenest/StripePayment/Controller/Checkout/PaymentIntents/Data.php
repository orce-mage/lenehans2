<?php
namespace Magenest\StripePayment\Controller\Checkout\PaymentIntents;

use Magenest\StripePayment\Exception\StripePaymentException;
use Magenest\StripePayment\Helper\Constant;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session as CheckoutSession;

class Data extends \Magento\Framework\App\Action\Action
{
    protected $_checkoutSession;
    protected $_chargeFactory;
    protected $invoiceSender;
    protected $transactionFactory;
    protected $jsonFactory;
    protected $stripeConfig;
    protected $storeManagerInterface;
    protected $stripeLogger;
    protected $_formKeyValidator;
    protected $stripeHelper;

    public function __construct(
        Context $context,
        CheckoutSession $session,
        \Magenest\StripePayment\Model\ChargeFactory $chargeFactory,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magenest\StripePayment\Helper\Config $stripeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magenest\StripePayment\Helper\Logger $stripeLogger,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magenest\StripePayment\Helper\Data $stripeHelper
    ) {
        parent::__construct($context);
        $this->_checkoutSession = $session;
        $this->_chargeFactory = $chargeFactory;
        $this->invoiceSender = $invoiceSender;
        $this->transactionFactory = $transactionFactory;
        $this->jsonFactory = $resultJsonFactory;
        $this->stripeConfig = $stripeConfig;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->stripeLogger = $stripeLogger;
        $this->_formKeyValidator = $formKeyValidator;
        $this->stripeHelper = $stripeHelper;
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $result->setData([
                'error' => true,
                'message' => __("Invalid Form Key")
            ]);
        }
        if ($this->getRequest()->isAjax()) {
            try {
                if (!class_exists(\Stripe\Stripe::class)) {
                    throw new StripePaymentException(
                        __("Stripe PHP library was not installed")
                    );
                }
                $quote = $this->_checkoutSession->getQuote();
                $grandtotal = $quote->getBaseGrandTotal();
                $currency = $quote->getBaseCurrencyCode();
                $paymentIntent = $this->getPaymentIntent($grandtotal, $currency);
                $clientSecret = $paymentIntent->client_secret;
                if ($clientSecret) {
                    return $result->setData([
                        'success' => true,
                        'error' => false,
                        'clientSecret' => $clientSecret
                    ]);
                }
            } catch (\Stripe\Exception\ApiErrorException $e) {
                $this->stripeLogger->critical($e->getMessage());
                return $result->setData([
                    'error' => true,
                    'message' => $e->getMessage()
                ]);
            } catch (StripePaymentException $e) {
                return $result->setData([
                    'error' => true,
                    'message' => $e->getMessage()
                ]);
            } catch (\Exception $e) {
                $this->stripeLogger->critical($e->getMessage());
                return $result->setData([
                    'error' => true,
                    'message' => __("Payment exception")
                ]);
            }
        }
    }

    public function getPaymentIntent($grandtotal, $currency)
    {
        $paymentAction = $this->stripeConfig->getStripeIntentPaymentAction();
        $amount = $this->stripeHelper->getPaymentAmountByCurrency($grandtotal, $currency);
        $this->stripeHelper->initStripeApi();
        $intent = \Stripe\PaymentIntent::create([
            'amount' => $amount,
            'currency' => $currency,
            'payment_method_types' => ["card"],
            'capture_method' => ($paymentAction=='authorize_capture')?'automatic':'manual'
        ]);
        return $intent;
    }
}
