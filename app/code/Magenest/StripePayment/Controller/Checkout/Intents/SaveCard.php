<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Stripe extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package Magenest_Stripe
 */

namespace Magenest\StripePayment\Controller\Checkout\Intents;

use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Class SaveCard
 * @package Magenest\StripePayment\Controller\Checkout\Intents
 */
class SaveCard extends \Magento\Framework\App\Action\Action
{
    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;
    /**
     * @var \Magenest\StripePayment\Model\Intents
     */
    protected $intents;
    /**
     * @var \Magenest\StripePayment\Helper\Data
     */
    protected $stripeHelper;

    /**
     * @var \Magenest\StripePayment\Model\Intents
     */

    public function __construct(
        Context $context,
        CheckoutSession $session,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magenest\StripePayment\Model\Intents $intents,
        \Magenest\StripePayment\Helper\Data $stripeHelper
    )
    {
        parent::__construct($context);
        $this->_checkoutSession = $session;
        $this->_formKeyValidator = $formKeyValidator;
        $this->intents = $intents;
        $this->stripeHelper = $stripeHelper;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magenest\StripePayment\Exception\StripePaymentException
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function execute()
    {
        $result = $this->resultFactory->create('json');
        $paymentIntent = $this->getRequest()->getParam('payment_intent');
        if ($this->getRequest()->isAjax()) {
            if (!$this->_formKeyValidator->validate($this->getRequest())) {
                return $result->setData([
                    'error' => true,
                    'message' => __("Invalid Form Key")
                ]);
            }
            $order = $this->_checkoutSession->getLastRealOrder();
            /** @var \Magento\Sales\Model\Order\Payment $payment */
            $payment = $order->getPayment();
            $customerName = $order->getCustomerName();

            $this->stripeHelper->initStripeApi();

            $paymentMethod = isset($paymentIntent['payment_method']) ? $paymentIntent['payment_method'] : "";

            $client_secret = $payment->getAdditionalInformation("client_secret");
            $payment_method = \Stripe\PaymentMethod::retrieve($paymentMethod);
            $stripeCustomerId = $this->stripeHelper->getStripeCustomerId();
            if ($stripeCustomerId) {
                $payment_method->attach(['customer' => $stripeCustomerId]);
            }

            $this->stripeHelper->saveCardIntent($order->getCustomerId(), $payment_method);

            if ($payment) {
                return $result->setData([
                    'success' => true,
                    'error' => false,
                    'client_secret' => $client_secret,
                    'card_name' => $customerName
                ]);
            }
        }
        return $this->_redirect('');
    }
}
