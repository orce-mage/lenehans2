<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


namespace Amasty\GiftCardAccount\Test\Unit\Model\GiftCardExtension\Quote\Handlers;

use Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\Handlers\SaveHandler;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\Quote as GiftCardQuote;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\Repository;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\CartExtension;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\Handlers\SaveHandler
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
    private $gCardQuoteRepository;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->gCardQuoteRepository = $this->createPartialMock(Repository::class, ['save', 'delete']);

        $this->saveHandler = $objectManager->getObject(
            SaveHandler::class,
            [
                'repository' => $this->gCardQuoteRepository
            ]
        );
    }

    /**
     * @dataProvider saveAttributesDataProvider
     */
    public function testSaveAttributes($gCardQuote, $expectedSaveCount, $expectedDeleteCount)
    {
        $extensionAttributes = $this->createPartialMock(
            CartExtension::class,
            ['getAmGiftcardQuote']
        );
        $extensionAttributes->expects($this->any())->method('getAmGiftcardQuote')
            ->willReturn($gCardQuote);
        $quote = $this->createPartialMock(Quote::class, ['getExtensionAttributes']);
        $quote->expects($this->any())->method('getExtensionAttributes')->willReturn($extensionAttributes);

        $this->gCardQuoteRepository->expects($this->exactly($expectedSaveCount))->method('save');
        $this->gCardQuoteRepository->expects($this->exactly($expectedDeleteCount))->method('delete');

        $this->saveHandler->saveAttributes($quote);
    }

    /**
     * @return array
     */
    public function saveAttributesDataProvider()
    {
        $gCardQuoteA = $this->createPartialMock(GiftCardQuote::class, []);
        $gCardQuoteA->setGiftCards(['test']);

        $gCardQuoteB = $this->createPartialMock(GiftCardQuote::class, []);
        $gCardQuoteB->setEntityId(1);

        return [
            [$gCardQuoteA, 1, 0],//quote exist, with gift cards
            [$gCardQuoteB, 0, 1],//quote exist, without gift cards
            [null, 0, 0],//quote doesn't exist
        ];
    }
}
