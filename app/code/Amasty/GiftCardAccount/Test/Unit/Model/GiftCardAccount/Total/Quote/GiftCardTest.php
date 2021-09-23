<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


namespace Amasty\GiftCardAccount\Test\Unit\Model\GiftCardAccount\Total\Quote;

use Amasty\GiftCardAccount\Model\GiftCardAccount\Total\Quote\GiftCard;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\AllowedTotalCalculator;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\Quote as GiftCardQuote;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\CartExtension;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Shipping;
use Magento\Quote\Model\ShippingAssignment;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Amasty\GiftCardAccount\Model\GiftCardAccount\Total\Quote\GiftCard
 */
class GiftCardTest extends \PHPUnit\Framework\TestCase
{
    const TOTAL_CODE = 'amasty_giftcard';

    /**
     * @var GiftCard
     */
    private $giftCard;

    /**
     * @var Quote|MockObject
     */
    private $quote;

    /**
     * @var ShippingAssignment|MockObject
     */
    private $shippingAssigment;

    /**
     * @var Total|MockObject
     */
    private $total;

    /**
     * @var AllowedTotalCalculator|MockObject
     */
    private $allowedTotalCalculator;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->quote = $this->createPartialMock(
            Quote::class,
            ['getExtensionAttributes', 'setExtensionAttributes']
        );
        $this->shippingAssigment = $this->createPartialMock(ShippingAssignment::class, []);
        $this->total = $this->createPartialMock(Total::class, []);
        $this->allowedTotalCalculator = $this->createPartialMock(
            AllowedTotalCalculator::class,
            ['getAllowedBaseSubtotal', 'getAllowedSubtotal']
        );
        $this->giftCard = $objectManager->getObject(
            GiftCard::class,
            ['allowedTotalCalculator' => $this->allowedTotalCalculator]
        );
        $this->giftCard->setCode(self::TOTAL_CODE);
    }

    /**
     * @dataProvider collectDataProvider
     */
    public function testCollect($giftAmount, $subtotal, $cards, $expectedUsed)
    {
        $extensionAttributes = $this->createPartialMock(
            CartExtension::class,
            ['getAmGiftcardQuote', 'setAmGiftcardQuote', 'setAmAppliedGiftCards']
        );
        $gCardQuote = $this->createPartialMock(GiftCardQuote::class, []);
        $gCardQuote->setBaseGiftAmount($giftAmount);
        $gCardQuote->setBaseGiftAmountUsed(0);
        $gCardQuote->setGiftAmount($giftAmount);
        $gCardQuote->setGiftAmountUsed(0);
        $gCardQuote->setGiftCards($cards);

        $this->quote->expects($this->atLeastOnce())->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);
        $this->quote->expects($this->atLeastOnce())->method('setExtensionAttributes')->with($extensionAttributes);

        $extensionAttributes->expects($this->atLeastOnce())->method('getAmGiftcardQuote')
            ->willReturn($gCardQuote);

        $this->allowedTotalCalculator->expects($this->atLeastOnce())->method('getAllowedBaseSubtotal')
            ->with($this->total)
            ->willReturn($subtotal);
        $this->allowedTotalCalculator->expects($this->any())->method('getAllowedSubtotal')
            ->with($this->total)
            ->willReturn($subtotal);

        $this->giftCard->collect($this->quote, $this->shippingAssigment, $this->total);
        $this->assertEquals($expectedUsed, $gCardQuote->getBaseGiftAmountUsed());
    }

    public function testCollectNoExtensionAttributes()
    {
        $shipping = $this->createPartialMock(Shipping::class, []);
        $address = $this->createPartialMock(Address::class, []);
        $shipping->setAddress($address);

        $this->shippingAssigment->setShipping($shipping);
        $this->quote->expects($this->once())->method('getExtensionAttributes')->willReturn(null);

        $this->giftCard->collect($this->quote, $this->shippingAssigment, $this->total);
    }

    /**
     * @dataProvider fetchFromQuoteDataProvider
     */
    public function testFetchFromQuote($cards, $usedAmount, $expected)
    {
        $extensionAttributes = $this->createPartialMock(
            CartExtension::class,
            ['getAmGiftcardQuote']
        );
        $gCardQuote = $this->createPartialMock(GiftCardQuote::class, []);
        $gCardQuote->setGiftCards($cards);
        $gCardQuote->setGiftAmountUsed($usedAmount);

        $extensionAttributes->expects($this->atLeastOnce())->method('getAmGiftcardQuote')
            ->willReturn($gCardQuote);
        $this->quote->expects($this->atLeastOnce())->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        $this->assertEquals($expected, $this->giftCard->fetch($this->quote, $this->total));
    }

    public function testFetchFromQuoteNoExtension()
    {
        $extensionAttributes = $this->createPartialMock(
            CartExtension::class,
            ['getAmGiftcardQuote']
        );
        $extensionAttributes->expects($this->atLeastOnce())->method('getAmGiftcardQuote')
            ->willReturn(null);
        $this->quote->expects($this->atLeastOnce())->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        $this->assertNull($this->giftCard->fetch($this->quote, $this->total));
    }

    /**
     * @dataProvider fetchFromTotalDataProvider
     */
    public function testFetchFromTotal($cards, $expected)
    {
        $this->total->setAmGiftCards($cards);

        $this->assertEquals($expected, $this->giftCard->fetch($this->quote, $this->total));
    }

    /**
     * @return array
     */
    public function collectDataProvider()
    {
        return [
            [0, 50, [], 0],//no gift cards,
            [0, 50, [['amount' => 0, 'b_amount' => 0]], 0],//empty gift card
            [25, 45, [['amount' => 25, 'b_amount' => 25]], 25],//one gift card, less then subtotal
            [50, 45, [['amount' => 50, 'b_amount' => 50]], 45],//one gift card, more then subtotal
            [//two gift cards, more then subtotal
                75,
                45,
                [['amount' => 25, 'b_amount' => 25], ['amount' => 50, 'b_amount' => 50]],
                45
            ],
            [//two gift cards, first covers whole subtotal
                75,
                45,
                [['amount' => 50, 'b_amount' => 50], ['amount' => 25, 'b_amount' => 25]],
                45
            ]
        ];
    }

    /**
     * @return array
     */
    public function fetchFromQuoteDataProvider()
    {
        return [
            [[], 0, null],//no gift cards
            [//one card
                [['code' => 'card_1']],
                25,
                ['code' => self::TOTAL_CODE, 'title' => 'card_1', 'value' => -25]
            ],
            [//two cards
                [['code' => 'card_1'], ['code' => 'card_2']],
                50,
                [
                    'code' => self::TOTAL_CODE,
                    'title' => 'card_1, card_2',
                    'value' => -50
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function fetchFromTotalDataProvider()
    {
        return [
            [//one card
                [['code' => 'card_1', 'amount' => 25]],
                ['code' => self::TOTAL_CODE, 'title' => 'card_1', 'value' => -25]
            ],
            [//two cards
                [['code' => 'card_1', 'amount' => 25], ['code' => 'card_2', 'amount' => 10]],
                [
                    'code' => self::TOTAL_CODE,
                    'title' => 'card_1, card_2',
                    'value' => -35
                ]
            ]
        ];
    }
}
