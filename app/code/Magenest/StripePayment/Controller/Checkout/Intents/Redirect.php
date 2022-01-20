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
 * Class Redirect
 * @package Magenest\StripePayment\Controller\Checkout\Intents
 */
class Redirect extends \Magento\Framework\App\Action\Action
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
    ) {
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
        $this->stripeHelper->initStripeApi();
        $result = $this->resultFactory->create('json');
        if ($this->getRequest()->isAjax()) {
            if (!$this->_formKeyValidator->validate($this->getRequest())) {
                return $result->setData([
                    'error' => true,
                    'message' => __("Invalid Form Key")
                ]);
            }
            $order = $this->_checkoutSession->getLastRealOrder();
            $customerName = $order->getCustomerName();

            /** @var \Magento\Sales\Model\Order\Payment $payment */ 
            $payment = $order->getPayment();
            if ($payment) {
                $client_secret = $payment->getAdditionalInformation("client_secret");
                $_saved = $payment->getAdditionalInformation('save_option');
                $card_id = $payment->getAdditionalInformation('card_id');
                if (!$client_secret) {
                    //create new sessionId
                    $client_secret = $this->intents->getPaymentIntent($order->getBaseGrandTotal(),$order->getBaseCurrencyCode());
                }
                return $result->setData([
                    'success' => true,
                    'error' => false,
                    'client_secret' => $client_secret,
                    'save_option' => $_saved,
                    'card_id' => $card_id,
                    'card_name' => $customerName
                ]);
            }
        }
        return $this->_redirect('');
    }
}
