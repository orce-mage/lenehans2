<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 15:02
 */

namespace Magenest\StripePayment\Controller\Checkout\Multibanco;

use Magento\Framework\Exception\LocalizedException;
use Stripe;
use Magenest\StripePayment\Exception\StripePaymentException;

class Response extends \Magenest\StripePayment\Controller\Checkout\Response
{
    public function execute()
    {
        try {
            /**
             * @var \Magento\Sales\Model\Order\Payment $payment
             * @var \Magento\Sales\Model\Order $order
             */
            $this->_debug("Processing payment response");
            if (!class_exists(Stripe\Stripe::class)) {
                throw new StripePaymentException(
                    __("Stripe PHP library was not installed")
                );
            }
            $this->waitStripeNotification();
            $sourceId = $this->getRequest()->getParam('source');
            $order = $this->stripeHelper->getOrderBySource($sourceId);
            if ($order) {
                $this->stripeHelper->initStripeApi();
                $clientSecretResponse = $this->getRequest()->getParam('client_secret');
                $payment = $order->getPayment();
                $source = Stripe\Source::retrieve($sourceId);
                $this->_debug($source->getLastResponse()->json);
                $clientSecret = $source->client_secret;
                if ($clientSecret != $clientSecretResponse) {
                    throw new StripePaymentException(
                        __("Payment source validation fail")
                    );
                }
                //dont process capture ~~> handle by webhook
                if ($source->status == 'chargeable') {
                    return $this->_redirect('checkout/onepage/success');
                }

                if ($source->status == 'pending') {
                    //Payment pending
                    $referenceNumber = $payment->getAdditionalInformation("stripe_multibanco_reference");
                    $entityNumber = $payment->getAdditionalInformation("stripe_multibanco_entity");
                    $this->messageManager->addWarningMessage("Payment Pending");
                    $this->messageManager->addNoticeMessage("To complete payment, you will need to transfer of funds from your bank account using these reference and entity numbers");
                    $this->messageManager->addNoticeMessage("Reference Number: " . (string)$referenceNumber . " Entity Number: " . (string)$entityNumber);
                    return $this->_redirect('checkout/onepage/success');
                }

                if ($source->status == 'consumed') {
                    return $this->_redirect('checkout/onepage/success');
                }

                if ($source->status == 'failed') {
                    $this->cancelOrder($order, "Payment authentication fail");
                    throw new StripePaymentException(
                        __("Payment failed")
                    );
                }
            } else {
                throw new LocalizedException(__("Cannot find the order"));
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
}
