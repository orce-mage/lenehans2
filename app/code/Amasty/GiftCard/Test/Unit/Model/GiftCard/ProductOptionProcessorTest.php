<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Test\Unit\Model\GiftCard;

use Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
use Amasty\GiftCard\Model\GiftCard\GiftCardOption;
use Amasty\GiftCard\Model\GiftCard\ProductOptionProcessor;
use Magento\Catalog\Model\ProductOption;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\ProductOptionExtension;
use PHPUnit\Framework\MockObject\MockObject;
use Amasty\GiftCard\Api\Data\GiftCardOptionInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class ProductOptionProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductOptionProcessor
     */
    private $productOptionProcessor;

    /**
     * @var Factory|MockObject
     */
    private $objectFactory;

    /**
     * @var ProductOptionExtension|MockObject
     */
    private $extensionAttributes;

    /**
     * @var GiftCardOptionInterfaceFactory|MockObject
     */
    private $gCardOptionFactory;

    /**
     * @var DataObjectHelper|MockObject
     */
    private $dataObjectHelper;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->objectFactory = $this->createPartialMock(Factory::class, ['create']);
        $this->extensionAttributes = $this->createPartialMock(
            ProductOptionExtension::class,
            ['getAmGiftcardOptions', 'setAmGiftcardOptions']
        );
        $this->gCardOptionFactory = $this->createPartialMock(GiftCardOptionInterfaceFactory::class, ['create']);
        $this->dataObjectHelper = $this->createPartialMock(DataObjectHelper::class, ['populateWithArray']);

        $this->productOptionProcessor = $objectManager->getObject(
            ProductOptionProcessor::class,
            [
                'objectFactory' => $this->objectFactory,
                'giftCardOptionFactory' => $this->gCardOptionFactory,
                'dataObjectHelper' => $this->dataObjectHelper
            ]
        );
    }

    /**
     * @covers \Amasty\GiftCard\Model\GiftCard\ProductOptionProcessor::convertToBuyRequest
     */
    public function testConvertToBuyRequest()
    {
        $data = [
            GiftCardOptionInterface::GIFTCARD_AMOUNT => 'custom',
            GiftCardOptionInterface::CUSTOM_GIFTCARD_AMOUNT => 15
        ];
        $gCardOption = $this->createPartialMock(GiftCardOption::class, []);
        $gCardOption->setData($data);
        $productOption = $this->createPartialMock(ProductOption::class, ['getExtensionAttributes']);
        $productOption->expects($this->atLeastOnce())->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributes);
        $this->extensionAttributes->expects($this->atLeastOnce())->method('getAmGiftcardOptions')
            ->willReturn($gCardOption);

        $request = $this->createPartialMock(DataObject::class, ['addData']);
        $request->expects($this->once())->method('addData')->with($data);
        $this->objectFactory->expects($this->once())->method('create')->willReturn($request);

        $this->assertEquals($request, $this->productOptionProcessor->convertToBuyRequest($productOption));
    }

    /**
     * @covers \Amasty\GiftCard\Model\GiftCard\ProductOptionProcessor::convertToBuyRequest
     */
    public function testConvertToBuyRequestNoOption()
    {
        $request = $this->createPartialMock(DataObject::class, []);
        $this->objectFactory->expects($this->once())->method('create')->willReturn($request);

        $productOption = $this->createPartialMock(ProductOption::class, ['getExtensionAttributes']);
        $productOption->expects($this->atLeastOnce())->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributes);
        $this->extensionAttributes->expects($this->atLeastOnce())->method('getAmGiftcardOptions')
            ->willReturn(null);

        $this->assertEquals($request, $this->productOptionProcessor->convertToBuyRequest($productOption));
    }

    /**
     * @covers \Amasty\GiftCard\Model\GiftCard\ProductOptionProcessor::convertToProductOption
     */
    public function testConvertToProductOption()
    {
        $data = [
            GiftCardOptionInterface::GIFTCARD_AMOUNT => 'custom',
            GiftCardOptionInterface::CUSTOM_GIFTCARD_AMOUNT => 15
        ];
        $request = $this->createPartialMock(DataObject::class, []);
        $request->setData($data);
        $gCardOption = $this->createPartialMock(GiftCardOption::class, []);
        $this->gCardOptionFactory->expects($this->once())->method('create')->willReturn($gCardOption);

        $this->dataObjectHelper->expects($this->once())->method('populateWithArray')
            ->with($gCardOption, $data, GiftCardOptionInterface::class);

        $result = $this->productOptionProcessor->convertToProductOption($request);
        $this->assertEquals(['am_giftcard_options' => $gCardOption], $result);
    }

    /**
     * @covers \Amasty\GiftCard\Model\GiftCard\ProductOptionProcessor::convertToProductOption
     */
    public function testConvertToProductOptionNoRequestData()
    {
        $request = $this->createPartialMock(DataObject::class, []);
        $this->gCardOptionFactory->expects($this->never())->method('create');

        $this->assertEquals([], $this->productOptionProcessor->convertToProductOption($request));
    }
}
