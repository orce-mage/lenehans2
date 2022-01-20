<?php
namespace StripeIntegration\Payments\Model\Invoice\Total;

use Magento\Quote\Model\Quote;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address\Total;
use StripeIntegration\Payments\Helper\Logger;

class InitialFee extends \Magento\Sales\Model\Order\Total\AbstractTotal
{
    public function __construct(
        \StripeIntegration\Payments\Helper\InitialFee $helper
    )
    {
        $this->helper = $helper;
    }

    /**
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this
     */
    public function collect(
        \Magento\Sales\Model\Order\Invoice $invoice
    ) {

        $amount = $this->helper->getTotalInitialFeeForInvoice($invoice);

        if (is_numeric($invoice->getBaseToOrderRate()))
            $baseAmount = $amount / $invoice->getBaseToOrderRate();
        else
            $baseAmount = $amount;

        $invoice->setGrandTotal($invoice->getGrandTotal() + $amount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseAmount);

        return $this;
    }
}
