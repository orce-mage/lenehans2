<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\CacheWarmer\Service;

use Magento\Framework\Stdlib\DateTime;
use Mirasvit\CacheWarmer\Api\Data\JobInterface;
use Mirasvit\CacheWarmer\Api\Repository\JobRepositoryInterface;
use Mirasvit\CacheWarmer\Api\Service\ScheduleJobServiceInterface;
use Mirasvit\CacheWarmer\Model\JobFactory;

class ScheduleJobService implements ScheduleJobServiceInterface
{
    /**
     * @var JobRepositoryInterface
     */
    private $jobRepository;

    /**
     * ScheduleJobService constructor.
     * @param JobRepositoryInterface $jobRepository
     */
    public function __construct(
        JobRepositoryInterface $jobRepository
    ) {
        $this->jobRepository = $jobRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareSchedule()
    {
        $collection = $this->jobRepository->getCollection();

        $collection->addFieldToFilter(JobInterface::STARTED_AT, ['null' => true])
            ->addFieldToFilter(JobInterface::PRIORITY, JobInterface::PRIORITY_NORMAL);

        if ($collection->count() == 0) {
            $job = $this->jobRepository->create()
                ->setPriority(JobInterface::PRIORITY_NORMAL)
                ->setFilter([]);

            $this->jobRepository->save($job);
        }


        $collection = $this->jobRepository->getCollection();
        $collection->addFieldToFilter(JobInterface::FINISHED_AT, ['null' => true])
            ->addFieldToFilter(JobInterface::STARTED_AT, ['notnull' => true]);
        $collection->getSelect()->where('NOW() - started_at > 1800');

        /** @var JobInterface $job */
        foreach ($collection as $job) {
            $job->setFinishedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));
            $this->jobRepository->save($job);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteEmptySchedule()
    {
        $jobCollection = $this->jobRepository->getCollection();
        $jobCollection->addFieldToFilter(JobInterface::STARTED_AT, ['null' => true])
            ->addFieldToFilter(JobInterface::FINISHED_AT, ['null' => true]);

        if ($jobCollection->getSize() > 0) {
            /** @var JobInterface $job */
            foreach ($jobCollection as $job) {
                $this->jobRepository->delete($job);
            }
        }
    }
}
