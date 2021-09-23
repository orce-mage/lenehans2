<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */

declare(strict_types=1);

namespace Amasty\StorePickupWithLocator\Model;

use Amasty\Base\Model\Serializer;
use Amasty\Storelocator\Model\ResourceModel\Schedule\Collection;
use Amasty\Storelocator\Model\ResourceModel\Schedule\CollectionFactory;

/**
 * @since 2.3.7 Schedule Intervals moved to separate arrays due optimization
 */
class ScheduleProvider
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var TimeHandler
     */
    private $timeHandler;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        CollectionFactory $collectionFactory,
        TimeHandler $timeHandler,
        Serializer $serializer,
        ConfigProvider $configProvider
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->timeHandler = $timeHandler;
        $this->serializer = $serializer;
        $this->configProvider = $configProvider;
    }

    /**
     * @param int[] $scheduleIds
     *
     * @return array[]
     */
    public function getScheduleDataArray(array $scheduleIds): array
    {
        if (!$this->configProvider->isPickupDateEnabled()) {
            return [
                'items' => [],
                'intervals' => [],
                'emptySchedules' => [],
            ];
        }

        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('id', ['in' => $scheduleIds]);

        $scheduleItems = [];
        $emptySchedules = [];
        $timeIntervals = [
            'default' => $this->timeHandler->generate(TimeHandler::START_TIME, TimeHandler::END_TIME)
        ];

        foreach ($collection->getData() as &$scheduleData) {
            $schedule = $this->serializer->unserialize($scheduleData['schedule']);

            $timeIntervals[$scheduleData['id']] = $this->timeHandler->execute($schedule);
            $scheduleItems[$scheduleData['id']] = $schedule;

            if (empty($timeIntervals[$scheduleData['id']])) {
                $emptySchedules[] = $scheduleData['id'];
            }
        }

        return [
            'items' => $scheduleItems,
            'intervals' => $timeIntervals,
            'emptySchedules' => $emptySchedules,
        ];
    }
}
