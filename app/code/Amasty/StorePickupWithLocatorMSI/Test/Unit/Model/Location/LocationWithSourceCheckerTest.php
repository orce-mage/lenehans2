<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Test\Unit\Model\Location;

use Amasty\Storelocator\Model\ResourceModel\Location\Collection;
use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory;
use Amasty\StorePickupWithLocatorMSI\Model\Location\LocationWithSourceChecker;
use Magento\Store\Model\Store;
use PHPUnit\Framework\TestCase;

/**
 * @see LocationWithSourceChecker
 */
class LocationWithSourceCheckerTest extends TestCase
{
    /**
     * @param int $storeId
     * @param int $size
     * @param bool $expectedResult
     * @covers \Amasty\StorePickupWithLocatorMSI\Model\Location\LocationWithSourceChecker::isExists
     * @dataProvider dataProvider
     */
    public function testIsExists(int $storeId, int $size, bool $expectedResult)
    {
        $collectionMock = $this->createMock(Collection::class);
        $collectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn($size);

        $collectionMock->expects($this->once())
            ->method('addFilterByStores')
            ->with([Store::DEFAULT_STORE_ID, $storeId]);

        $collectionFactoryMock = $this->createMock(CollectionFactory::class);
        $collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
        $collectionMock->expects($this->once())
            ->method('getSelect')
            ->willReturn($selectMock);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var LocationWithSourceChecker $model */
        $model = $objectManager->getObject(LocationWithSourceChecker::class, [
            'locationCollectionFactory' => $collectionFactoryMock
        ]);
        $actualResult = $model->isExists($storeId);

        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @return array[]
     */
    public function dataProvider(): array
    {
        return [
            'true' => [
                'storeId' => 1,
                'size' => 1,
                'expectedResult' => true
            ],

            'false' => [
                'storeId' => 1,
                'size' => 0,
                'expectedResult' => false
            ],
        ];
    }
}
