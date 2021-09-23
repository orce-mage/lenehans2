<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Test\Unit\Model\Location;

use Amasty\StorePickupWithLocator\Model\Location\FilterIntersectLocations;
use Amasty\StorePickupWithLocatorMSI\Model\Location\GetLocationIdsByProducts;
use Amasty\StorePickupWithLocatorMSI\Model\ResourceModel\LocationResource;
use Amasty\StorePickupWithLocatorMSI\Model\StockIdResolver;
use PHPUnit\Framework\TestCase;

/**
 * @see GetLocationIdsByProducts
 */
class GetLocationIdsByProductsTest extends TestCase
{
    const STOCK_ID = 999;

    /**
     * @var LocationResource|\PHPUnit\Framework\MockObject\MockObject
     */
    private $locationResourceMock;

    /**
     * @var GetLocationIdsByProducts
     */
    private $model;

    /**
     * @var FilterIntersectLocations|\PHPUnit\Framework\MockObject\MockObject
     */
    private $filterIntersectLocationsMock;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $stockIdResolverMock = $this->createMock(StockIdResolver::class);
        $stockIdResolverMock->expects($this->once())
            ->method('getStockId')
            ->willReturn(self::STOCK_ID);

        $this->locationResourceMock = $this->createMock(LocationResource::class);
        $this->filterIntersectLocationsMock = $this->createMock(FilterIntersectLocations::class);

        $this->model = $objectManager->getObject(GetLocationIdsByProducts::class, [
            'locationResource' => $this->locationResourceMock,
            'stockIdResolver' => $stockIdResolverMock,
            'filterIntersectLocations' => $this->filterIntersectLocationsMock
        ]);
    }

    /**
     * @param array $skusWithQtys
     * @param array $productsLocationData
     * @param array $productsWithLocations
     * @covers       GetLocationIdsByProducts::getAvailableLocationIds()
     * @dataProvider dataProvider
     */
    public function testGetAvailableLocationIds(
        array $skusWithQtys,
        array $productsLocationData,
        array $productsWithLocations
    ) {
        $this->locationResourceMock->expects($this->once())
            ->method('getProductsLocationData')
            ->with(array_keys($skusWithQtys), self::STOCK_ID)
            ->willReturn($productsLocationData);

        $this->filterIntersectLocationsMock->expects($this->once())
            ->method('filter')
            ->with(array_keys($skusWithQtys), $productsWithLocations)
            ->willReturn([]);

        $actualResult = $this->model->getAvailableLocationIds($skusWithQtys, 1);

        $this->assertSame([], $actualResult);
    }

    /**
     * @return array[][]
     */
    public function dataProvider(): array
    {
        return [
            'emptyResultEmptyLocations' => [
                'skusWithQtys' => ['a' => 3, 'b' => 3, 'c' => 3],
                'productsLocationData' => [],
                'productsWithLocations' => []
            ],

            'emptyResultNoAvailableLocations' => [
                'skusWithQtys' => ['a' => 3, 'b' => 3, 'c' => 3],
                'productsLocationData' => [
                    [
                        'sku' => 'a',
                        'location_id' => 1,
                        'qty' => 2,
                    ],
                    [
                        'sku' => 'b',
                        'location_id' => 2,
                        'qty' => 2,
                    ],
                    [
                        'sku' => 'c',
                        'location_id' => 3,
                        'qty' => 2,
                    ],
                ],
                'productsWithLocations' => []
            ],

            'locationsWithQty' => [
                'skusWithQtys' => ['a' => 3, 'b' => 3, 'c' => 3],
                'productsLocationData' => [
                    [
                        'sku' => 'a',
                        'location_id' => 1,
                        'qty' => '2.0000',
                    ],
                    [
                        'sku' => 'a',
                        'location_id' => 2,
                        'qty' => '8.0000',
                    ],
                    [
                        'sku' => 'a',
                        'location_id' => 3,
                        'qty' => '4.0000',
                    ],
                    [
                        'sku' => 'b',
                        'location_id' => 2,
                        'qty' => 2,
                    ],
                    [
                        'sku' => 'c',
                        'location_id' => 3,
                        'qty' => 2,
                    ],
                ],
                'productsWithLocations' => [
                    'a' => [2, 3]
                ]
            ],
        ];
    }
}
