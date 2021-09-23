<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Amasty\Orderexport\Cron\Profile;

use Amasty\Orderexport\Model\ProfilesRepository;
use Amasty\Orderexport\Model\QueueRepository;
use Amasty\Orderexport\Model\ResourceModel\Queue\CollectionFactory as QueueCollection;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;

class Run
{
    const SCHEDULED_AT_FIELD = 'scheduled_at';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var QueueRepository
     */
    private $queueRepository;

    /**
     * @var QueueCollection
     */
    private $queueCollectionFactory;

    /**
     * @var ProfilesRepository
     */
    private $profilesRepository;

    public function __construct(
        LoggerInterface $logger,
        TimezoneInterface $timezone,
        QueueRepository $queueRepository,
        ProfilesRepository $profilesRepository,
        QueueCollection $queueCollectionFactory
    ) {
        $this->logger = $logger;
        $this->timezone = $timezone;
        $this->queueRepository = $queueRepository;
        $this->profilesRepository = $profilesRepository;
        $this->queueCollectionFactory = $queueCollectionFactory;
    }

    public function execute()
    {
        $queueCollection = $this->queueCollectionFactory->create()
            ->addFieldToFilter(self::SCHEDULED_AT_FIELD, ['lteq' => $this->timezone->date()->getTimestamp()]);

        foreach ($queueCollection as $queueItem) {
            try {
                $profile = $this->profilesRepository->getById($queueItem->getProfileId());
                $profile->run(null);
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }

            $this->queueRepository->delete($queueItem);
        }
    }
}
