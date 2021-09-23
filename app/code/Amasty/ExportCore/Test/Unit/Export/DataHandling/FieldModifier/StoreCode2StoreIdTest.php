<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */

declare(strict_types=1);

namespace Amasty\ExportCore\Test\Unit\Export\DataHandling\FieldModifier;

use Amasty\ExportCore\Export\DataHandling\FieldModifier\StoreCode2StoreId;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Amasty\ExportCore\Export\DataHandling\FieldModifier\StoreCode2StoreId
 */
class StoreCode2StoreIdTest extends TestCase
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
                0
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
            StoreCode2StoreId::class,
            [
                'storeManager' => $storeManager,
                'config' => []
            ]
        );
        $this->assertSame($expectedResult, $modifier->transform($value));
    }
}
