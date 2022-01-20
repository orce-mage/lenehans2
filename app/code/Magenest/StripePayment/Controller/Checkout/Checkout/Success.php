<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Controller\Checkout\Checkout;

use Magenest\StripePayment\Helper\Data;
use Magenest\StripePayment\Model\ChargeFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Stripe\Error\Api;
use Stripe\Event;

class Success extends Action
{
    protected $_checkoutSession;
    protected $_helper;
    protected $chargeFactory;

    public function __construct(
        Context $context,
        CheckoutSession $session,
        Data $_helper,
        ChargeFactory $chargeFactory
    ) {
        parent::__construct($context);
        $this->_checkoutSession = $session;
        $this->_helper = $_helper;
        $this->chargeFactory = $chargeFactory;
    }

    public function execute()
    {
        $order = $this->_checkoutSession->getLastRealOrder();
        $payment = $order->getPayment();
        if ($payment) {
            $chargeId = (string)$payment->getAdditionalInformation('stripe_charge_id');
            $this->_helper->initStripeApi();
            try {
                $events = Event::all([
                    'type' => 'checkout.session.completed',
                    'created' => [
                        // Check for events created in the last 5 minutes
                        'gte' => time() - 5 * 60,
                    ],
                ]);
                $eventsData = $events->autoPagingIterator();
            } catch (Api $e) {
                $eventsData = [];
            }

            foreach ($eventsData as $event) {
                $session = $event->data->object;
                if ($session->payment_intent == $chargeId) {
                    $chargeModel = $this->chargeFactory->create()->load($chargeId, "charge_id");
                    if ($chargeModel->getId()) {
                        $orderId = $chargeModel->getData('order_id');
                        if (!$payment->getAdditionalInformation('stripe_checkout_finish')) {
                            $this->_helper->continueProcessOrder($orderId);
                            $this->_helper->sendEmailOrderConfirm($orderId);
                        }
                    }
                    break;
                }
            }
            if ($payment->getMethod() == 'magenest_stripe_checkout') {
                return $this->_redirect('checkout/onepage/success');
            }
        }
        return $this->_redirect('checkout/cart');
    }
}
