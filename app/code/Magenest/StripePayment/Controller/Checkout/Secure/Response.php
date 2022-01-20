<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 15:02
 */

namespace Magenest\StripePayment\Controller\Checkout\Secure;

use Magenest\StripePayment\Exception\StripePaymentException;
use Magenest\StripePayment\Helper\Constant;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

class Response extends \Magenest\StripePayment\Controller\Checkout\Response
{
    public function execute()
    {
        try {
            /**
             * @var \Magento\Sales\Model\Order\Payment $payment
             * @var \Magento\Sales\Model\Order $order
             */
            $this->_debug("Class: SecureResponse, function: execute");
            $response = $this->getRequest()->getParams();
            $this->_debug($response);
            $cardSource = isset($response['source'])?$response['source']:"";
            $order = $this->stripeHelper->getOrderBySource($cardSource);
            if ($order) {
                $payment = $order->getPayment();
                $clientSecret = $payment->getAdditionalInformation("client_secret");
                $clientSecretConfirm = $response['client_secret'];
                if ($clientSecret != $clientSecretConfirm) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('3d secure validate fail')
                    );
                }
                $this->stripeHelper->initStripeApi();
                $source = \Stripe\Source::retrieve($cardSource);
                $this->_debug($source->getLastResponse()->json);
                if ($source->status == 'failed') {
                    $this->cancelOrder($order, "Payment authentication fail");
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('3d secure authenticate fail')
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
                if ($source->status == 'consumed') {
                    //Source was processed by webhooks, payment complete
                    $this->_debug("Payment consumed");
                    $this->waitStripeNotification();
                    $sourceModel = $this->sourceFactory->create()->load($cardSource);
                    $orderId = $sourceModel->getOrderId();
                    if ($orderId) {
                        $order = $this->orderRepository->get($orderId);
                        $quoteId = $order->getQuoteId();
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
            } else {
                throw new LocalizedException(__("Cannot find the order"));
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirect('checkout/cart');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->stripeHelper->debugException($e);
            $this->_redirect('checkout/cart');
        } catch (\Exception $e) {
            $this->stripeHelper->debugException($e);
            $this->_redirect('checkout/cart');
        }
        return $this->_redirect('checkout/cart');
    }
}
