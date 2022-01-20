<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class SaveCharge implements ObserverInterface
{
    protected $chargeFactory;
    protected $sourceFactory;
    protected $stripeHelper;

    public function __construct(
        \Magenest\StripePayment\Model\ChargeFactory $chargeFactory,
        \Magenest\StripePayment\Model\SourceFactory $sourceFactory,
        \Magenest\StripePayment\Helper\Data $stripeHelper
    ) {
        $this->sourceFactory = $sourceFactory;
        $this->chargeFactory = $chargeFactory;
        $this->stripeHelper = $stripeHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var \Magento\Sales\Model\Order $order
         * @var \Magento\Sales\Model\Order\Payment $payment
         */
        $order = $observer->getOrder();
        $orderId = $order->getEntityId();
        $payment = $order->getPayment();
        $methodName = $payment->getMethod();

        $this->changeOrderStatus($order);

        if (strpos($methodName, "magenest_stripe") !== false) {
            $chargeId = $payment->getAdditionalInformation('stripe_charge_id');
            if ($chargeId) {
                $chargeModel = $this->chargeFactory->create()->load($chargeId, "charge_id");
                if (!$chargeModel->getId()) {
                    $chargeModel->setData("charge_id", $chargeId);
                    $chargeModel->setData("order_id", $orderId);
                    $chargeModel->setData("method", $methodName);
                    $chargeModel->save();
                }
            }

            $sourceId = $payment->getAdditionalInformation('stripe_source_id');
            if ($sourceId) {
                $sourceModel = $this->sourceFactory->create()->load($sourceId);
                if (!$sourceModel->getId()) {
                    $sourceModel->setData("source_id", $sourceId);
                    $sourceModel->isObjectNew(true);
                }
                $sourceModel->addData([
                    'order_id' => $orderId,
                    'method' => $methodName
                ]);
                $sourceModel->save();
            }

        }
    }

    protected function changeOrderStatus($order) {
        $this->stripeHelper->initStripeApi();
        $event = \Stripe\Event::all(['limit' => 1]);
        $eventStatus = $event->data[0]->type;

        if ($eventStatus == 'payment_intent.succeeded' || $eventStatus == 'payment_intent.amount_capturable_updated') {
            $orderState = Order::STATE_PROCESSING;
            $order->setState($orderState)->setStatus(Order::STATE_PROCESSING);
            $order->save();
        }
    }
}
