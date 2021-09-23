<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */

declare(strict_types=1);

namespace Amasty\ExportCore\Test\Unit\Export\DataHandling\FieldModifier;

use Amasty\ExportCore\Export\DataHandling\FieldModifier\DefaultValue;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Amasty\ExportCore\Export\DataHandling\FieldModifier\DefaultValue
 */
class DefaultValueTest extends TestCase
{
    /**
     * Data provider for transform
     * @return array
     */
    public function validateDataProvider(): array
    {
        return [
            'default' => [
                ['input_value' => 'test'],
                'test',
                'test'
            ],
            'empty' => [
                ['input_value' => ''],
                'test',
                ''
            ],
            'null' => [
                ['input_value' => null],
                'test',
                'test'
            ],
            'number' => [
                ['input_value' => 123],
                123,
                123
            ],
            'without_key' => [
                ['test_key' => 'test_value'],
                'text',
                'text'
            ]
        ];
    }

    /**
     * @param array $config
     * @param $value
     * @param $expectedResult
     * @dataProvider validateDataProvider
     */
    public function testTransform(array $config, $value, $expectedResult)
    {
        $objectManager = new ObjectManager($this);
        $modifier = $objectManager->getObject(DefaultValue::class, ['config' => $config]);
        $this->assertSame($expectedResult, $modifier->transform($value));
    }
}
