<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


namespace Amasty\GiftCardAccount\Test\Unit\Model\GiftCardExtension\Order\Handlers;

use Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Handlers\SaveHandler;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Order as GiftCardOrder;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Repository;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderExtension;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Handlers\SaveHandler
 */
class SaveHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SaveHandler
     */
    private $saveHandler;

    /**
     * @var Repository|MockObject
     */
    private $gCardOrderRepository;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->gCardOrderRepository = $this->createPartialMock(Repository::class, ['save', 'delete']);

        $this->saveHandler = $objectManager->getObject(
            SaveHandler::class,
            [
                'repository' => $this->gCardOrderRepository
            ]
        );
    }

    /**
     * @dataProvider saveAttributesDataProvider
     */
    public function testSaveAttributes($gCardOrder, $expectedSaveCount, $expectedDeleteCount)
    {
        $extensionAttributes = $this->createPartialMock(
            OrderExtension::class,
            ['getAmGiftcardOrder']
        );
        $extensionAttributes->expects($this->any())->method('getAmGiftcardOrder')
            ->willReturn($gCardOrder);
        $order = $this->createPartialMock(Order::class, ['getExtensionAttributes']);
        $order->expects($this->any())->method('getExtensionAttributes')->willReturn($extensionAttributes);

        $this->gCardOrderRepository->expects($this->exactly($expectedSaveCount))->method('save');
        $this->gCardOrderRepository->expects($this->exactly($expectedDeleteCount))->method('delete');

        $this->saveHandler->saveAttributes($order);
    }

    /**
     * @return array
     */
    public function saveAttributesDataProvider()
    {
        $gCardOrderA = $this->createPartialMock(GiftCardOrder::class, []);
        $gCardOrderA->setGiftCards(['test']);

        $gCardOrderB = $this->createPartialMock(GiftCardOrder::class, []);
        $gCardOrderB->setEntityId(1);

        return [
            [$gCardOrderA, 1, 0],//order exist, with gift cards
            [$gCardOrderB, 0, 1],//order exist, without gift cards
            [null, 0, 0],//order doesn't exist
        ];
    }
}
