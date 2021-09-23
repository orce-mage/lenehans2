<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Test\Unit\Model\GiftCard\Product;

use Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
use Amasty\GiftCard\Model\Config\Source\Fee;
use Amasty\GiftCard\Model\GiftCard\Product\Price;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\Option;
use Magento\Directory\Model\PriceCurrency;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Amasty\GiftCard\Model\GiftCard\Product\Price
 */
class PriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Price
     */
    private $price;

    /**
     * @var PriceCurrency|MockObject
     */
    private $priceCurrency;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->priceCurrency = $this->createPartialMock(PriceCurrency::class, ['round']);

        $this->price = $objectManager->getObject(
            Price::class,
            [
                'priceCurrency' => $this->priceCurrency
            ]
        );
    }

    /**
     * @dataProvider getPriceDataProvider
     */
    public function testGetPrice($amounts, $withCustomOption, $expected)
    {
        $product = $this->createPartialMock(Product::class, ['getAmAllowOpenAmount', 'hasCustomOptions']);
        $product->expects($this->once())->method('getAmAllowOpenAmount')->willReturn(false);
        $product->expects($this->any())->method('hasCustomOptions')->willReturn($withCustomOption);
        $product->addData([
            'am_giftcard_prices' => $amounts
        ]);

        $this->assertEquals($expected, $this->price->getPrice($product));
    }

    public function testGetFinalPrice()
    {
        $productPrice = 5.0;
        $optionPrice = 3.0;

        $product = $this->initProduct($productPrice, $optionPrice);

        $this->assertEquals($productPrice + $optionPrice, $this->price->getFinalPrice(3, $product));
    }

    public function testGetFinalPriceWithFee()
    {
        $productPrice = 5.0;
        $optionPrice = 3.0;
        $feeValue = 2.0;

        $product = $this->initProduct($productPrice, $optionPrice);
        $product->setAmGiftcardFeeEnable(true);
        $product->setAmGiftcardFeeType(Fee::PRICE_TYPE_FIXED);
        $product->setAmGiftcardFeeValue($feeValue);

        $this->priceCurrency->expects($this->once())->method('round')
            ->with($optionPrice + $feeValue)
            ->willReturn($optionPrice + $feeValue);

        $this->assertEquals($productPrice + $optionPrice + $feeValue, $this->price->getFinalPrice(3, $product));
    }

    /**
     * @param float $productPrice
     * @param float $optionPrice
     *
     * @return Product|MockObject
     */
    protected function initProduct($productPrice, $optionPrice)
    {
        $product = $this->createPartialMock(
            Product::class,
            ['getPrice', 'getCustomOption', 'hasCustomOptions']
        );
        $customOption = $this->createPartialMock(Option::class, []);
        $customOption->setValue($optionPrice);

        $product->expects($this->once())->method('getPrice')->willReturn($productPrice);
        $product->expects($this->once())->method('hasCustomOptions')->will($this->returnValue(true));
        $product->expects($this->at(2))->method('getCustomOption')
            ->with(GiftCardOptionInterface::GIFTCARD_AMOUNT)
            ->willReturn($customOption);
        $product->expects($this->at(3))->method('getCustomOption')
            ->with(GiftCardOptionInterface::GIFTCARD_AMOUNT)
            ->willReturn($customOption);
        $product->expects($this->at(4))
            ->method('getCustomOption')
            ->with('option_ids')
            ->will($this->returnValue(null));

        return $product;
    }

    /**
     * @return array
     */
    public function getPriceDataProvider()
    {
        return [
            [[['website_id' => 0, 'value' => '10.0000']], false, 10],
            [[['website_id' => 0, 'value' => '10.0000']], true, 0],
            [
                [
                    ['website_id' => 0, 'value' => '10.0000'],
                    ['website_id' => 0, 'value' => '100.0000'],
                ],
                false,
                0
            ],
        ];
    }
}
