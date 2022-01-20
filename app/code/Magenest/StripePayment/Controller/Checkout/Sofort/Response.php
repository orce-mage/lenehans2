<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Controller\Checkout\Sofort;

use Magenest\StripePayment\Exception\StripePaymentException;
use Magento\Sales\Model\Order;

class Response extends \Magenest\StripePayment\Controller\Checkout\Response
{
    protected function setSourceAdditionalInformation($source, $payment)
    {
        parent::setSourceAdditionalInformation($source, $payment);
        $bankName = $source->sofort->bank_name;
        $bankCode = $source->sofort->bank_code;
        $bic = $source->sofort->bic;
        $ibanLast4 = $source->sofort->iban_last4;
        $sourceAdditionalInformation = [];
        $sourceAdditionalInformation[] = [
            'label' => "Payment Method",
            'value' => "SOFORT"
        ];
        if ($bankName) {
            $sourceAdditionalInformation[] = [
                'label' => "Bank name",
                'value' => $bankName
            ];
        }
        if ($bankCode) {
            $sourceAdditionalInformation[] = [
                'label' => "Bank code",
                'value' => $bankCode
            ];
        }
        if ($bic) {
            $sourceAdditionalInformation[] = [
                'label' => "BIC",
                'value' => $bic
            ];
        }
        if ($ibanLast4) {
            $sourceAdditionalInformation[] = [
                'label' => "IBAN last4",
                'value' => $ibanLast4
            ];
        }
        $payment->setAdditionalInformation("stripe_source_additional_information", json_encode($sourceAdditionalInformation));
    }

    /**
     * @param \Stripe\StripeObject $source
     */
    public function processCharge($charge)
    {
        /**
         * @var \Magento\Sales\Model\Order\Payment $payment
         * @var \Magento\Customer\Model\Session $customerSession
         * @var \Magento\Quote\Model\Quote $quote
         * @var \Magento\Sales\Model\Order $order
         */
        try {
            $this->waitStripeNotification();
            $sourceId = $charge->source->id;
            $sourceModel = $this->sourceFactory->create()->load($sourceId);
            $orderId = $sourceModel->getOrderId();
            if ($orderId) {
                $order = $this->orderRepository->get($orderId);
                $payment = $order->getPayment();
                $methodInstance = $payment->getMethodInstance();
                $action = $methodInstance->getConfigPaymentAction();
                $totalDue = $order->getTotalDue();
                $baseTotalDue = $order->getBaseTotalDue();
                $orderState = Order::STATE_PROCESSING;
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
            } else {
                throw new StripePaymentException(__("Cannot get order info"));
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
        } catch (\Exception $e) {
            $this->stripeHelper->debugException($e);
            return false;
        }
    }
}
