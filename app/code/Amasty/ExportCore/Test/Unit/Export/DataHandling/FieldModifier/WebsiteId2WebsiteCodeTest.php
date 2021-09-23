<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */

declare(strict_types=1);

namespace Amasty\ExportCore\Test\Unit\Export\DataHandling\FieldModifier;

use Amasty\ExportCore\Export\DataHandling\FieldModifier\WebsiteId2WebsiteCode;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers EavOptionValue2OptionLabel
 */
class WebsiteId2WebsiteCodeTest extends TestCase
{
    /**
     * Data provider for transform
     * @return array
     */
    public function transformDataProvider(): array
    {
        return [
            'default' => [
                [1 => 'default', 2 => 'custom'],
                1,
                'default'
            ],
            'custom' => [
                [1 => 'default', 2 => 'custom'],
                2,
                'custom'
            ],
            'empty_map' => [
                [],
                2,
                'custom'
            ]
        ];
    }

    /**
     * @param array $map
     * @param int $value
     * @param string $expectedResult
     *
     * @dataProvider transformDataProvider
     */
    public function testTransform(array $map, int $value, string $expectedResult)
    {
        $website = $this->createMock(WebsiteInterface::class);
        $website->method('getId')->willReturnOnConsecutiveCalls(1, 2);
        $website->method('getCode')->willReturnOnConsecutiveCalls('default', 'custom');
        $storeManager = $this->createMock(
            StoreManagerInterface::class
        );
        $storeManager->expects($this->any())
            ->method('getWebsites')
            ->willReturn([$website, $website]);

        $objectManager = new ObjectManager($this);
        $modifier = $objectManager->getObject(
            WebsiteId2WebsiteCode::class,
            [
                'storeManager' => $storeManager,
                'map' => $map
            ]
        );

        $this->assertSame($expectedResult, $modifier->transform($value));
    }
}
