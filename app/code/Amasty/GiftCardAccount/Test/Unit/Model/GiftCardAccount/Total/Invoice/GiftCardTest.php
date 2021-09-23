<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


namespace Amasty\GiftCardAccount\Test\Unit\Model\GiftCardAccount\Total\Invoice;

use Amasty\GiftCardAccount\Model\GiftCardAccount\Total\Invoice\GiftCard;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Invoice\Handlers\ReadHandler as InvoiceReadHandler;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Handlers\ReadHandler as OrderReadHandler;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\InvoiceExtension;
use Magento\Sales\Api\Data\OrderExtension;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Amasty\GiftCardAccount\Model\GiftCardAccount\Total\Invoice\GiftCard
 */
class GiftCardTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GiftCard
     */
    private $giftCard;

    /**
     * @var Invoice|MockObject
     */
    private $invoice;

    /**
     * @var InvoiceReadHandler|MockObject
     */
    private $invoceReadHandler;

    /**
     * @var OrderReadHandler|MockObject
     */
    private $orderReadHandler;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->giftCard = $this->createPartialMock(GiftCard::class, []);
        $this->invoice = $this->createPartialMock(Invoice::class, ['getExtensionAttributes']);
        $this->invoceReadHandler = $this->createPartialMock(InvoiceReadHandler::class, ['loadAttributes']);
        $this->orderReadHandler = $this->createPartialMock(OrderReadHandler::class, ['loadAttributes']);
        $this->giftCard = $objectManager->getObject(
            GiftCard::class,
            [
                'invoiceReadHandler' => $this->invoceReadHandler,
                'orderReadHandler' => $this->orderReadHandler
            ]
        );
    }

    /**
     * @dataProvider collectDataProvider
     */
    public function testCollect($orderGiftAmount, $orderInvoiceGiftAmount, $invoiceTotal, $exptected)
    {
        $gCardOrder = $this->createPartialMock(
            \Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Order::class,
            []
        );
        $gCardInvoice = $this->createPartialMock(
            \Amasty\GiftCardAccount\Model\GiftCardExtension\Invoice\Invoice::class,
            []
        );
        $this->invoice->setBaseGrandTotal($invoiceTotal);
        $this->invoice->setGrandTotal($invoiceTotal);
        $gCardOrder->setGiftAmount($orderGiftAmount);
        $gCardOrder->setBaseGiftAmount($orderGiftAmount);
        $gCardOrder->setInvoiceGiftAmount($orderInvoiceGiftAmount);
        $gCardOrder->setBaseInvoiceGiftAmount($orderInvoiceGiftAmount);

        $orderExtension = $this->createPartialMock(OrderExtension::class, ['getAmGiftcardOrder']);
        $invoiceExtension = $this->createPartialMock(InvoiceExtension::class, ['getAmGiftcardInvoice']);

        $order = $this->createPartialMock(Order::class, ['getExtensionAttributes']);
        $this->invoice->setOrder($order);

        $orderExtension->expects($this->atLeastOnce())->method('getAmGiftcardOrder')
            ->willReturn($gCardOrder);
        $invoiceExtension->expects($this->atLeastOnce())->method('getAmGiftcardInvoice')
            ->willReturn($gCardInvoice);
        $this->invoice->expects($this->atLeastOnce())->method('getExtensionAttributes')
            ->willReturn($invoiceExtension);
        $order->expects($this->atLeastOnce())->method('getExtensionAttributes')
            ->willReturn($orderExtension);

        $this->orderReadHandler->expects($this->once())->method('loadAttributes')
            ->with($order);
        $this->invoceReadHandler->expects($this->once())->method('loadAttributes')
            ->with($this->invoice);

        $this->giftCard->collect($this->invoice);
        $this->assertEquals($exptected, $gCardInvoice->getBaseGiftAmount());
        $this->assertEquals(
            $invoiceTotal - $gCardInvoice->getBaseGiftAmount(),
            $this->invoice->getBaseGrandTotal()
        );
    }

    /**
     * @return array
     */
    public function collectDataProvider()
    {
        return [
            [0, 0, 50, 0],//no gift cards
            [50, 0, 100, 50],//gift card doesn't cover whole invoice
            [50, 0, 50, 50],//gift card covers whole invoice
            [50, 25, 60, 25],//partial invoice with gift cards doesn't cover second invoice
            [100, 50, 50, 50]//partial invoice with gift cards covers whole invoice
        ];
    }
}
