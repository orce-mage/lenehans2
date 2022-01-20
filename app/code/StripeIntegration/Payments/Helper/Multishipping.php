<?php

namespace StripeIntegration\Payments\Helper;

use Psr\Log\LoggerInterface;
use StripeIntegration\Payments\Helper\Logger;

class Multishipping
{

    public function __construct(
        \StripeIntegration\Payments\Helper\Generic $helper,
        \Magento\Framework\Session\Generic $session,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \StripeIntegration\Payments\Model\PaymentIntent $paymentIntent
    ) {
        $this->helper = $helper;
        $this->session = $session;
        $this->cart = $cart;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->paymentIntent = $paymentIntent;
    }

    public function confirmPaymentsForSessionOrders()
    {
        $orderIds = $this->session->getOrderIds();
        $outcomes = ["hasErrors" => false];

        if (empty($orderIds))
            return [];

        foreach ($orderIds as $orderId)
        {
            $order = $this->helper->loadOrderByIncrementId($orderId);

            if ($this->helper->hasTrialSubscriptionsIn($order->getAllItems()))
                continue;

            try
            {
                $paymentIntent = $this->confirmPaymentFor($order);

                $transactionId = $paymentIntent->id;
                $transactionPending = $paymentIntent->charges->data[0]->captured;

                $order->getPayment()
                    ->setIsTransactionPending($transactionPending)
                    ->setLastTransId($transactionId)
                    ->save();

                $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)
                    ->setStatus($order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_PROCESSING))
                    ->setCanSendNewEmailFlag(true)
                    ->addStatusToHistory($status = false, $comment = __("Payment authentication succeeded."), $isCustomerNotified = false)
                    ->save();

                $this->paymentIntent->processAuthenticatedOrder($order, $paymentIntent);

                $this->helper->sendNewOrderEmailFor($order);

                $outcomes["orders"][$order->getIncrementId()] = [
                    "success" => true,
                    "message" => __("Order #%1 has been placed successfully", $order->getIncrementId())
                ];
            }
            catch (\Exception $e)
            {
                $outcomes["hasErrors"] = true;

                $outcomes["orders"][$order->getIncrementId()] = [
                    "success" => false,
                    "message" => __("Order #%1 could not be placed: %2. Please try placing the order again.", $order->getIncrementId(), $e->getMessage())
                ];

                $comment = __("The payment for this order could not be confirmed: %1.", $e->getMessage());
                $order->addStatusToHistory($status = \Magento\Sales\Model\Order::STATE_CANCELED, $comment, $isCustomerNotified = false)
                    ->setState(\Magento\Sales\Model\Order::STATE_CANCELED)
                    ->save();

                $this->restoreSessionQuoteFor($order);
            }
        }

        return $outcomes;
    }

    public function confirmPaymentFor($order)
    {
        $payment = $order->getPayment();
        if (empty($payment))
            throw new \Exception("Invalid payment for order #" . $order->getIncrementId());

        $paymentIntentId = $payment->getAdditionalInformation("payment_intent_id");
        if (empty($paymentIntentId))
            throw new \Exception("Payment Intent ID not found for order #" . $order->getIncrementId());

        $pi = \Stripe\PaymentIntent::retrieve($paymentIntentId);

        try
        {
            if ($this->paymentIntent->isSuccessfulStatus($pi))
                return $pi;

            $pi->confirm();
            $this->paymentIntent->prepareRollback();

            if (!$this->paymentIntent->isSuccessfulStatus($pi))
            {
                // if (isset($pi->last_payment_error->message))
                //     throw new \Exception($pi->last_payment_error->message);

                throw new \Exception("Payment authentication failed");
            }
        }
        catch (\Exception $e)
        {
            $this->paymentIntent->prepareRollback();
            throw new \Exception("Payment authentication failed");
        }

        return $pi;
    }

    public function restoreSessionQuoteFor($order)
    {
        $orderItems = $order->getAllItems();
        foreach ($orderItems as $orderItem) {
            $productIds[] = $orderItem->getProductId();
        }

        $quoteId = $order->getQuoteId();
        $quote = $this->helper->loadQuoteById($quoteId);

        $items = $quote->getAllVisibleItems();

        foreach ($items as $item)
        {
            $productId = $item->getProductId();
            if (in_array($productId, $productIds))
            {
                $_product = $this->helper->loadProductById($productId);
                $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                $info = $options['info_buyRequest'];
                $request = $this->dataObjectFactory->create();
                $request->setData($info);
                $this->cart->addProduct($_product, $request);
            }
        }
        $this->cart->save();
    }
}
