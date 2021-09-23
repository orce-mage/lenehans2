<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Test\Unit\FieldModifier;

/**
 * @covers \Amasty\ImportCore\Import\DataHandling\FieldModifier\Explode
 */
class ExplodeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Data provider for transform
     * @return array
     */
    public function validateDataProvider(): array
    {
        return [
            'basic' => [
                ['separator' => ','],
                'test,test2,test3,test4',
                [
                    'test',
                    'test2',
                    'test3',
                    'test4'
                ]
            ],
            'array_as_value' => [
                ['separator' => ','],
                [
                    'test',
                    'test2',
                    'test3',
                    'test4'
                ],
                [
                    'test',
                    'test2',
                    'test3',
                    'test4'
                ]
            ],
            'separator_test' => [
                ['separator' => '|'],
                'test|test2|test3|test4',
                [
                    'test',
                    'test2',
                    'test3',
                    'test4'
                ]
            ],
            'string_without_separator_test' => [
                ['separator' => ','],
                'test|test2',
                [
                    'test|test2'
                ]
            ],
            'empty_config' => [
                [],
                'test,test2',
                [
                    'test',
                    'test2'
                ]
            ],
            'first_last_separator' => [
                [],
                ',test,test2,',
                [
                    'test',
                    'test2'
                ]
            ],
            'empty_separator_value' => [
                [],
                'test,,test2',
                [
                    'test',
                    '',
                    'test2'
                ]
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
        $modifier = new \Amasty\ImportCore\Import\DataHandling\FieldModifier\Explode($config);
        $this->assertSame($expectedResult, $modifier->transform($value));
    }
}
