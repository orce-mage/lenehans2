<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


namespace Amasty\GiftCardAccount\Test\Unit\Model\GiftCardExtension\Invoice\Handlers;

use Amasty\GiftCardAccount\Model\GiftCardExtension\Invoice\Handlers\SaveHandler;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Invoice\Invoice as GiftCardInvoice;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Invoice\Repository;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\InvoiceExtension;
use Magento\Sales\Model\Order\Invoice;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Amasty\GiftCardAccount\Model\GiftCardExtension\Invoice\Handlers\SaveHandler
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
    private $gCardInvoiceRepository;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->gCardInvoiceRepository = $this->createPartialMock(Repository::class, ['save']);

        $this->saveHandler = $objectManager->getObject(
            SaveHandler::class,
            [
                'repository' => $this->gCardInvoiceRepository
            ]
        );
    }

    /**
     * @dataProvider saveAttributesDataProvider
     */
    public function testSaveAttributes($gCardInvoice, $expectedSaveCount)
    {
        $extensionAttributes = $this->createPartialMock(
            InvoiceExtension::class,
            ['getAmGiftcardInvoice']
        );
        $extensionAttributes->expects($this->any())->method('getAmGiftcardInvoice')
            ->willReturn($gCardInvoice);
        $invoice = $this->createPartialMock(Invoice::class, ['getExtensionAttributes']);
        $invoice->expects($this->any())->method('getExtensionAttributes')->willReturn($extensionAttributes);

        $this->gCardInvoiceRepository->expects($this->exactly($expectedSaveCount))->method('save');

        $this->saveHandler->saveAttributes($invoice);
    }

    /**
     * @return array
     */
    public function saveAttributesDataProvider()
    {
        $gCardInvoiceA = $this->createPartialMock(GiftCardInvoice::class, []);
        $gCardInvoiceA->setInvoiceId(1);
        $gCardInvoiceA->setGiftAmount(20);

        $gCardInvoiceB = $this->createPartialMock(GiftCardInvoice::class, []);
        $gCardInvoiceB->setInvoiceId(1);

        return [
            [$gCardInvoiceA, 1],//invoice with gift cards
            [$gCardInvoiceB, 0],//invoice without gift cards
            [null, 0],//no extension attributes
        ];
    }
}
