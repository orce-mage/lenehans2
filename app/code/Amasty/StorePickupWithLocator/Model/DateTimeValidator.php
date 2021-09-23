<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Model;

use Amasty\Base\Model\Serializer;
use Amasty\Storelocator\Model\Location;
use Amasty\Storelocator\Model\LocationFactory;
use Amasty\Storelocator\Model\ResourceModel\Location as LocationResource;
use Amasty\Storelocator\Ui\DataProvider\Form\ScheduleDataProvider;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Class DateTimeValidator for Validate Date/Time
 */
class DateTimeValidator
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var DateTime
     */
    private $libDate;

    /**
     * @var LocationFactory
     */
    private $locationFactory;

    /**
     * @var LocationResource
     */
    private $locationResource;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var TimeHandler
     */
    private $timeHandler;

    public function __construct(
        ConfigProvider $configProvider,
        CartRepositoryInterface $quoteRepository,
        DateTime $libDate,
        LocationFactory $locationFactory,
        LocationResource $locationResource,
        Serializer $serializer,
        TimeHandler $timeHandler
    ) {
        $this->configProvider = $configProvider;
        $this->quoteRepository = $quoteRepository;
        $this->libDate = $libDate;
        $this->locationFactory = $locationFactory;
        $this->locationResource = $locationResource;
        $this->serializer = $serializer;
        $this->timeHandler = $timeHandler;
    }

    /**
     * @param int $quoteId
     * @param int $storeId
     * @param string $date
     * @param int|null $timeFrom
     * @param int|null $timeTo
     * @return bool
     */
    public function isValidDate($quoteId, $storeId, $date, $timeFrom, $timeTo)
    {
        $inputDate = strtotime($date);
        $currentDateTime = $this->timeHandler->getDateTimestamp();

        if (date(TimeHandler::DATE_FORMAT, $inputDate) < date(TimeHandler::DATE_FORMAT, $currentDateTime)) {
            return false;
        }

        if (!$this->configProvider->isSameDayAllowed() && $inputDate == $currentDateTime) {
            return false;
        }

        if (!$this->isValidTime($quoteId, $storeId, $timeFrom, $timeTo, $inputDate, $currentDateTime)) {
            return false;
        }

        return true;
    }

    /**
     * @param int $quoteId
     * @param int $storeId
     * @param int|null $timeFrom
     * @param int|null $timeTo
     * @param int $inputDate
     * @param int $currentDateTime
     * @return bool
     */
    private function isValidTime($quoteId, $storeId, $timeFrom, $timeTo, $inputDate, $currentDateTime)
    {
        if ($this->configProvider->isPickupTimeEnabled()) {
            if (date(TimeHandler::DATE_FORMAT, $inputDate) == date(TimeHandler::DATE_FORMAT, $currentDateTime)) {
                if (!$timeFrom
                    || !$timeTo
                    || ($this->configProvider->isSameDayAllowed() && $timeTo <= $currentDateTime)
                ) {
                    return false;
                }

                $delay = $this->getTimeDelay($quoteId) * 60 * 60;

                if ($timeTo < $currentDateTime + $delay) {
                    return false;
                }
            }

            if (!$this->isValidTimeForLocation($storeId, $inputDate, $timeFrom, $timeTo)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param int $storeId
     * @param string $inputDate
     * @param int $timeFrom
     * @param int $timeTo
     * @return bool
     */
    private function isValidTimeForLocation($storeId, $inputDate, $timeFrom, $timeTo)
    {
        /** @var Location $location */
        $location = $this->getLocationByStoreId($storeId);
        if ($scheduleString = $location->getData('schedule_string')) {
            $schedule = $this->serializer->unserialize($scheduleString);
            $dayOfWeek = strtolower(date("l", $inputDate));

            if (empty($schedule[$dayOfWeek][$dayOfWeek . '_status'])) {
                return false;
            }

            $storeDateWithTime = $this->timeHandler->getDate();

            $storeFrom = strtotime(
                $storeDateWithTime . ' ' .
                $schedule[$dayOfWeek][ScheduleDataProvider::OPEN_TIME][ScheduleDataProvider::HOURS] . ':' .
                $schedule[$dayOfWeek][ScheduleDataProvider::OPEN_TIME][ScheduleDataProvider::MINUTES]
            );

            if ($schedule[$dayOfWeek][ScheduleDataProvider::CLOSE_TIME][ScheduleDataProvider::HOURS] === '00') {
                $schedule[$dayOfWeek][ScheduleDataProvider::CLOSE_TIME][ScheduleDataProvider::HOURS] = '24';
            }

            $storeTo = strtotime(
                $storeDateWithTime . ' ' .
                $schedule[$dayOfWeek][ScheduleDataProvider::CLOSE_TIME][ScheduleDataProvider::HOURS] . ':' .
                $schedule[$dayOfWeek][ScheduleDataProvider::CLOSE_TIME][ScheduleDataProvider::MINUTES]
            );

            $storeBreakFrom = strtotime(
                $storeDateWithTime . ' ' .
                $schedule[$dayOfWeek][ScheduleDataProvider::START_BREAK_TIME][ScheduleDataProvider::HOURS] . ':' .
                $schedule[$dayOfWeek][ScheduleDataProvider::START_BREAK_TIME][ScheduleDataProvider::MINUTES]
            );

            $storeBreakTo = strtotime(
                $storeDateWithTime . ' ' .
                $schedule[$dayOfWeek][ScheduleDataProvider::END_BREAK_TIME][ScheduleDataProvider::HOURS] . ':' .
                $schedule[$dayOfWeek][ScheduleDataProvider::END_BREAK_TIME][ScheduleDataProvider::MINUTES]
            );

            if ($storeFrom > $storeTo) {
                $storeTo = strtotime($storeDateWithTime . ' ' .TimeHandler::END_TIME);
            }

            if (!$this->isValidForSchedule($timeFrom, $timeTo, $storeFrom, $storeTo, $storeBreakFrom, $storeBreakTo)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param int $quoteId
     * @return int
     */
    private function getTimeDelay($quoteId)
    {
        $delay = $this->configProvider->getMinTimeOrder();

        /** @var CartInterface $quote */
        $quote = $this->quoteRepository->get($quoteId);
        foreach ($quote->getItems() as $quoteItem) {
            if ($quoteItem->getBackorders()) {
                $delay = $this->configProvider->getMinTimeBackorder();
                break;
            }
        }

        return $delay;
    }

    /**
     * @param int $storeId
     * @return Location
     */
    protected function getLocationByStoreId($storeId)
    {
        /** @var Location $location */
        $location = $this->locationFactory->create();
        $this->locationResource->load($location, $storeId);

        return $location;
    }

    /**
     * @param int $timeFrom
     * @param int $timeTo
     * @param int $storeFrom
     * @param int $storeTo
     * @param int $storeBreakFrom
     * @param int $storeBreakTo
     * @return bool
     */
    private function isValidForSchedule($timeFrom, $timeTo, $storeFrom, $storeTo, $storeBreakFrom, $storeBreakTo)
    {
        if ($timeFrom < $storeFrom
            || $timeFrom >= $storeTo
            || ($timeFrom >= $storeBreakFrom && $timeFrom < $storeBreakFrom)
            || $timeTo <= $storeFrom
            || $timeTo > $storeTo
            || ($timeTo > $storeBreakTo && $timeTo <= $storeBreakTo)
        ) {
            return false;
        }

        return true;
    }
}
