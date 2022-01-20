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

class MultibancoEventHandler
{
    protected $chargeFactory;
    protected $stripeLogger;
    protected $orderRepository;
    protected $orderManagement;
    protected $invoiceSender;
    protected $sourceFactory;

    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magenest\StripePayment\Helper\Logger $stripeLogger,
        \Magenest\StripePayment\Model\ChargeFactory $chargeFactory,
        OrderManagementInterface $orderManagement,
        InvoiceSender $invoiceSender,
        \Magenest\StripePayment\Model\SourceFactory $sourceFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->stripeLogger = $stripeLogger;
        $this->chargeFactory = $chargeFactory;
        $this->orderManagement = $orderManagement;
        $this->invoiceSender = $invoiceSender;
        $this->sourceFactory = $sourceFactory;
    }

    public function handleSource($source)
    {
        //$this->stripeLogger->debug(var_export($source, true));
        /** @var \Magento\Sales\Model\Order $order */
        $objectManager = ObjectManager::getInstance();
        $sourceStatus = $source->status;
        $sourceId = $source->id;
        $sourceModel = $this->sourceFactory->create()->load($sourceId);
        if ($sourceModel->getId()) {
            $orderId = $sourceModel->getData('order_id');
            $order = $this->orderRepository->get($orderId);
            if ($sourceStatus == 'chargeable') {
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

                }
            }
            if (($sourceStatus == 'failed') || ($sourceStatus == 'canceled')) {
                $this->orderManagement->cancel($orderId);
            }
        } else {
            return false;
        }
        return true;
    }
}
