<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Block\Adminhtml\Creditmemo;

use Magenest\StripePayment\Model\StripePaymentIframe;
use Magenest\StripePayment\Model\StripePaymentMethod;

class RefundReason extends \Magento\Backend\Block\Template
{
    protected $orderFactory;

    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
    
        parent::__construct($context, $data);
        $this->orderFactory = $orderFactory;
    }

    public function canShowOption()
    {
        try {
            $orderId = $this->_request->getParam('order_id');
            $order = $this->orderFactory->create()->load($orderId);
            $payment = $order->getPayment();
            if ($payment) {
                $method = $payment->getMethod();
                if (strpos($method, "magenest_stripe") !== false) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            return false;
        }
        return false;
    }
}
