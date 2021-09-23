<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Amasty\Orderexport\Model;

use Amasty\Orderexport\Model\QueueFactory as QueueFactory;
use Amasty\Orderexport\Model\ResourceModel\Queue as QueueResource;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class QueueRepository
{
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var QueueResource
     */
    private $queueResource;

    /**
     * @var QueueFactory
     */
    private $queueFactory;

    public function __construct(
        TimezoneInterface $timezone,
        QueueResource $queueResource,
        QueueFactory $queueFactory
    ) {
        $this->timezone = $timezone;
        $this->queueResource = $queueResource;
        $this->queueFactory = $queueFactory;
    }

    /**
     * @param $profileId
     * @param $scheduleTime
     * @return mixed
     * @throws CouldNotSaveException
     */
    public function save($profileId, $scheduleTime)
    {
        try {
            $queueItem = $this->queueFactory->create()
                ->setProfileId($profileId)
                ->setCreatedAt($this->timezone->date()->format('Y:m:d H:i:s'))
                ->setScheduledAt($scheduleTime);
            $this->queueResource->save($queueItem);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $queueItem;
    }

    /**
     * @param Queue $queue
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Queue $queue)
    {
        try {
            $this->queueResource->delete($queue);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the queue item with id %1: %2',
                $queue->getId(),
                $exception->getMessage()
            ));
        }

        return true;
    }
}
