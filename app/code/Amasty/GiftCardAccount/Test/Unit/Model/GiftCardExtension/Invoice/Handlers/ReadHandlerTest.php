<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


namespace Amasty\GiftCardAccount\Test\Unit\Model\GiftCardExtension\Invoice\Handlers;

use Amasty\GiftCardAccount\Model\GiftCardExtension\Invoice\Handlers\ReadHandler;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Invoice\Invoice as GiftCardInvoice;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Invoice\Repository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\InvoiceExtension;
use Magento\Sales\Model\Order\Invoice;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Amasty\GiftCardAccount\Model\GiftCardExtension\Invoice\Handlers\ReadHandler
 */
class ReadHandlerTest extends \PHPUnit\Framework\TestCase
{
    const INVOICE_ID = 1;

    /**
     * @var ReadHandler
     */
    private $readHandler;

    /**
     * @var Repository|MockObject
     */
    private $gCardInvoiceRepository;

    /**
     * @var GiftCardInvoice
     */
    private $gCardInvoice;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->gCardInvoiceRepository = $this->createPartialMock(
            Repository::class,
            ['getByInvoiceId', 'getEmptyInvoiceModel']
        );
        $this->readHandler = $objectManager->getObject(
            ReadHandler::class,
            [
                'repository' => $this->gCardInvoiceRepository
            ]
        );
    }

    public function testLoadAttributesNewQuote()
    {
        $invoice = $this->initInvoiceWithExtension();

        $this->gCardInvoiceRepository->expects($this->once())->method('getByInvoiceId')
            ->with(self::INVOICE_ID)
            ->willThrowException(new NoSuchEntityException());
        $this->gCardInvoiceRepository->expects($this->once())->method('getEmptyInvoiceModel')
            ->willReturn($this->gCardInvoice);

        $this->readHandler->loadAttributes($invoice);
        $this->assertEquals(self::INVOICE_ID, $this->gCardInvoice->getInvoiceId());
    }

    public function testLoadAttributesExistingQuote()
    {
        $invoice = $this->initInvoiceWithExtension(self::INVOICE_ID);

        $this->gCardInvoiceRepository->expects($this->once())->method('getByInvoiceId')
            ->with(self::INVOICE_ID)
            ->willReturn($this->gCardInvoice);
        $this->gCardInvoiceRepository->expects($this->never())->method('getEmptyInvoiceModel');

        $this->readHandler->loadAttributes($invoice);
        $this->assertEquals(self::INVOICE_ID, $this->gCardInvoice->getInvoiceId());
    }

    public function testLoadAttributesWithLoadedExtension()
    {
        $this->gCardInvoice = $this->createPartialMock(GiftCardInvoice::class, []);
        $extensionAttributes = $this->createPartialMock(
            InvoiceExtension::class,
            ['getAmGiftcardInvoice']
        );
        $extensionAttributes->expects($this->atLeastOnce())->method('getAmGiftcardInvoice')
            ->willReturn($this->gCardInvoice);
        $invoice = $this->createPartialMock(Invoice::class, ['getExtensionAttributes']);
        $invoice->expects($this->any())->method('getExtensionAttributes')->willReturn($extensionAttributes);

        $this->gCardInvoiceRepository->expects($this->never())->method('getByInvoiceId');
        $this->readHandler->loadAttributes($invoice);
    }

    /**
     * @param int $gCardInvoiceId
     *
     * @return Invoice|MockObject
     */
    protected function initInvoiceWithExtension($gCardInvoiceId = 0)
    {
        $extensionAttributes = $this->createPartialMock(
            InvoiceExtension::class,
            ['setAmGiftcardInvoice', 'getAmGiftcardInvoice']
        );
        $invoice = $this->createPartialMock(Invoice::class, ['getExtensionAttributes', 'setExtensionAttributes']);
        $invoice->expects($this->any())->method('getExtensionAttributes')->willReturn($extensionAttributes);
        $invoice->setId(self::INVOICE_ID);
        $this->gCardInvoice = $this->createPartialMock(GiftCardInvoice::class, []);
        $this->gCardInvoice->setInvoiceId($gCardInvoiceId);

        $extensionAttributes->expects($this->once())->method('setAmGiftcardInvoice')->with($this->gCardInvoice);
        $invoice->expects($this->once())->method('setExtensionAttributes')->with($extensionAttributes);

        return $invoice;
    }
}
