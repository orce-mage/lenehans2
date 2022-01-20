<?php

namespace StripeIntegration\Payments\Block\Authorization;

use StripeIntegration\Payments\Helper\Logger;

class Multishipping extends \Magento\Framework\View\Element\Template
{
    public $config = null;
    public $orders = [];
    public $paymentIntentClientSecrets = [];

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Session\Generic $session,
        \StripeIntegration\Payments\Helper\Generic $helper,
        \StripeIntegration\Payments\Helper\Multishipping $multishippingHelper,
        \StripeIntegration\Payments\Model\Config $config,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->customerSession = $customerSession;
        $this->session = $session;
        $this->helper = $helper;
        $this->config = $config;

        $orderIds = $this->session->getOrderIds();
        if (!empty($orderIds))
        {
            foreach ($orderIds as $orderId)
            {
                $order = $this->helper->loadOrderByIncrementId($orderId);
                $this->orders[] = $order;

                $payment = $order->getPayment();
                if (empty($payment))
                    continue;

                $paymentIntentClientSecret = $payment->getAdditionalInformation("payment_intent_client_secret");
                if (!empty($paymentIntentClientSecret))
                    $this->paymentIntentClientSecrets[] = $paymentIntentClientSecret;
            }
        }

        if ($this->session->getAddressErrors())
        {
            foreach ($this->session->getAddressErrors() as $msg)
                $this->helper->addError($msg);
        }
    }

    public function getConfirmationUrl()
    {
        return $this->getUrl('stripe/authorization/confirm');
    }

    public function hasErrors()
    {
        if ($this->session->getAddressErrors())
            return (string)'true';
        else
            return (string)'false';
    }

    public function getFormattedAmountFor($order)
    {
        return $order->formatPrice($order->getGrandTotal());
    }
}
