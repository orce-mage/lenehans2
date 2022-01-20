<?php
namespace StripeIntegration\Payments\Plugin\Order;

use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Sales\Block\Order\Totals;
use Magento\Sales\Model\Order;
use StripeIntegration\Payments\Helper\Logger;

class AddInitialFeeToTotalsBlock
{
    protected $quotes = [];
    protected $fees = [];

    public function __construct(
        \StripeIntegration\Payments\Helper\InitialFee $helper,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->helper = $helper;
        $this->quoteFactory = $quoteFactory;
        $this->storeManager = $storeManager;
    }

    public function afterGetOrder(Totals $subject, Order $order)
    {
        if (empty($subject->getTotal("grand_total")))
            return $order;

        if ($subject->getTotal('initial_fee') !== false)
            return $order;

        if ($this->isRecurringOrder($subject, $order))
            return $order;

        if ($this->removeInitialFee($order))
            return $order;

        if (!isset($this->quotes[$order->getId()]))
            $this->quotes[$order->getId()] = $this->quoteFactory->create()->load($order->getQuoteId());

        $quote = $this->quotes[$order->getId()];

        if ($subject->getInvoice())
            $items = $subject->getInvoice()->getAllItems();
        else if ($subject->getCreditmemo())
            $items = $subject->getCreditmemo()->getAllItems();
        else
            $items = $order->getAllItems();

        if (!isset($this->fees[$order->getId()]))
            $this->fees[$order->getId()] = $this->helper->getTotalInitialFeeFor($items, $quote);

        $store = $this->storeManager->getStore();

        $rate = $order->getBaseToOrderRate();
        if (empty($rate))
            $rate = 1;

        $baseFee = $this->fees[$order->getId()];
        $fee = round($baseFee * $rate, 2);
        if ($fee > 0)
        {
            $subject->addTotalBefore(new DataObject([
                'code' => 'initial_fee',
                'base_value' => $baseFee,
                'value' => $fee,
                'label' => __('Initial Fee')
            ]), TotalsInterface::KEY_GRAND_TOTAL);
        }

        return $order;
    }

    public function isRecurringOrder($subject, $order)
    {
        if ($order->getPayment()->getAdditionalInformation("is_recurring_subscription"))
            return true;

        return false;
    }

    public function removeInitialFee($order)
    {
        $payment = $order->getPayment();
        if (!$payment)
            return false;

        return $payment->getAdditionalInformation("remove_initial_fee");
    }
}
