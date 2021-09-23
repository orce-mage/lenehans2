<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */

declare(strict_types=1);

namespace Amasty\ExportCore\Test\Unit\Export\DataHandling\FieldModifier;

use Amasty\ExportCore\Export\DataHandling\FieldModifier\Date;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Amasty\ExportCore\Export\DataHandling\FieldModifier\Date
 */
class DateTest extends TestCase
{
    /**
     * Data provider for transform
     * @return array
     */
    public function validateDataProvider(): array
    {
        return [
            'default' => [
                ['input_value' => 'd.m.y'],
                '01-01-2020',
                '01.01.20'
            ],
            'empty' => [
                ['input_value' => ''],
                '02-02-2020',
                ''
            ],
            'null' => [
                ['input_value' => null],
                '03-03-2020',
                '03-03-2020'
            ],
            'mysql' => [
                ['input_value' => 'Y-m-d H:i:s'],
                '04-04-2020',
                '2020-04-04 00:00:00'
            ],
            'exception' => [
                ['input_value' => 'Y-m-d H:i:s'],
                '123',
                '123'
            ]
        ];
    }

    /**
     * @param array $config
     * @param string $value
     * @param string $expectedResult
     * @dataProvider validateDataProvider
     */
    public function testTransform(array $config, string $value, string $expectedResult)
    {
        $objectManager = new ObjectManager($this);
        $modifier = $objectManager->getObject(Date::class, ['config' => $config]);
        $this->assertSame($expectedResult, $modifier->transform($value));
    }
}
