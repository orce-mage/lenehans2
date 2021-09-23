<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


namespace Amasty\GiftCardAccount\Test\Unit\Model\GiftCardAccount\Total\Creditmemo;

use Amasty\GiftCardAccount\Model\GiftCardAccount\Total\Creditmemo\GiftCard;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Creditmemo\Handlers\ReadHandler as CreditmemoReadHandler;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Handlers\ReadHandler as OrderReadHandler;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\CreditmemoExtension;
use Magento\Sales\Api\Data\OrderExtension;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Amasty\GiftCardAccount\Model\GiftCardAccount\Total\Creditmemo\GiftCard
 */
class GiftCardTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GiftCard
     */
    private $giftCard;

    /**
     * @var Creditmemo|MockObject
     */
    private $creditmemo;

    /**
     * @var CreditmemoReadHandler|MockObject
     */
    private $memoReadHandler;

    /**
     * @var OrderReadHandler|MockObject
     */
    private $orderReadHandler;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->giftCard = $this->createPartialMock(GiftCard::class, []);
        $this->creditmemo = $this->createPartialMock(Creditmemo::class, ['getExtensionAttributes']);
        $this->memoReadHandler = $this->createPartialMock(CreditmemoReadHandler::class, ['loadAttributes']);
        $this->orderReadHandler = $this->createPartialMock(OrderReadHandler::class, ['loadAttributes']);
        $this->giftCard = $objectManager->getObject(
            GiftCard::class,
            [
                'creditMemoReadHandler' => $this->memoReadHandler,
                'orderReadHandler' => $this->orderReadHandler
            ]
        );
    }

    /**
     * @dataProvider collectDataProvider
     */
    public function testCollect($orderGiftAmount, $orderInvoiceGiftAmount, $orderMemoGiftAmount, $memoTotal, $exptected)
    {
        $gCardOrder = $this->createPartialMock(
            \Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Order::class,
            []
        );
        $gCardMemo = $this->createPartialMock(
            \Amasty\GiftCardAccount\Model\GiftCardExtension\Creditmemo\Creditmemo::class,
            []
        );
        $this->creditmemo->setBaseGrandTotal($memoTotal);
        $this->creditmemo->setGrandTotal($memoTotal);
        $gCardOrder->setGiftAmount($orderGiftAmount);
        $gCardOrder->setBaseGiftAmount($orderGiftAmount);
        $gCardOrder->setInvoiceGiftAmount($orderInvoiceGiftAmount);
        $gCardOrder->setBaseInvoiceGiftAmount($orderInvoiceGiftAmount);
        $gCardOrder->setBaseRefundGiftAmount($orderMemoGiftAmount);
        $gCardOrder->setRefundGiftAmount($orderMemoGiftAmount);

        $orderExtension = $this->createPartialMock(OrderExtension::class, ['getAmGiftcardOrder']);
        $memoExtension = $this->createPartialMock(CreditmemoExtension::class, ['getAmGiftcardCreditmemo']);

        $order = $this->createPartialMock(Order::class, ['getExtensionAttributes']);
        $this->creditmemo->setOrder($order);

        $orderExtension->expects($this->atLeastOnce())->method('getAmGiftcardOrder')
            ->willReturn($gCardOrder);
        $memoExtension->expects($this->atLeastOnce())->method('getAmGiftcardCreditmemo')
            ->willReturn($gCardMemo);
        $this->creditmemo->expects($this->atLeastOnce())->method('getExtensionAttributes')
            ->willReturn($memoExtension);
        $order->expects($this->atLeastOnce())->method('getExtensionAttributes')
            ->willReturn($orderExtension);

        $this->orderReadHandler->expects($this->once())->method('loadAttributes')
            ->with($order);
        $this->memoReadHandler->expects($this->once())->method('loadAttributes')
            ->with($this->creditmemo);

        $this->giftCard->collect($this->creditmemo);
        $this->assertEquals($exptected, $gCardMemo->getBaseGiftAmount());
        $this->assertEquals(
            $memoTotal - $gCardMemo->getBaseGiftAmount(),
            $this->creditmemo->getBaseGrandTotal()
        );
    }

    public function collectDataProvider()
    {
        return [
            [0, 0, 0, 50, 0],//no gift cards
            [50, 50, 0, 100, 50],//gift card doesn't cover whole creditmemo
            [50, 50, 0, 50, 50],//gift card covers whole creditmemo
            [50, 50, 25, 60, 25],//partial creditmemo with gift cards doesn't cover second creditmemo
            [50, 50, 25, 50, 25],//partial creditmemo with gift cards covers whole creditmemo
            [50, 25, 0, 25, 25]//partial creditmemo with gift cards covers whole creditmemo, not full invoice
        ];
    }
}
