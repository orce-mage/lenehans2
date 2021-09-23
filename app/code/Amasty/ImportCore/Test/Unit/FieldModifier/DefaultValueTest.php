<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Test\Unit\FieldModifier;

/**
 * @covers \Amasty\ImportCore\Import\DataHandling\FieldModifier\DefaultValue
 */
class DefaultValueTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Data provider for transform
     * @return array
     */
    public function validateDataProvider(): array
    {
        return [
            'basic' => [
                ['value' => '5', 'force' => false],
                '',
                "5"
            ],
            'null' => [
                ['value' => '6', 'force' => false],
                null,
                '6'
            ],
            'force' => [
                ['value' => '7', 'force' => true],
                'test',
                '7'
            ],
            'notforce' => [
                ['value' => '6', 'force' => false],
                'test',
                'test'
            ],
            'empty_array' => [
                ['value' => '5', 'force' => true],
                [],
                '5'
            ]
        ];
    }

    /**
     * @param $config
     * @param $value
     * @param $expectedResult
     * @dataProvider validateDataProvider
     */
    public function testTransform(array $config, $value, $expectedResult)
    {
        $modifier = new \Amasty\ImportCore\Import\DataHandling\FieldModifier\DefaultValue($config);
        $this->assertSame($expectedResult, $modifier->transform($value));
    }

    /**
     * @param $config
     * @param $value
     * @param $expectedResult
     * @dataProvider validateDataProvider
     */
    public function testTransformException()
    {
        $this->expectException(\LogicException::class);
        new \Amasty\ImportCore\Import\DataHandling\FieldModifier\DefaultValue([]);
    }
}
