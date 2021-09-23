<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Test\Unit\Import\Action\DataPrepare;

use Amasty\ImportCore\Import\Action\DataPrepare\Mapping\Mapper;

/**
 * @covers \Amasty\ImportCore\Import\Action\DataPrepare\Mapping\Mapper
 */
class MapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * data provider for mapData test
     * @return array
     */
    public function mapDataProvider(): array
    {
        return [
            'basic_test' => [
                [
                    ['a', 'b', 'c'],
                    ['d', 'e', 'f']
                ],
                [[0 => 'name']],
                [
                    ['name' => 'a', 1 => 'b', 2 => 'c'],
                    ['name' => 'd', 1 => 'e', 2 => 'f']
                ],
            ],
            'no_mapping' => [
                [['a', 'b', 'c'], ['d', 'e', 'f']],
                [],
                [['a', 'b', 'c'], ['d', 'e', 'f']]
            ],
            'empty_mapping' => [
                [['a', 'b', 'c'], ['d', 'e', 'f']],
                [[]],
                [['a', 'b', 'c'], ['d', 'e', 'f']]
            ],
            'multiple_mappings' => [
                [
                    ['x_1' => 'a', 'x_2' => 'b', 'x_3' => 'c'],
                    ['x_1' => 'd', 'x_2' => 'e', 'x_3' => 'f']
                ],
                [
                    ['x_1' => 'y_1', 'x_2' => 'y_2'],
                    ['x_3' => 'z_3', 'y_2' => 'z_2']
                ],
                [
                    ['y_1' => 'a', 'z_2' => 'b', 'z_3' => 'c'],
                    ['y_1' => 'd', 'z_2' => 'e', 'z_3' => 'f']
                ],
            ]
        ];
    }

    /**
     * @dataProvider mapDataProvider
     * @param array $inputData
     * @param array $mappings
     * @param array $expectedResult
     */
    public function testMapData(array $inputData, array $mappings, array $expectedResult)
    {
        $mapper = new Mapper();
        $mapper->mapData($inputData, ...$mappings);
        $this->assertEquals($expectedResult, $inputData);
    }
}
