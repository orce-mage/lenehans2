<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Test\Unit\Model;

use Amasty\Storelocator\Api\ReviewRepositoryInterface;
use Amasty\Storelocator\Model\Location;
use Amasty\Storelocator\Model\ResourceModel\Review\CollectionFactory;
use Amasty\Storelocator\Test\Unit\Traits;
use Magento\Customer\Api\CustomerRepositoryInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class LocationTest
 *
 * @see Location
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class LocationTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @covers Location::getLocationReviews
     *
     * @throws \ReflectionException
     */
    public function testGetLocationReviews()
    {
        /** @var Location $model */
        $model = $this->createPartialMock(Location::class, []);
        /** @var CustomerRepositoryInterface|MockObject $customerRepository */
        $customerRepository = $this->createMock(CustomerRepositoryInterface::class, ['getById']);
        /** @var CollectionFactory|MockObject $reviewsCollection */
        $reviewsCollection = $this->createPartialMock(CollectionFactory::class, ['create']);
        /** @var ReviewRepositoryInterface|MockObject $reviewRepInterface */
        $reviewRepInterface = $this->createMock(ReviewRepositoryInterface::class, ['getApprovedByLocationId']);
        $reviewRepInterface->expects($this->any())->method('getApprovedByLocationId')->willReturn($reviewsCollection);

        $this->setProperty($model, 'reviewRepository', $reviewRepInterface, Location::class);

        $this->assertEquals([], $this->invokeMethod($model, 'getLocationReviews'));
        $customerRepository->expects($this->never())->method('getById');
    }

    /**
     * @covers Location::getLocationAverageRating
     *
     * @dataProvider getLocationAverageRatingDataProvider
     *
     * @throws \ReflectionException
     */
    public function testGetLocationAverageRating($rating, $expectedResult)
    {
        /** @var Location $model */
        $model = $this->createPartialMock(Location::class, []);

        /** @var ReviewRepositoryInterface|MockObject $reviewRepInterface */
        $reviewRepInterface = $this->createMock(ReviewRepositoryInterface::class, ['getApprovedByLocationId']);

        $reviews = $this->getObjectManager()->getObject(\Amasty\Storelocator\Model\Review::class);
        $reviews->setRating($rating);

        $reviewRepInterface->expects($this->any())->method('getApprovedByLocationId')->willReturn([$reviews]);

        $this->setProperty($model, 'reviewRepository', $reviewRepInterface, Location::class);

        $result = $this->invokeMethod($model, 'getLocationAverageRating');
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for getLocationAverageRating test
     * @return array
     */
    public function getLocationAverageRatingDataProvider()
    {
        return [
            [1, 1],
            [-2, -2],
            [3, 3],
            [-4, -4],
            [5, 5],
            [0, 0],
        ];
    }
}
