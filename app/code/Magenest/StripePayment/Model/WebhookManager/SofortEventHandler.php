<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Model\WebhookManager;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;

class SofortEventHandler
{
    protected $chargeFactory;
    protected $stripeLogger;
    protected $orderRepository;
    protected $orderManagement;
    protected $invoiceSender;

    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magenest\StripePayment\Helper\Logger $stripeLogger,
        \Magenest\StripePayment\Model\ChargeFactory $chargeFactory,
        OrderManagementInterface $orderManagement,
        InvoiceSender $invoiceSender
    ) {
        $this->orderRepository = $orderRepository;
        $this->stripeLogger = $stripeLogger;
        $this->chargeFactory = $chargeFactory;
        $this->orderManagement = $orderManagement;
        $this->invoiceSender = $invoiceSender;
    }

    public function handleResponse($charge)
    {
        //$this->stripeLogger->debug(var_export($charge, true));
        /** @var \Magento\Sales\Model\Order $order */
        $objectManager = ObjectManager::getInstance();
        $chargeStatus = $charge->status;
        $chargeId = $charge->id;
        $chargeModel = $this->chargeFactory->create()->load($chargeId, "charge_id");
        if ($chargeModel->getId()) {
            $orderId = $chargeModel->getData('order_id');
            $order = $this->orderRepository->get($orderId);
            if ($chargeStatus == 'succeeded') {
                if ($order->canInvoice()) {
                    $invoice = $objectManager->create('Magento\Sales\Model\Service\InvoiceService')->prepareInvoice($order);
                    if (!$invoice->getTotalQty()) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('You can\'t create an invoice without products.')
                        );
                    }
                    $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                    $invoice->register();
                    $invoice->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                    $invoice->getOrder()->setIsInProcess(true);
                    $transaction = $objectManager->create('Magento\Framework\DB\Transaction')
                        ->addObject($invoice)
                        ->addObject($invoice->getOrder());
                    $transaction->save();
                    $this->invoiceSender->send($invoice);
//                    $order->addStatusHistoryComment(
//                        __('Captured invoice #%1.', $invoice->getId())
//                    )
//                        ->setIsCustomerNotified(true)
//                        ->save();
                }
            }
            if ($chargeStatus == 'failed') {
                $this->orderManagement->cancel($orderId);
            }
        } else {
            return false;
        }
        return true;
    }
}
