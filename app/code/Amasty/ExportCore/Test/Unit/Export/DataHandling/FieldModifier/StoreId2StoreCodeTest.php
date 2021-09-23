<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */

declare(strict_types=1);

namespace Amasty\ExportCore\Test\Unit\Export\DataHandling\FieldModifier;

use Amasty\ExportCore\Export\DataHandling\FieldModifier\StoreId2StoreCode;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Amasty\ExportCore\Export\DataHandling\FieldModifier\StoreId2StoreCode
 */
class StoreId2StoreCodeTest extends TestCase
{
    /**
     * Data provider for transform
     * @return array
     */
    public function validateDataProvider(): array
    {
        return [
            'basic' => [
                1,
                'default'
            ],
            'custom' => [
                2,
                'custom'
            ],
            'array' => [
                [1, 2],
                ['default', 'custom']
            ],
            'all' => [
                0,
                'all'
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
        $store->expects($this->any())->method('getId')->willReturnOnConsecutiveCalls(1, 2);
        $store->expects($this->any())->method('getCode')
            ->willReturnOnConsecutiveCalls('default', 'custom');
        $storeManager = $this->createMock(
            StoreManagerInterface::class
        );
        $storeManager->expects($this->once())
            ->method('getStores')
            ->willReturn([$store, $store]);

        $objectManager = new ObjectManager($this);
        $modifier = $objectManager->getObject(
            StoreId2StoreCode::class,
            [
                'storeManager' => $storeManager,
                'config' => []
            ]
        );
        $this->assertSame($expectedResult, $modifier->transform($value));
    }
}
