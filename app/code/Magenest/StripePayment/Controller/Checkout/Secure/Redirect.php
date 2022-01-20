<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Controller\Checkout\Secure;

use Magenest\StripePayment\Helper\Constant;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session as CheckoutSession;

class Redirect extends \Magento\Framework\App\Action\Action
{
    protected $_checkoutSession;
    protected $_chargeFactory;
    protected $invoiceSender;
    protected $transactionFactory;
    protected $jsonFactory;
    protected $stripeConfig;
    protected $storeManagerInterface;
    protected $stripeLogger;
    protected $_formKeyValidator;

    public function __construct(
        Context $context,
        CheckoutSession $session,
        \Magenest\StripePayment\Model\ChargeFactory $chargeFactory,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magenest\StripePayment\Helper\Config $stripeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magenest\StripePayment\Helper\Logger $stripeLogger,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
    ) {
        parent::__construct($context);
        $this->_checkoutSession = $session;
        $this->_chargeFactory = $chargeFactory;
        $this->invoiceSender = $invoiceSender;
        $this->transactionFactory = $transactionFactory;
        $this->jsonFactory = $resultJsonFactory;
        $this->stripeConfig = $stripeConfig;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->stripeLogger = $stripeLogger;
        $this->_formKeyValidator = $formKeyValidator;
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $result->setData([
                'error' => true,
                'message' => __("Invalid Form Key")
            ]);
        }
        if ($this->getRequest()->isAjax()) {
            try {
                $order = $this->_checkoutSession->getLastRealOrder();
                /** @var \Magento\Sales\Model\Order\Payment $payment */
                $payment = $order->getPayment();
                if ($payment) {
                    $threeDAction = $payment->getAdditionalInformation(Constant::ADDITIONAL_THREEDS);
                    if ($threeDAction == 'true') {
                        $threeDSecureUrl = $payment->getAdditionalInformation("threed_secure_url");
                        return $result->setData([
                            'success' => true,
                            'threeDSercueActive' => true,
                            'threeDSercueUrl' => $threeDSecureUrl,
                            'defaultPay' => false
                        ]);
                    }
                }
            } catch (\Exception $e) {
                $this->stripeLogger->critical($e->getMessage());

                return $result->setData([
                    'error' => true,
                    'message' => __("Payment exception")
                ]);
            }
        }

        return $result->setData([
            'success' => true,
            'threeDSercueActive' => false,
            'defaultPay' => true
        ]);
    }
}
