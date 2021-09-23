<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


namespace Amasty\GiftCardAccount\Test\Unit\Model\GiftCardExtension\Creditmemo\Handlers;

use Amasty\GiftCardAccount\Model\GiftCardExtension\Creditmemo\Handlers\SaveHandler;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Creditmemo\Creditmemo as GiftCardMemo;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Creditmemo\Repository;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\CreditmemoExtension;
use Magento\Sales\Model\Order\Creditmemo;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Amasty\GiftCardAccount\Model\GiftCardExtension\Creditmemo\Handlers\SaveHandler
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
    private $gCardCreditmemoRepository;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->gCardCreditmemoRepository = $this->createPartialMock(Repository::class, ['save', 'delete']);

        $this->saveHandler = $objectManager->getObject(
            SaveHandler::class,
            [
                'repository' => $this->gCardCreditmemoRepository
            ]
        );
    }

    /**
     * @dataProvider saveAttributesDataProvider
     */
    public function testSaveAttributes($gCardCreditmemo, $expectedSaveCount)
    {
        $extensionAttributes = $this->createPartialMock(
            CreditmemoExtension::class,
            ['getAmGiftcardCreditmemo']
        );
        $extensionAttributes->expects($this->any())->method('getAmGiftcardCreditmemo')
            ->willReturn($gCardCreditmemo);
        $memo = $this->createPartialMock(Creditmemo::class, ['getExtensionAttributes']);
        $memo->expects($this->any())->method('getExtensionAttributes')->willReturn($extensionAttributes);

        $this->gCardCreditmemoRepository->expects($this->exactly($expectedSaveCount))->method('save');

        $this->saveHandler->saveAttributes($memo);
    }

    /**
     * @return array
     */
    public function saveAttributesDataProvider()
    {
        $gCardCreditmemoA = $this->createPartialMock(GiftCardMemo::class, []);
        $gCardCreditmemoA->setCreditmemoId(1);
        $gCardCreditmemoA->setGiftAmount(20);

        $gCardInvoiceB = $this->createPartialMock(GiftCardMemo::class, []);
        $gCardInvoiceB->setCreditmemoId(1);

        return [
            [$gCardCreditmemoA, 1],//creditmemo with gift cards
            [$gCardInvoiceB, 0],//creditmemo without gift cards
            [null, 0],//no extension attributes
        ];
    }
}
