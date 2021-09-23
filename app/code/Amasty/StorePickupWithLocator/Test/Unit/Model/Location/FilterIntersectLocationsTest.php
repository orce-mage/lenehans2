<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocator\Test\Unit\Model\Location;

use Amasty\StorePickupWithLocator\Model\Location\FilterIntersectLocations;
use Amasty\StorePickupWithLocator\Test\Unit\Traits\ObjectManagerTrait;
use PHPUnit\Framework\TestCase;

/**
 * @see FilterIntersectLocations
 */
class FilterIntersectLocationsTest extends TestCase
{
    use ObjectManagerTrait;

    /**
     * @covers       \Amasty\StorePickupWithLocator\Model\Location\FilterIntersectLocations::filter
     * @dataProvider dataProvider
     * @param array $productIdentifiers
     * @param array $productsWithLocations
     * @param array $expectedResult
     */
    public function testFilter(array $productIdentifiers, array $productsWithLocations, array $expectedResult)
    {
        /** @var FilterIntersectLocations $model */
        $model = $this->getObjectManager()->getObject(FilterIntersectLocations::class);

        $actualResult = $model->filter($productIdentifiers, $productsWithLocations);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @return array[]
     */
    public function dataProvider(): array
    {
        return [
            'emptyResultSkuEmptyLocations' => [
                'productIdentifiers' => ['a', 'b', 'c'],
                'productsWithLocations' => [],
                'expectedResult' => []
            ],
            'emptyResultIdsEmptyLocations' => [
                'productIdentifiers' => ['1', '2', '3'],
                'productsWithLocations' => [],
                'expectedResult' => []
            ],
            'emptyResultNoAvailableLocations' => [
                'productIdentifiers' => [1, 2, 3],
                'productsWithLocations' => [
                    1 => [1, 2, 3],
                    2 => [1, 4, 5, 6],
                    3 => [7, 8, 9],
                ],
                'expectedResult' => []
            ],
            'availableOneLocation' => [
                'productIdentifiers' => ['a', 'b', 'c'],
                'productsWithLocations' => [
                    'a' => [1, 2, 3],
                    'b' => [1, 4, 5, 6],
                    'c' => [1, 7, 8, 9],
                ],
                'expectedResult' => [1]
            ],
            'availableLocations' => [
                'productIdentifiers' => ['a', 'b', 'c'],
                'productsWithLocations' => [
                    'a' => [1, 2, 3, 8],
                    'b' => [1, 3, 4, 5, 6, 8],
                    'c' => [1, 2, 7, 8, 9],
                ],
                'expectedResult' => [1, 8]
            ],
            'noLocationsForOneProduct' => [
                'skus' => ['a', 'b', 'c', 'd'],
                'productsWithLocations' => [
                    'a' => [1, 2, 3, 8],
                    'b' => [1, 3, 4, 5, 6, 8],
                    'c' => [1, 2, 7, 8, 9],
                ],
                'expectedResult' => []
            ],
        ];
    }
}
