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



namespace Mirasvit\CacheWarmer\Cron;

use Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory as ScheduleCollectionFactory;
use Magento\Cron\Model\Schedule;
use Magento\Framework\App\ResourceConnection;
use Mirasvit\CacheWarmer\Api\Data\JobInterface;
use Mirasvit\CacheWarmer\Api\Data\LogInterface;
use Mirasvit\CacheWarmer\Api\Data\PageInterface;
use Mirasvit\CacheWarmer\Api\Data\TraceInterface;
use Mirasvit\CacheWarmer\Api\Repository\JobRepositoryInterface;
use Mirasvit\CacheWarmer\Api\Repository\PageRepositoryInterface;
use Mirasvit\CacheWarmer\Api\Repository\TraceRepositoryInterface;
use Mirasvit\CacheWarmer\Model\Config;
use Mirasvit\CacheWarmer\Service\WarmRuleService;

class UpdatePagesCron
{
    private $pageRepository;

    private $jobRepository;

    private $scheduleCollectionFactory;

    private $config;

    private $resource;

    private $traceRepository;

    private $warmRuleService;

    public function __construct(
        PageRepositoryInterface $pageRepository,
        JobRepositoryInterface $jobRepository,
        TraceRepositoryInterface $traceRepository,
        ScheduleCollectionFactory $scheduleCollectionFactory,
        WarmRuleService $warmRuleService,
        ResourceConnection $resource,
        Config $config
    ) {
        $this->pageRepository            = $pageRepository;
        $this->jobRepository             = $jobRepository;
        $this->traceRepository           = $traceRepository;
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        $this->warmRuleService           = $warmRuleService;
        $this->resource                  = $resource;
        $this->config                    = $config;
    }

    /**
     * @return void
     */
    public function execute()
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        // Delete old jobs
        $jobsLimitDate = date('Y-m-d H:i:s', time() - 2 * 24 * 60 * 60);
        $this->resource->getConnection()->delete(
            $this->resource->getTableName(JobInterface::TABLE_NAME),
            [
                JobInterface::FINISHED_AT . " <= '" . $jobsLimitDate . "'",
                JobInterface::STARTED_AT . " <= '" . $jobsLimitDate . "'",
            ]
        );

        // Delete old logs (older then 90 days)
        $date = date("Y-m-d H:i:s", time() - 90 * 24 * 60 * 60);

        $this->resource->getConnection()->delete(
            $this->resource->getTableName(LogInterface::TABLE_NAME),
            LogInterface::CREATED_AT . " < '" . $date . "'"
        );

        $maxRecordsNumber = 900;

        // Delete old traces
        $cnt = $this->resource->getConnection()->fetchOne(
            "SELECT COUNT(*) FROM {$this->resource->getTableName(TraceInterface::TABLE_NAME)};"
        );
        $n   = $cnt - $maxRecordsNumber;
        if ($n > 100) {
            $this->resource->getConnection()->query(
                "DELETE FROM {$this->resource->getTableName(TraceInterface::TABLE_NAME)} ORDER BY trace_id ASC LIMIT $n"
            );
        }

        // Delete ignored pages
        $state          = 0;
        $offset         = $this->config->getCleanupPagesState();
        $limit          = 1000;
        $pageCollection = $this->pageRepository->getCollection()->addFieldToSelect([
            'page_id',
            'uri',
            'user_agent',
            'page_type'
        ]);

        $start = time();
        $timeLimit = 15 * 60;

        $select = $pageCollection->getSelect();
        $select->limit($limit, $offset);

        while ($pageCollection->count()) {
            /** @var PageInterface $page */
            foreach ($pageCollection as $page) {
                if ($this->config->isIgnoredPage($page)) {
                    $this->pageRepository->delete($page);
                }
            }
            $pageCollection->clear();
            $offset += $limit;

            if (time() - $start >= $timeLimit) {
                $state = $offset;

                break;
            }

            $select->limit($limit, $offset);
        }

        $this->config->setCleanupPagesState($state);

        //Update pages by warm rules
        $this->warmRuleService->refreshPagesByRules();

        // Delete old cron jobs (running)
        $scheduleCollection = $this->scheduleCollectionFactory->create();
        $scheduleCollection->addFieldToFilter('status', Schedule::STATUS_RUNNING);
        $scheduleCollection->addFieldToFilter(
            'scheduled_at',
            ['lteq' => date('Y-m-d H:i:s', time() - 60 * 60)]
        );
        foreach ($scheduleCollection as $schedule) {
            $schedule->delete();
        }

        // Delete mst_cache_warmer_cleanup jobs
        $cleanupJobs = $this->scheduleCollectionFactory->create();
        $cleanupJobs->addFieldToFilter('job_code', 'mst_cache_warmer_cleanup');

        foreach ($cleanupJobs as $job) {
            $job->delete();
        }
    }
}
