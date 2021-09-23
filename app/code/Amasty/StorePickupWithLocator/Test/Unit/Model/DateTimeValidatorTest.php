<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Test\Unit\Model;

use Amasty\Base\Model\Serializer;
use Amasty\Storelocator\Model\Location;
use Amasty\Storelocator\Model\LocationFactory;
use Amasty\Storelocator\Model\ResourceModel\Location as LocationResource;
use Amasty\StorePickupWithLocator\Model\ConfigProvider;
use Amasty\StorePickupWithLocator\Model\DateTimeValidator;
use Amasty\StorePickupWithLocator\Model\TimeHandler;
use Amasty\StorePickupWithLocator\Test\Unit\Traits;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class DateTimeValidatorTest
 *
 * @see DateTimeValidator
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class DateTimeValidatorTest extends TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const QUOTE_ID = 1;
    const STORE_ID = 1;
    const RETURN_STRING = '{"monday":{"monday_status":"1","from":{"hours":"09","minutes":"00"},"break_from":{"hours":"15","minutes":"00"},"break_to":{"hours":"16","minutes":"00"},"to":{"hours":"21","minutes":"00"}},"tuesday":{"tuesday_status":"1","from":{"hours":"09","minutes":"00"},"break_from":{"hours":"15","minutes":"00"},"break_to":{"hours":"16","minutes":"00"},"to":{"hours":"21","minutes":"00"}},"wednesday":{"wednesday_status":"1","from":{"hours":"09","minutes":"00"},"break_from":{"hours":"15","minutes":"00"},"break_to":{"hours":"16","minutes":"00"},"to":{"hours":"21","minutes":"00"}},"thursday":{"thursday_status":"1","from":{"hours":"09","minutes":"00"},"break_from":{"hours":"15","minutes":"00"},"break_to":{"hours":"16","minutes":"00"},"to":{"hours":"21","minutes":"00"}},"friday":{"friday_status":"1","from":{"hours":"09","minutes":"00"},"break_from":{"hours":"15","minutes":"00"},"break_to":{"hours":"16","minutes":"00"},"to":{"hours":"21","minutes":"00"}},"saturday":{"saturday_status":"1","from":{"hours":"09","minutes":"00"},"break_from":{"hours":"15","minutes":"00"},"break_to":{"hours":"16","minutes":"00"},"to":{"hours":"21","minutes":"00"}},"sunday":{"sunday_status":"1","from":{"hours":"09","minutes":"00"},"break_from":{"hours":"15","minutes":"00"},"break_to":{"hours":"16","minutes":"00"},"to":{"hours":"21","minutes":"00"}}}';

    /**
     * @var DateTimeValidator
     */
    private $model;

    /**
     * @var TimeHandler
     */
    private $timeHandler;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var LocationFactory
     */
    private $locationFactory;

    /**
     * @var LocationResource
     */
    private $locationResource;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var array
     */
    private $items = [];

    /**
     * @var MockObject
     */
    private $dateTimeValidator;

    /**
     * @var MockObject
     */
    private $location;

    protected function setUp(): void
    {
        $this->timeHandler = $this->createMock(TimeHandler::class);
        $this->configProvider = $this->createMock(ConfigProvider::class);
        $this->locationFactory = $this->createMock(LocationFactory::class);
        $this->locationResource = $this->createMock(LocationResource::class);
        $this->quoteRepository = $this->createMock(CartRepositoryInterface::class);
        $this->serializer = $this->createMock(Serializer::class);
        $this->dateTimeValidator = $this->createPartialMock(DateTimeValidator::class, ['getLocationByStoreId']);
        $this->location = $this->createPartialMock(Location::class, []);

        $this->locationFactory->expects($this->any())
            ->method('create')->willReturn($this->location);

        $this->dateTimeValidator->expects($this->any())->method('getLocationByStoreId')
            ->with(self::STORE_ID)->willReturnArgument($this->location);

        $this->model = $this->getObjectManager()->getObject(
            DateTimeValidator::class,
            [
                'timeHandler' => $this->timeHandler,
                'configProvider' => $this->configProvider,
                'locationFactory' => $this->locationFactory,
                'locationResource' => $this->locationResource,
                'quoteRepository' => $this->quoteRepository,
                'serializer' => $this->serializer
            ]
        );
    }

    /**
     * @covers DateTimeValidator::isValidDate
     * @dataProvider isValidDateDataProvider
     */
    public function testIsValidDate($quoteId, $storeId, $date, $timeFrom, $timeTo, $flagIsDayAllowed, $flagIsTimeEnabled, $expected)
    {
        $this->timeHandler->expects($this->once())->method('getDateTimestamp')
            ->willReturn(strtotime(date('Y-m-d')));

        $this->configProvider->expects($this->any())->method('isSameDayAllowed')
            ->willReturn($flagIsDayAllowed);

        $this->configProvider->expects($this->any())->method('isPickupTimeEnabled')
            ->willReturn($flagIsTimeEnabled);

        $this->assertEquals($expected, $this->model->isValidDate($quoteId, $storeId, $date, $timeFrom, $timeTo));
    }

    /**
     * @covers DateTimeValidator::isValidTime
     * @dataProvider isValidTimeDataProvider
     */
    public function testIsValidTime($quoteId, $storeId, $timeFrom, $timeTo, $inputDate, $currentDateTime, $flagIsTimeEnabled, $flagIsDayAllowed, $expected)
    {
        $quote = $this->createMock(Quote::class);
        $this->items[] = $this->createMock(Quote\Item::class);

        $this->configProvider->expects($this->any())->method('isPickupTimeEnabled')
            ->willReturn($flagIsTimeEnabled);

        $this->configProvider->expects($this->any())->method('isSameDayAllowed')
            ->willReturn($flagIsDayAllowed);

        $this->quoteRepository->expects($this->any())->method('get')->with($quoteId)->willReturn($quote);

        $quote->expects($this->any())->method('getItems')
            ->willReturnCallback(function () {
                return $this->items;
            }
        );

        $this->location->setScheduleString(static::RETURN_STRING);

        $this->assertEquals($expected, $this->invokeMethod($this->model, 'isValidTime',
                [
                    $quoteId,
                    $storeId,
                    $timeFrom,
                    $timeTo,
                    $inputDate,
                    $currentDateTime
                ]
            )
        );
    }

    /**
     * @covers DateTimeValidator::isValidTimeForLocation
     * @dataProvider isValidTimeForLocationDataProvider
     */
    public function testIsValidTimeForLocation($storeId, $inputDate, $timeFrom, $timeTo, $scheduleString, $isNeedUnserialize, $expected)
    {
        $dayOfWeek = strtolower(date("l", $inputDate));
        $this->location->setScheduleString($scheduleString);

        if ($isNeedUnserialize) {
            $this->serializer->expects($this->any())->method('unserialize')->with($scheduleString)
                ->willReturn(
                    [
                        $dayOfWeek => [
                            $dayOfWeek . '_status' => 1,
                            'from' => [
                                'hours' => 12,
                                'minutes' => 00
                            ],
                            'to' => [
                                'hours' => 18,
                                'minutes' => 00
                            ],
                            'break_from' => [
                                'hours' => 14,
                                'minutes' => 00
                            ],
                            'break_to' => [
                                'hours' => 15,
                                'minutes' => 00
                            ]
                        ]
                    ]
                );
        }

        $this->timeHandler->expects($this->any())->method('getDate')->willReturn(date('Y-m-d', $inputDate));

        $this->assertEquals($expected, $this->invokeMethod($this->model, 'isValidTimeForLocation',
                [
                    $storeId,
                    $inputDate,
                    $timeFrom,
                    $timeTo
                ]
            )
        );
    }

    /**
     * @covers DateTimeValidator::isValidForSchedule
     * @dataProvider isValidForScheduleDataProvider
     */
    public function testIsValidForSchedule($timeFrom, $timeTo, $storeFrom, $storeTo, $storeBreakFrom, $storeBreakTo, $expected)
    {
        $this->assertEquals($expected, $this->invokeMethod($this->model, 'isValidForSchedule',
                [
                    $timeFrom,
                    $timeTo,
                    $storeFrom,
                    $storeTo,
                    $storeBreakFrom,
                    $storeBreakTo
                ]
            )
        );
    }

    /**
     * @return array
     */
    public function isValidDateDataProvider()
    {
        return [
            [
                self::QUOTE_ID,
                self::STORE_ID,
                date('Y-m-d'),
                strtotime('+1 hour'),
                strtotime('+1 hour 30 minutes'),
                true,
                false,
                true
            ],
            [
                self::QUOTE_ID,
                self::STORE_ID,
                date('Y/m/d', strtotime('yesterday')),
                strtotime('+1 hour 30 minutes'),
                strtotime('+2 hours'),
                true,
                false,
                false
            ],
            [
                self::QUOTE_ID,
                self::STORE_ID,
                date('Y-m-d'),
                strtotime('+1 hour'),
                strtotime('+1 hour 30 minutes'),
                false,
                false,
                false
            ],
            [
                self::QUOTE_ID,
                self::STORE_ID,
                date('Y-m-d'),
                '',
                strtotime('+1 hour 30 minutes'),
                true,
                true,
                false
            ]
        ];
    }

    /**
     * @return array
     */
    public function isValidTimeDataProvider()
    {
        return [
            [
                self::QUOTE_ID,
                self::STORE_ID,
                strtotime('+1 hour'),
                strtotime('+2 hours'),
                strtotime('+2 hours'),
                strtotime('+1 hour'),
                false,
                true,
                true
            ],
            [
                self::QUOTE_ID,
                self::STORE_ID,
                strtotime('+1 hour'),
                strtotime('+1 hour'),
                strtotime('+2 hours'),
                strtotime('+1 hour'),
                true,
                true,
                false
            ],
            [
                self::QUOTE_ID,
                self::STORE_ID,
                strtotime('+1 hour'),
                strtotime('+30 minutes'),
                strtotime('+2 hours'),
                strtotime('+1 hour'),
                true,
                false,
                false
            ],
            [
                self::QUOTE_ID,
                self::STORE_ID,
                strtotime('+1 hour'),
                strtotime('+30 minutes'),
                strtotime('yesterday'),
                strtotime('+1 hour'),
                true,
                false,
                false
            ]
        ];
    }

    /**
     * @return array
     */
    public function isValidTimeForLocationDataProvider()
    {
        return [
            [
                self::STORE_ID,
                strtotime('now'),
                strtotime('+1 hour'),
                strtotime('+2 hour'),
                static::RETURN_STRING,
                false,
                false
            ],
            [
                self::STORE_ID,
                strtotime('now'),
                strtotime('+1 hour'),
                strtotime('+2 hour'),
                static::RETURN_STRING,
                true,
                false
            ],
            [
                self::STORE_ID,
                strtotime('now'),
                strtotime('+1 hour'),
                strtotime('+2 hour'),
                '',
                false,
                true
            ]
        ];
    }

    /**
     * @return array
     */
    public function isValidForScheduleDataProvider()
    {
        return [
            [
                strtotime('now'),
                strtotime('+1 hour 30 minutes'),
                strtotime('+1 hour'),
                strtotime('+4 hours'),
                strtotime('+2 hours'),
                strtotime('+2 hours 30 minutes'),
                false
            ],
            [
                strtotime('+1 hour 30 minutes'),
                strtotime('+1 hour 30 minutes'),
                strtotime('+1 hour'),
                strtotime('+2 hours'),
                strtotime('+2 hours'),
                strtotime('+2 hours 30 minutes'),
                true
            ]
        ];
    }
}
