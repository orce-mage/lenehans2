<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Test\Unit\FieldModifier;

use Amasty\ImportCore\Import\DataHandling\FieldModifier\Map as MapModifier;

/**
 * @covers \Amasty\ImportCore\Import\DataHandling\FieldModifier\Map
 */
class MapTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Data provider for transform
     * @return array
     */
    public function validateDataProvider(): array
    {
        return [
            'empty' => [
                [MapModifier::IS_MULTIPLE => false],
                'test',
                'test'
            ],
            'simple_test_match' => [
                [
                    MapModifier::IS_MULTIPLE => false,
                    MapModifier::MAP => [
                        'simple' => 'Simple Product',
                        'configurable' => 'Configurable Product',
                    ]
                ],
                'simple',
                'Simple Product'
            ],
            'simple_test_mismatch' => [
                [
                    MapModifier::IS_MULTIPLE => false,
                    MapModifier::MAP => [
                        'simple' => 'Simple Product',
                        'configurable' => 'Configurable Product',
                    ]
                ],
                'bundle',
                'bundle'
            ],
            'simple_test_multiple' => [
                [
                    MapModifier::IS_MULTIPLE => true,
                    MapModifier::MAP => [
                        'simple' => 'Simple Product',
                        'configurable' => 'Configurable Product',
                    ]
                ],
                'simple,configurable',
                'Simple Product,Configurable Product'
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
        $modifier = new MapModifier($config);
        $this->assertSame($expectedResult, $modifier->transform($value));
    }
}
