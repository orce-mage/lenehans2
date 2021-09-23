<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


namespace Amasty\GiftCardAccount\Test\Unit\Model\GiftCardExtension\Quote;

use Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\AllowedTotalCalculator;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @covers \Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\AllowedTotalCalculator
 */
class AllowedTotalCalculatorTest extends \PHPUnit\Framework\TestCase
{
    const TOTALS_DATA = [
        'subtotal' => 44.99,
        'subtotal_incl_tax' => 48.71,
        'discount_amount' => -5.37,
        'subtotal_with_discount' => 39.63,
        'discount_tax_compensation_amount' => 0.37,
        'shipping_amount' => 5,
        'shipping_discount_tax_compensation_amount' => 0.03,
        'shipping_discount_amount' => 0.5,
        'tax_amount' => 3.72,
        'amasty_extrafee_amount' => 2.1,

        'base_subtotal' => 44.99,
        'base_subtotal_incl_tax' => 48.71,
        'base_discount_amount' => -5.37,
        'base_subtotal_with_discount' => 39.63,
        'base_discount_tax_compensation_amount' => 0.37,
        'base_shipping_amount' => 5,
        'base_shipping_discount_tax_compensation_amount' => 0.03,
        'base_shipping_discount_amount' => 0.5,
        'base_tax_amount' => 3.72
    ];

    /**
     * @var AllowedTotalCalculator
     */
    private $totalCalculator;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var DataObject
     */
    private $total;

    protected function setUp(): void
    {
        $this->total = new DataObject(
            self::TOTALS_DATA
        );
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * @dataProvider allowedTotalDataProvider
     */
    public function testGetAllowedTotals($taxAllowed, $shippingAllowed, $extraFeeAllowed, $expected)
    {
        $this->initTotalCalculator($taxAllowed, $shippingAllowed, $extraFeeAllowed);

        $this->assertEquals($expected, $this->totalCalculator->getAllowedSubtotal($this->total));
        $this->assertEquals($expected, $this->totalCalculator->getAllowedBaseSubtotal($this->total));
    }

    protected function initTotalCalculator($taxAllowed, $shippingAllowed, $extraFeeAllowed)
    {
        $this->totalCalculator = $this->objectManager->getObject(
            AllowedTotalCalculator::class,
            [
                'isShippingAllowed' => $shippingAllowed,
                'isTaxAllowed' => $taxAllowed,
                'isExtraFeeAllowed' => $extraFeeAllowed
            ]
        );
    }

    /**
     * @return array
     */
    public function allowedTotalDataProvider()
    {
        return [
            [true, true, true, 50.81],
            [false, true, true, 47.13],
            [true, false, false, 44.25],
            [false, false, false, 40.5]
        ];
    }
}
