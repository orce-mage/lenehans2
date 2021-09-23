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

use Mirasvit\CacheWarmer\Api\Data\JobInterface;
use Mirasvit\CacheWarmer\Api\Repository\JobRepositoryInterface;
use Mirasvit\CacheWarmer\Model\Config;


class WarmerSpeedService
{
    private $config;

    private $jobRepository;

    public function __construct(
        JobRepositoryInterface $jobRepository,
        Config $config
    ) {
        $this->jobRepository = $jobRepository;
        $this->config        = $config;
    }

    /**
     * @return int
     */
    public function getAverageWarmingSpeed()
    {
        $jobs = $this->jobRepository->getCollection()
            ->addFieldToFilter(JobInterface::STATUS, ['neq' => JobInterface::STATUS_SCHEDULED])
            ->addFieldToFilter(JobInterface::STATUS, ['neq' => JobInterface::STATUS_RUNNING]);

        $warmedPages = 0;
        $totalTime   = 0;

        foreach ($jobs as $job) {
            $info = $job->getInfo();

            $startedAt  = $job->getStartedAt();
            $finishedAt = $job->getFinishedAt();

            if ($job->getStatus() == JobInterface::STATUS_COMPLETED && (!isset($info["Warmed Pages"]) || !$info["Warmed Pages"])) {
                continue;
            }

            $warmedPages += isset($info["Warmed Pages"]) ? $info["Warmed Pages"] : 0;
            $totalTime   += strtotime($finishedAt) - strtotime($startedAt);
        }

        if (!$warmedPages) {
            return 0;
        }

        $timePerPage = $totalTime / $warmedPages;

        if (!$timePerPage) {
            return 0;
        }

        $avgSpeed = $this->config->getJobRunThreshold() * $this->getCronTriggeredJobsPerHour() / $timePerPage;

        return floor($avgSpeed);
    }

    /**
     * @return float
     */
    private function getCronTriggeredJobsPerHour()
    {
        $cronTriggeredJobs = $this->jobRepository->getCollection()
            ->addFieldToFilter(JobInterface::STATUS, ['neq' => JobInterface::STATUS_SCHEDULED])
            ->addFieldToFilter(JobInterface::STATUS, ['neq' => JobInterface::STATUS_RUNNING])
            ->addFieldToFilter(JobInterface::INFO_SERIALIZED, ['like' => '%cron%']);

        $totalCronJobsCount = $cronTriggeredJobs->getSize() ? $cronTriggeredJobs->getSize() - 1 : 0;

        if (!$totalCronJobsCount) {
            return 0;
        }

        $cronFirstJobStartedAt = $cronTriggeredJobs->getFirstItem()->getStartedAt();
        $cronLastJobStartedAt  = $cronTriggeredJobs->getLastItem()->getStartedAt();
        $cronJobsTimeInterval  = strtotime($cronLastJobStartedAt) - strtotime($cronFirstJobStartedAt);

        $runningHours = $cronJobsTimeInterval / 3600;

        return $totalCronJobsCount / $runningHours;
    }
}
