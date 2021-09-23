<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Test\Unit\Model\GiftCard\Quote\Item;

use Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
use Amasty\GiftCard\Api\Data\GiftCardOptionInterfaceFactory;
use Amasty\GiftCard\Model\GiftCard\GiftCardOption;
use Amasty\GiftCard\Model\GiftCard\Quote\Item\CartItemProcessor;
use Magento\Framework\DataObject\Factory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Api\Data\ProductOptionExtension;
use Magento\Quote\Model\Quote\Item\Option;
use Magento\Quote\Model\Quote\ProductOption;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @see CartItemProcessor
 */
class CartItemProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CartItemProcessor
     */
    private $cartItemProcessor;

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
    private $giftcardOptionFactory;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->objectFactory = $this->createPartialMock(Factory::class, ['create']);
        $this->extensionAttributes = $this->createPartialMock(
            ProductOptionExtension::class,
            ['getAmGiftcardOptions', 'setAmGiftcardOptions']
        );
        $this->giftcardOptionFactory = $this->createPartialMock(GiftCardOptionInterfaceFactory::class, ['create']);

        $this->cartItemProcessor = $objectManager->getObject(
            CartItemProcessor::class,
            [
                'objectFactory' => $this->objectFactory,
                'giftCardOptionInterfaceFactory' => $this->giftcardOptionFactory
            ]
        );
    }

    /**
     * @covers \Amasty\GiftCard\Model\GiftCard\Quote\Item\CartItemProcessor::convertToBuyRequest
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
        $productOption->expects($this->any())->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributes);
        $this->extensionAttributes->expects($this->any())->method('getAmGiftcardOptions')
            ->willReturn($gCardOption);

        /** @var Item|MockObject $cartItem */
        $cartItem = $this->createPartialMock(Item::class, ['getProductOption']);
        $cartItem->expects($this->once())->method('getProductOption')->willReturn($productOption);

        $this->objectFactory->expects($this->once())->method('create')->with($data);
        $this->cartItemProcessor->convertToBuyRequest($cartItem);
    }

    /**
     * @covers \Amasty\GiftCard\Model\GiftCard\Quote\Item\CartItemProcessor::convertToBuyRequest
     */
    public function testConverToBuyRequestNoProductOption()
    {
        /** @var Item|MockObject $cartItem */
        $cartItem = $this->createPartialMock(Item::class, ['getProductOption']);
        $cartItem->expects($this->once())->method('getProductOption')->willReturn(null);
        $this->objectFactory->expects($this->never())->method('create');

        $this->assertNull($this->cartItemProcessor->convertToBuyRequest($cartItem));
    }

    /**
     * @covers \Amasty\GiftCard\Model\GiftCard\Quote\Item\CartItemProcessor::processOptions
     */
    public function testProcessOptions()
    {
        $option = $this->createPartialMock(Option::class, []);
        $option->setCode(GiftCardOptionInterface::GIFTCARD_AMOUNT);
        $option->setValue(25);

        $gCardOption = $this->createPartialMock(GiftCardOption::class, []);
        $this->giftcardOptionFactory->expects($this->once())->method('create')->willReturn($gCardOption);
        $productOption = $this->createPartialMock(
            ProductOption::class,
            ['getExtensionAttributes', 'setExtensionAttributes']
        );
        $productOption->expects($this->atLeastOnce())->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributes);
        $this->extensionAttributes->expects($this->once())->method('setAmGiftcardOptions')
            ->with($gCardOption);
        $productOption->expects($this->once())->method('setExtensionAttributes')
            ->with($this->extensionAttributes);
        /** @var Item|MockObject $cartItem */
        $cartItem = $this->createPartialMock(
            Item::class,
            ['getOptions', 'getProductOption', 'setProductOption']
        );
        $cartItem->expects($this->once())->method('getOptions')->willReturn([$option]);
        $cartItem->expects($this->atLeastOnce())->method('getProductOption')->willReturn($productOption);
        $cartItem->expects($this->once())->method('setProductOption')->with($productOption);

        $this->assertEquals($cartItem, $this->cartItemProcessor->processOptions($cartItem));
    }

    public function testProcessOptionsWithoutOptions()
    {
        $cartItem = $this->createPartialMock(
            Item::class,
            ['getOptions']
        );
        $cartItem->expects($this->once())->method('getOptions')->willReturn(null);

        $this->assertEquals($cartItem, $this->cartItemProcessor->processOptions($cartItem));
    }
}
