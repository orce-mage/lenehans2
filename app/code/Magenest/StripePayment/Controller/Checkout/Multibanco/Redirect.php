<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 15:02
 */

namespace Magenest\StripePayment\Controller\Checkout\Multibanco;

use Magenest\StripePayment\Helper\Constant;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Controller\ResultFactory;
use Magenest\StripePayment\Exception\StripePaymentException;

class Redirect extends \Magento\Framework\App\Action\Action
{
    protected $_checkoutSession;
    protected $_formKeyValidator;

    public function __construct(
        Context $context,
        CheckoutSession $session,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
    ) {
        parent::__construct($context);
        $this->_checkoutSession = $session;
        $this->_formKeyValidator = $formKeyValidator;
    }

    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        try {
            if (!$this->_formKeyValidator->validate($this->getRequest())) {
                throw new StripePaymentException(
                    __("Invalid form key")
                );
            }
            $order = $this->_checkoutSession->getLastRealOrder();
            /** @var \Magento\Sales\Model\Order\Payment $payment */
            $payment = $order->getPayment();
            $redirectUrl = $payment->getAdditionalInformation("stripe_redirect_url");
            $result->setData([
                'error' => false,
                'success' => true,
                'redirect_url' => $redirectUrl
            ]);

        } catch (\Magenest\StripePayment\Exception\StripePaymentException $e) {
            $result->setData([
                'error' => true,
                'success' => false,
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            $result->setData([
                'error' => true,
                'success' => false,
                'message' => "Cannot process payment"
            ]);
        } finally {
            return $result;
        }
    }
}
