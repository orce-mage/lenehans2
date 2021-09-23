<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


namespace Amasty\GiftCardAccount\Test\Unit\Model\GiftCardExtension\Quote\Handlers;

use Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\Handlers\ReadHandler;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\Quote as GiftCardQuote;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\Repository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartExtension;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\Handlers\ReadHandler
 */
class ReadHandlerTest extends \PHPUnit\Framework\TestCase
{
    const QUOTE_ID = 1;

    /**
     * @var ReadHandler
     */
    private $readHandler;

    /**
     * @var Repository|MockObject
     */
    private $gCardQuoteRepository;

    /**
     * @var GiftCardQuote
     */
    private $gCardQuote;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->gCardQuoteRepository = $this->createPartialMock(
            Repository::class,
            ['getByQuoteId', 'getEmptyQuoteModel']
        );
        $this->readHandler = $objectManager->getObject(
            ReadHandler::class,
            [
                'repository' => $this->gCardQuoteRepository
            ]
        );
    }

    public function testLoadAttributesNewQuote()
    {
        $quote = $this->initQuoteWithExtension();

        $this->gCardQuoteRepository->expects($this->once())->method('getByQuoteId')
            ->with(self::QUOTE_ID)
            ->willThrowException(new NoSuchEntityException());
        $this->gCardQuoteRepository->expects($this->once())->method('getEmptyQuoteModel')
            ->willReturn($this->gCardQuote);

        $this->readHandler->loadAttributes($quote);
        $this->assertEquals(self::QUOTE_ID, $this->gCardQuote->getQuoteId());
    }

    public function testLoadAttributesExistingQuote()
    {
        $quote = $this->initQuoteWithExtension(self::QUOTE_ID);

        $this->gCardQuoteRepository->expects($this->once())->method('getByQuoteId')
            ->with(self::QUOTE_ID)
            ->willReturn($this->gCardQuote);
        $this->gCardQuoteRepository->expects($this->never())->method('getEmptyQuoteModel');

        $this->readHandler->loadAttributes($quote);
        $this->assertEquals(self::QUOTE_ID, $this->gCardQuote->getQuoteId());
    }

    public function testLoadAttributesWithLoadedExtension()
    {
        $this->gCardQuote = $this->createPartialMock(GiftCardQuote::class, []);
        $extensionAttributes = $this->createPartialMock(
            CartExtension::class,
            ['getAmGiftcardQuote']
        );
        $extensionAttributes->expects($this->atLeastOnce())->method('getAmGiftcardQuote')
            ->willReturn($this->gCardQuote);
        $quote = $this->createPartialMock(Quote::class, ['getExtensionAttributes']);
        $quote->expects($this->any())->method('getExtensionAttributes')->willReturn($extensionAttributes);

        $this->gCardQuoteRepository->expects($this->never())->method('getByQuoteId');
        $this->readHandler->loadAttributes($quote);
    }

    /**
     * @param int $gCardQuoteId
     *
     * @return Quote|MockObject
     */
    protected function initQuoteWithExtension($gCardQuoteId = 0)
    {
        $extensionAttributes = $this->createPartialMock(
            CartExtension::class,
            ['setAmGiftcardQuote', 'getAmGiftcardQuote']
        );
        $quote = $this->createPartialMock(Quote::class, ['getExtensionAttributes', 'setExtensionAttributes']);
        $quote->expects($this->any())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $quote->setId(self::QUOTE_ID);
        $this->gCardQuote = $this->createPartialMock(GiftCardQuote::class, []);
        $this->gCardQuote->setQuoteId($gCardQuoteId);

        $extensionAttributes->expects($this->once())->method('setAmGiftcardQuote')->with($this->gCardQuote);
        $quote->expects($this->once())->method('setExtensionAttributes')->with($extensionAttributes);

        return $quote;
    }
}
