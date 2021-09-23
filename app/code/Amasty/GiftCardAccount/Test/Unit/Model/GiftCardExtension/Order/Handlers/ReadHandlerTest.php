<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


namespace Amasty\GiftCardAccount\Test\Unit\Model\GiftCardExtension\Order\Handlers;

use Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Handlers\ReadHandler;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Order as GiftCardOrder;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Repository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderExtension;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Handlers\ReadHandler
 */
class ReadHandlerTest extends \PHPUnit\Framework\TestCase
{
    const ORDER_ID = 1;

    /**
     * @var ReadHandler
     */
    private $readHandler;

    /**
     * @var Repository|MockObject
     */
    private $gCardOrderRepository;

    /**
     * @var GiftCardOrder
     */
    private $gCardOrder;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->gCardOrderRepository = $this->createPartialMock(
            Repository::class,
            ['getByOrderId', 'getEmptyOrderModel']
        );
        $this->readHandler = $objectManager->getObject(
            ReadHandler::class,
            [
                'repository' => $this->gCardOrderRepository
            ]
        );
    }

    public function testLoadAttributesNewOrder()
    {
        $order = $this->initOrderWithExtension();

        $this->gCardOrderRepository->expects($this->once())->method('getByOrderId')
            ->with(self::ORDER_ID)
            ->willThrowException(new NoSuchEntityException());
        $this->gCardOrderRepository->expects($this->once())->method('getEmptyOrderModel')
            ->willReturn($this->gCardOrder);

        $this->readHandler->loadAttributes($order);
        $this->assertEquals(self::ORDER_ID, $this->gCardOrder->getOrderId());
    }

    public function testLoadAttributesExistingOrder()
    {
        $order = $this->initOrderWithExtension(self::ORDER_ID);

        $this->gCardOrderRepository->expects($this->once())->method('getByOrderId')
            ->with(self::ORDER_ID)
            ->willReturn($this->gCardOrder);
        $this->gCardOrderRepository->expects($this->never())->method('getEmptyOrderModel');

        $this->readHandler->loadAttributes($order);
        $this->assertEquals(self::ORDER_ID, $this->gCardOrder->getOrderId());
    }

    public function testLoadAttributesWithLoadedExtension()
    {
        $this->gCardOrder = $this->createPartialMock(GiftCardOrder::class, []);
        $extensionAttributes = $this->createPartialMock(
            OrderExtension::class,
            ['getAmGiftcardOrder']
        );
        $extensionAttributes->expects($this->atLeastOnce())->method('getAmGiftcardOrder')
            ->willReturn($this->gCardOrder);
        $order = $this->createPartialMock(Order::class, ['getExtensionAttributes']);
        $order->expects($this->any())->method('getExtensionAttributes')->willReturn($extensionAttributes);

        $this->gCardOrderRepository->expects($this->never())->method('getByOrderId');
        $this->readHandler->loadAttributes($order);
    }

    /**
     * @param int $gCardOrderId
     *
     * @return Order|MockObject
     */
    protected function initOrderWithExtension($gCardOrderId = 0)
    {
        $extensionAttributes = $this->createPartialMock(
            OrderExtension::class,
            ['setAmGiftcardOrder', 'getAmGiftcardOrder']
        );
        $order = $this->createPartialMock(Order::class, ['getExtensionAttributes', 'setExtensionAttributes']);
        $order->expects($this->any())->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $order->setId(self::ORDER_ID);
        $this->gCardOrder = $this->createPartialMock(GiftCardOrder::class, []);
        $this->gCardOrder->setOrderId($gCardOrderId);

        $extensionAttributes->expects($this->once())->method('setAmGiftcardOrder')->with($this->gCardOrder);
        $order->expects($this->once())->method('setExtensionAttributes')->with($extensionAttributes);

        return $order;
    }
}
