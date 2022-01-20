<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Controller\Checkout;

use Magenest\StripePayment\Exception\StripePaymentDuplicateException;
use Magenest\StripePayment\Exception\StripePaymentException;
use Magenest\StripePayment\Helper\Constant;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\GuestCart\GuestCartManagement;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Stripe;

class Response extends \Magento\Framework\App\Action\Action
{
    protected $_checkoutSession;
    protected $_chargeFactory;
    protected $invoiceSender;
    protected $transactionFactory;
    protected $jsonFactory;
    protected $stripeConfig;
    protected $storeManagerInterface;
    protected $stripeLogger;
    protected $orderSender;
    protected $stripeHelper;

    /**
     * @var \Magento\Quote\Model\QuoteManagement
     */
    protected $quoteManagement;

    protected $cartRepository;

    protected $sourceFactory;

    protected $orderRepository;

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
        OrderSender $orderSender,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magenest\StripePayment\Helper\Data $stripeHelper,
        \Magenest\StripePayment\Model\SourceFactory $sourceFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
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
        $this->orderSender = $orderSender;
        $this->quoteManagement = $quoteManagement;
        $this->stripeHelper = $stripeHelper;
        $this->sourceFactory = $sourceFactory;
        $this->cartRepository = $cartRepository;
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        try {
            $this->_debug("Processing payment response");
            if (!class_exists(Stripe\Stripe::class)) {
                throw new StripePaymentException(
                    __("Stripe PHP library was not installed")
                );
            }
            $this->waitStripeNotification();
            $this->stripeHelper->initStripeApi();
            $sourceId = $this->getRequest()->getParam('source');
            $clientSecretResponse = $this->getRequest()->getParam('client_secret');
            $source = Stripe\Source::retrieve($sourceId);
            $this->_debug($source->getLastResponse()->json);
            $clientSecret = $source->client_secret;
            if ($clientSecret != $clientSecretResponse) {
                throw new StripePaymentException(
                    __("Payment source validation fail")
                );
            }
            if ($source->status == 'chargeable') {
                if ($this->processOrder($source)) {
                    return $this->_redirect('checkout/onepage/success');
                } else {
                    throw new StripePaymentException(
                        __("Order processing error")
                    );
                }
            }
            if ($source->status == 'pending') {
                $this->messageManager->addWarningMessage(__("Payment is authorizing for complete order"));
                return $this->_redirect('checkout/cart');
            }
            if ($source->status == 'consumed') {
                $this->_debug("Payment consumed");
                $this->waitStripeNotification();
                //Source was processed by webhooks, payment complete
                $sourceModel = $this->sourceFactory->create()->load($sourceId);
                $quoteId = $sourceModel->getQuoteId();
                $orderId = $sourceModel->getOrderId();
                if ($orderId) {
                    $order = $this->orderRepository->get($orderId);
                    $this->_checkoutSession->setLastQuoteId($quoteId);
                    $this->_checkoutSession->setLastSuccessQuoteId($quoteId);
                    $this->_checkoutSession->setLastOrderId($order->getId());
                    $this->_checkoutSession->setLastRealOrderId($order->getIncrementId());
                    $this->_checkoutSession->setLastOrderStatus($order->getStatus());
                    return $this->_redirect('checkout/onepage/success');
                } else {
                    $this->messageManager->addWarningMessage(__("Payment success"));
                    return $this->_redirect('checkout/cart');
                }
            }
            if ($source->status == 'failed') {
                $quote = $this->cartRepository->get($this->_checkoutSession->getQuoteId());
                $quote->setIsActive(1)->setReservedOrderId(null);
                $this->cartRepository->save($quote);
                throw new StripePaymentException(
                    __("Payment failed")
                );
            }
        } catch (Stripe\Exception\ApiErrorException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_debug($e->getMessage());
            return $this->_redirect('checkout/cart');
        } catch (StripePaymentException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_debug($e->getMessage());
            return $this->_redirect('checkout/cart');
        } catch (LocalizedException $e) {
            $this->stripeHelper->debugException($e);
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_debug($e->getMessage());
            return $this->_redirect('checkout/cart');
        } catch (\Exception $e) {
            $this->stripeHelper->debugException($e);
            $this->messageManager->addErrorMessage("Payment Exception");
            $this->_debug($e->getMessage());
            return $this->_redirect('checkout/cart');
        }
        return $this->_redirect('checkout/cart');
    }

    /**
     * @param Stripe\StripeObject $source
     */
    public function processOrder($source)
    {
        /**
         * @var \Magento\Sales\Model\Order\Payment $payment
         * @var \Magento\Customer\Model\Session $customerSession
         * @var \Magento\Quote\Model\Quote $quote
         * @var \Magento\Sales\Model\Order $order
         */
        try {
            $sourceModel = $this->sourceFactory->create()->load($source->id);
            $quoteId = $sourceModel->getQuoteId();
            $orderId = $sourceModel->getOrderId();
            if ($orderId) {
                $order = $this->stripeHelper->continueProcessOrder($orderId);
                if ($order) {
                    $payment = $order->getPayment();
                    $this->setSourceAdditionalInformation($source, $payment);
                    $payment->setAdditionalInformation('payment_token', $source->id);
                }
            } else {
                if ($quoteId) {
                    $quote = $this->cartRepository->get($quoteId);
                    $quote->setIsActive(true);
                    $payment = $quote->getPayment();
                    $customerSession = $this->_objectManager->create('Magento\Customer\Model\Session');
                    if (!$customerSession->isLoggedIn()) {
                        $quote->setCheckoutMethod(\Magento\Quote\Model\QuoteManagement::METHOD_GUEST);
                    }
                    $this->setSourceAdditionalInformation($source, $payment);
                    $orderId = $this->quoteManagement->placeOrder($quote->getId(), $payment);
                    $order = $this->orderRepository->get($orderId);
                } else {
                    throw new StripePaymentException(__("Cannot get cart info"));
                }
            }
            if ($order->getCanSendNewEmailFlag()) {
                try {
                    $this->stripeLogger->debug("Email send for order ".$orderId);
                    $this->orderSender->send($order);
                } catch (\Exception $e) {
                    $this->stripeLogger->critical($e->getMessage());
                }
            }
            return true;
        } catch (StripePaymentDuplicateException $e) {
            $this->waitStripeNotification();
            $this->_debug("Payment consumed");
            $order = $this->stripeHelper->getOrderBySource($source->id);
            $quoteId = $order->getQuoteId();
            $this->_checkoutSession->setLastQuoteId($quoteId);
            $this->_checkoutSession->setLastSuccessQuoteId($quoteId);
            $this->_checkoutSession->setLastOrderId($order->getId());
            $this->_checkoutSession->setLastRealOrderId($order->getIncrementId());
            $this->_checkoutSession->setLastOrderStatus($order->getStatus());
            return true;
        } catch (\Exception $e) {
            $this->stripeHelper->debugException($e);
            return false;
        }
    }

    public function notifyCustomer($sourceId)
    {
        /**
         * @var \Magento\Sales\Model\Order $order
         */
        if ($sourceId) {
            $sourceModel = $this->sourceFactory->create()->load($sourceId);
            $orderId = $sourceModel->getData("order_id");
            $order = $this->orderRepository->get($orderId);
            $order->addStatusHistoryComment("The charge succeeded and the payment is complete")
                ->setIsCustomerNotified(true);
            $this->orderRepository->save($order);
        }
    }

    /**
     * @var \Magento\Sales\Model\Order $order
     */
    public function cancelOrder($order, $comment = null)
    {
        if ($order) {
            $this->_debug("Cancel order ".$order->getEntityId());
            $order->cancel();
            $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED);
            $order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
            $payment = $order->getPayment();
            $payment
                ->setShouldCloseParentTransaction(true)
                ->setIsTransactionClosed(true);
            if ($comment) {
                $order->addStatusHistoryComment($comment)->setIsCustomerNotified(true);
            }
            $this->orderRepository->save($order);
            $this->_checkoutSession->restoreQuote();
        }
    }

    protected function waitStripeNotification()
    {
        sleep(Constant::RETRY_TIMEOUT);
    }

    /**
     * @param Stripe\StripeObject $source
     * @param \Magento\Quote\Model\Quote\Payment $payment
     */
    protected function setSourceAdditionalInformation($source, $payment)
    {
        $payment->setAdditionalInformation("stripe_source_id", $source->id);
    }

    /**
     * @param array|string $debugData
     */
    protected function _debug($debugData)
    {
        $this->stripeLogger->debug(var_export($debugData, true));
    }
}
