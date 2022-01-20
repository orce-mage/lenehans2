<?php

namespace StripeIntegration\Payments\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use StripeIntegration\Payments\Helper\Logger;

class OrderObserver extends AbstractDataAssignObserver
{
    public function __construct(
        \StripeIntegration\Payments\Model\Config $config,
        \StripeIntegration\Payments\Model\PaymentIntent $paymentIntent,
        \StripeIntegration\Payments\Helper\Generic $helper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Checkout\Model\Session $checkoutSession
    )
    {
        $this->config = $config;
        $this->paymentIntent = $paymentIntent;
        $this->helper = $helper;
        $this->quoteRepository = $quoteRepository;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $eventName = $observer->getEvent()->getName();
        $method = $order->getPayment()->getMethod();

        if ($method == 'stripe_payments' && $eventName == "sales_order_place_after")
        {
            $this->updateOrderState($observer);
        }
    }

    public function updateOrderState($observer)
    {
        $order = $observer->getEvent()->getOrder();
        $payment = $order->getPayment();

        if ($payment->getAdditionalInformation('stripe_outcome_type') == "manual_review")
            $this->helper->holdOrder($order)->save();

        if ($payment->getAdditionalInformation('authentication_pending'))
        {
            $comment = __("Customer 3D secure authentication is pending for this order.");
            $order->addStatusToHistory($status = \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT, $comment, $isCustomerNotified = false);
            $order->save();
        }
    }
}
