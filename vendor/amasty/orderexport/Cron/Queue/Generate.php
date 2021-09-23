<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Amasty\Orderexport\Cron\Queue;

use Amasty\Orderexport\Model\QueueRepository;
use Amasty\Orderexport\Model\ResourceModel\Profiles\CollectionFactory as ProfilesCollection;
use Magento\Cron\Model\Schedule;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;

class Generate implements QueueInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Schedule
     */
    private $schedule;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var QueueRepository
     */
    private $queueRepository;

    /**
     * @var ProfilesCollection
     */
    private $profilesCollectionFactory;

    public function __construct(
        LoggerInterface $logger,
        Schedule $schedule,
        TimezoneInterface $timezone,
        QueueRepository $queueRepository,
        ProfilesCollection $profilesCollectionFactory
    ) {
        $this->logger = $logger;
        $this->schedule = $schedule;
        $this->timezone = $timezone;
        $this->queueRepository = $queueRepository;
        $this->profilesCollectionFactory = $profilesCollectionFactory;
    }

    /**
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\CronException
     */
    public function execute()
    {
        $profilesCollection = $this->profilesCollectionFactory->create()
            ->addFieldToFilter(QueueInterface::PROFILE_ENABLED_FIELD, 1)
            ->addFieldToFilter(QueueInterface::RUN_BY_CRON_FIELD, 1);

        foreach ($profilesCollection as $profile) {
            $currentTime = $this->timezone->date()->getTimestamp();
            $nextExecutionTime = $currentTime;
            $this->schedule->setCronExpr($profile->getCronSchedule());
            $this->schedule->setScheduledAt($currentTime);

            while ($nextExecutionTime < $currentTime + QueueInterface::GENERATE_QUEUE_AHEAD_FOR) {
                if ($this->schedule->trySchedule()) {
                    try {
                        $this->queueRepository->save($profile->getId(), $nextExecutionTime);
                    } catch (CouldNotSaveException $e) {
                        $this->logger->critical($e->getMessage());
                        break;
                    }
                }

                $nextExecutionTime += QueueInterface::SECONDS_IN_MINUTE;
                $this->schedule->setScheduledAt($nextExecutionTime);
            }
        }
    }
}
