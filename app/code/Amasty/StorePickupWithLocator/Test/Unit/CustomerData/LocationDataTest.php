<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Test\Unit\CustomerData;

use Amasty\StorePickupWithLocator\CustomerData\LocationData;
use Amasty\StorePickupWithLocator\Model\ScheduleProvider;
use Amasty\StorePickupWithLocator\Test\Unit\Traits;
use Amasty\StorePickupWithLocator\Model\ConfigProvider;
use Amasty\StorePickupWithLocator\Model\LocationProvider;
use PHPUnit\Framework\TestCase;

/**
 * Class LocationDataTest
 *
 * @see LocationData
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class LocationDataTest extends TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const STORE_ID = 0;

    const WEBSITE_ID = 0;

    /**
     * @var LocationData
     */
    private $model;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var LocationProvider
     */
    private $locationProvider;


    public function setUp(): void
    {
        $this->configProvider = $this->createMock(ConfigProvider::class);
        $this->locationProvider = $this->createMock(LocationProvider::class);

        $scheduleProvider = $this->createMock(ScheduleProvider::class);
        $scheduleProvider->expects($this->any())
            ->method('getScheduleDataArray')
            ->willReturn([
                'items' => [],
                'intervals' => [],
                'emptySchedules' => [],
            ]);

        $this->model = $this->getObjectManager()->getObject(
            LocationData::class,
            [
                'configProvider' => $this->configProvider,
                'locationProvider' => $this->locationProvider,
                'scheduleProvider' => $scheduleProvider
            ]
        );
    }

    /**
     * @covers       LocationData::getSectionData
     * @dataProvider testGetSectionDataDataProvider
     */
    public function testGetSectionData($expected)
    {
        $this->configProvider->expects($this->any())->method('isStorePickupEnabled')
            ->willReturn(true);

        $returnedStores = [
            [
                'id' => '2',
                'schedule_id' => 0
            ]
        ];

        $this->locationProvider->expects($this->any())->method('getLocationCollection')->willReturn($returnedStores);

        $quoteMock = $this->createMock(\Magento\Quote\Model\Quote::class);
        $this->locationProvider->expects($this->any())->method('getQuote')->willReturn($quoteMock);

        $store = $this->createMock(\Magento\Store\Model\Store::class);
        $quoteMock->expects($this->any())->method('getStore')->willReturn($store);

        $store->expects($this->any())->method('getWebsiteId')->willReturn(self::WEBSITE_ID);
        $store->expects($this->any())->method('getId')->willReturn(self::STORE_ID);

        $this->assertEquals($expected, $this->model->getSectionData());
    }

    public function testGetSectionDataDataProvider()
    {
        return
            [
                [
                    [
                        'stores' => [
                            [
                                'id' => '2',
                                'schedule_id' => 0
                            ]
                        ],
                        'website_id' => self::WEBSITE_ID,
                        'store_id' => self::STORE_ID,
                        'schedule_data' => [
                            'items' => [],
                            'intervals' => [],
                            'emptySchedules' => []
                        ],
                        'multiple_addresses_url' => null,
                        'contact_us_url' => null
                    ]
                ]
            ];
    }
}
