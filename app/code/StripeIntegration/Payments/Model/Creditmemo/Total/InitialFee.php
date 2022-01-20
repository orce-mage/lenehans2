<?php
namespace StripeIntegration\Payments\Model\Creditmemo\Total;

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
        \Magento\Sales\Model\Order\Creditmemo $creditmemo
    ) {
        $amount = $this->helper->getTotalInitialFeeForCreditmemo($creditmemo);
        if (is_numeric($creditmemo->getBaseToQuoteRate()))
            $baseAmount = $amount / $creditmemo->getBaseToQuoteRate();
        else
            $baseAmount = $amount;

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $amount);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseAmount);

        return $this;
    }
}
