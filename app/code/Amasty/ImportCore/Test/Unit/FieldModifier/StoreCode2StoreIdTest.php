<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Test\Unit\FieldModifier;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @covers \Amasty\ImportCore\Import\DataHandling\FieldModifier\StoreCode2StoreId
 */
class StoreCode2StoreIdTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Data provider for transform
     * @return array
     */
    public function validateDataProvider(): array
    {
        return [
            'basic' => [
                'default',
                1
            ],
            'custom' => [
                'custom',
                2
            ],
            'array' => [
                ['default', 'custom'],
                [1, 2]
            ],
            'all' => [
                'all',
                null
            ]
        ];
    }

    /**
     * @param $value
     * @param $expectedResult
     * @dataProvider validateDataProvider
     */
    public function testTransform($value, $expectedResult)
    {
        $store = $this->createMock(StoreInterface::class);
        $store->method('getId')->willReturnOnConsecutiveCalls(1, 2);
        $store->method('getCode')->willReturnOnConsecutiveCalls('default', 'custom');
        $storeManager = $this->createMock(
            StoreManagerInterface::class
        );
        $storeManager->method('getStores')->willReturn([$store, $store]);
        $modifier = new \Amasty\ImportCore\Import\DataHandling\FieldModifier\StoreCode2StoreId($storeManager);
        $this->assertSame($expectedResult, $modifier->transform($value));
    }
}
