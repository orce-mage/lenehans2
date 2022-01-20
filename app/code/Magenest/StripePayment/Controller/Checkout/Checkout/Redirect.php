<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Controller\Checkout\Checkout;

use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session as CheckoutSession;

class Redirect extends \Magento\Framework\App\Action\Action
{
    protected $_checkoutSession;
    protected $_formKeyValidator;
    protected $stripeCheckout;

    public function __construct(
        Context $context,
        CheckoutSession $session,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magenest\StripePayment\Model\StripeCheckout $stripeCheckout
    ) {
        parent::__construct($context);
        $this->_checkoutSession = $session;
        $this->_formKeyValidator = $formKeyValidator;
        $this->stripeCheckout = $stripeCheckout;
    }

    public function execute()
    {
        $result = $this->resultFactory->create('json');
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
            if ($payment) {
                $sessionId = $payment->getAdditionalInformation("stripe_checkout_session_id");
                if (!$sessionId) {
                    //create new sessionId
                    $sessionId = $this->stripeCheckout->createCheckoutSession($order);
                }
                return $result->setData([
                    'success' => true,
                    'error' => false,
                    'session_id' => $sessionId
                ]);
            }
        }
        return $this->_redirect('');
    }
}
