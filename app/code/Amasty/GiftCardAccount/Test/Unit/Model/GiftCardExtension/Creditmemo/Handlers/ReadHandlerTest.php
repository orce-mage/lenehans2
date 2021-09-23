<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


namespace Amasty\GiftCardAccount\Test\Unit\Model\GiftCardExtension\Creditmemo\Handlers;

use Amasty\GiftCardAccount\Model\GiftCardExtension\Creditmemo\Handlers\ReadHandler;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Creditmemo\Creditmemo as GiftCardMemo;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Creditmemo\Repository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\CreditmemoExtension;
use Magento\Sales\Model\Order\Creditmemo;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Amasty\GiftCardAccount\Model\GiftCardExtension\Creditmemo\Handlers\ReadHandler
 */
class ReadHandlerTest extends \PHPUnit\Framework\TestCase
{
    const CREDITMEMO_ID = 1;

    /**
     * @var ReadHandler
     */
    private $readHandler;

    /**
     * @var Repository|MockObject
     */
    private $gCardCreditmemoRepository;

    /**
     * @var GiftCardMemo
     */
    private $gCardCreditmemo;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->gCardCreditmemoRepository = $this->createPartialMock(
            Repository::class,
            ['getByCreditmemoId', 'getEmptyCreditmemoModel']
        );
        $this->readHandler = $objectManager->getObject(
            ReadHandler::class,
            [
                'repository' => $this->gCardCreditmemoRepository
            ]
        );
    }

    public function testLoadAttributesNewQuote()
    {
        $memo = $this->initCreditmemoWithExtension();

        $this->gCardCreditmemoRepository->expects($this->once())->method('getByCreditmemoId')
            ->with(self::CREDITMEMO_ID)
            ->willThrowException(new NoSuchEntityException());
        $this->gCardCreditmemoRepository->expects($this->once())->method('getEmptyCreditmemoModel')
            ->willReturn($this->gCardCreditmemo);

        $this->readHandler->loadAttributes($memo);
        $this->assertEquals(self::CREDITMEMO_ID, $this->gCardCreditmemo->getCreditmemoId());
    }

    public function testLoadAttributesExistingQuote()
    {
        $memo = $this->initCreditmemoWithExtension(self::CREDITMEMO_ID);

        $this->gCardCreditmemoRepository->expects($this->once())->method('getByCreditmemoId')
            ->with(self::CREDITMEMO_ID)
            ->willReturn($this->gCardCreditmemo);
        $this->gCardCreditmemoRepository->expects($this->never())->method('getEmptyCreditmemoModel');

        $this->readHandler->loadAttributes($memo);
        $this->assertEquals(self::CREDITMEMO_ID, $this->gCardCreditmemo->getCreditmemoId());
    }

    public function testLoadAttributesWithLoadedExtension()
    {
        $this->gCardCreditmemo = $this->createPartialMock(GiftCardMemo::class, []);
        $extensionAttributes = $this->createPartialMock(
            CreditmemoExtension::class,
            ['getAmGiftcardCreditmemo']
        );
        $extensionAttributes->expects($this->atLeastOnce())->method('getAmGiftcardCreditmemo')
            ->willReturn($this->gCardCreditmemo);
        $memo = $this->createPartialMock(Creditmemo::class, ['getExtensionAttributes']);
        $memo->expects($this->any())->method('getExtensionAttributes')->willReturn($extensionAttributes);

        $this->gCardCreditmemoRepository->expects($this->never())->method('getByCreditmemoId');
        $this->readHandler->loadAttributes($memo);
    }

    /**
     * @param int $gCardCreditmemoId
     *
     * @return Creditmemo|MockObject
     */
    protected function initCreditmemoWithExtension($gCardCreditmemoId = 0)
    {
        $extensionAttributes = $this->createPartialMock(
            CreditmemoExtension::class,
            ['setAmGiftcardCreditmemo', 'getAmGiftcardCreditmemo']
        );
        $memo = $this->createPartialMock(Creditmemo::class, ['getExtensionAttributes', 'setExtensionAttributes']);
        $memo->expects($this->any())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $memo->setId(self::CREDITMEMO_ID);
        $this->gCardCreditmemo = $this->createPartialMock(GiftCardMemo::class, []);
        $this->gCardCreditmemo->setCreditmemoId($gCardCreditmemoId);

        $extensionAttributes->expects($this->once())->method('setAmGiftcardCreditmemo')
            ->with($this->gCardCreditmemo);
        $memo->expects($this->once())->method('setExtensionAttributes')->with($extensionAttributes);

        return $memo;
    }
}
