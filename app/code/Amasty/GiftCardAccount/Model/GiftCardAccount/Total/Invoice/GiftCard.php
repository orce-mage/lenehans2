<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardAccount\Total\Invoice;

use Amasty\GiftCardAccount\Model\GiftCardExtension\Invoice\Handlers\ReadHandler as InvoiceReadHandler;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Handlers\ReadHandler as OrderReadHandler;

class GiftCard extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    /**
     * @var OrderReadHandler
     */
    private $orderReadHandler;

    /**
     * @var InvoiceReadHandler
     */
    private $invoiceReadHandler;

    public function __construct(
        OrderReadHandler $orderReadHandler,
        InvoiceReadHandler $invoiceReadHandler,
        array $data = []
    ) {
        parent::__construct($data);
        $this->orderReadHandler = $orderReadHandler;
        $this->invoiceReadHandler = $invoiceReadHandler;
    }

    /**
     * Collect gift card account totals for invoice
     *
     * @inheritDoc
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $this->invoiceReadHandler->loadAttributes($invoice);
        $gCardInvoice = $invoice->getExtensionAttributes()->getAmGiftcardInvoice();

        $order = $invoice->getOrder();
        $this->orderReadHandler->loadAttributes($order);
        $gCardOrder = $order->getExtensionAttributes()->getAmGiftcardOrder();

        if ($gCardOrder->getBaseGiftAmount()
            && $gCardOrder->getBaseInvoiceGiftAmount() != $gCardOrder->getBaseGiftAmount()
        ) {
            $gcaLeft = $gCardOrder->getBaseGiftAmount() - $gCardOrder->getBaseInvoiceGiftAmount();

            if ($gcaLeft >= $invoice->getBaseGrandTotal()) {
                $baseUsed = $invoice->getBaseGrandTotal();
                $used = $invoice->getGrandTotal();

                $invoice->setBaseGrandTotal(0);
                $invoice->setGrandTotal(0);
            } else {
                $baseUsed = $gCardOrder->getBaseGiftAmount() - $gCardOrder->getBaseInvoiceGiftAmount();
                $used = $gCardOrder->getGiftAmount() - $gCardOrder->getInvoiceGiftAmount();

                $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $baseUsed);
                $invoice->setGrandTotal($invoice->getGrandTotal() - $used);
            }
            $gCardInvoice->setBaseGiftAmount($baseUsed);
            $gCardInvoice->setGiftAmount($used);

            $gCardOrder->setBaseInvoiceGiftAmount((float)($gCardOrder->getBaseInvoiceGiftAmount() + $baseUsed));
            $gCardOrder->setInvoiceGiftAmount((float)($gCardOrder->getInvoiceGiftAmount() + $used));
        }

        return $this;
    }
}
