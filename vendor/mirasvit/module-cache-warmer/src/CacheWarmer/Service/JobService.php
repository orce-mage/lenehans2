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

use Magento\Framework\Shell;
use Magento\Framework\Stdlib\DateTime;
use Mirasvit\CacheWarmer\Api\Data\JobInterface;
use Mirasvit\CacheWarmer\Api\Data\PageInterface;
use Mirasvit\CacheWarmer\Api\Data\WarmRuleInterface;
use Mirasvit\CacheWarmer\Api\Repository\JobRepositoryInterface;
use Mirasvit\CacheWarmer\Api\Repository\PageRepositoryInterface;
use Mirasvit\CacheWarmer\Api\Repository\WarmRuleRepositoryInterface;
use Mirasvit\CacheWarmer\Api\Service\JobServiceInterface;
use Mirasvit\CacheWarmer\Api\Service\WarmerServiceInterface;
use Mirasvit\CacheWarmer\Api\Service\MigrateServiceInterface;
use Mirasvit\CacheWarmer\Logger\Logger;
use Mirasvit\CacheWarmer\Model\Config;
use Mirasvit\CacheWarmer\Model\ResourceModel\Page\Collection;
use Mirasvit\CacheWarmer\Model\ResourceModel\WarmRule\Collection as WarmRuleCollection;
use Mirasvit\CacheWarmer\Service\Rate\CacheFillRateService;
use Mirasvit\CacheWarmer\Service\Rate\ServerLoadRateService;
use Mirasvit\CacheWarmer\Service\Warmer\PageWarmStatus;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class JobService implements JobServiceInterface
{
    const MAX_ERRORS_TO_STOP   = 30;
    const MAX_ATTEMPTS = 3;

    private $jobRepository;

    private $cacheFillRateService;

    private $serverLoadRateService;

    private $pageRepository;

    private $warmerService;

    private $ruleRepository;

    private $config;

    private $logger;

    private $dateFactory;

    private $migrateService;

    private $statusService;

    private $shell;

    private $binPath;

    /** @var string */
    private $flag;

    public function __construct(
        JobRepositoryInterface $jobRepository,
        PageRepositoryInterface $pageRepository,
        WarmerServiceInterface $warmerService,
        CacheFillRateService $cacheFillRateService,
        ServerLoadRateService $serverLoadRateService,
        StatusService $statusService,
        MigrateServiceInterface $migrateService,
        WarmRuleRepositoryInterface $ruleRepository,
        Config $config,
        Logger $logger,
        DateTime\DateTimeFactory $dateFactory,
        Shell $shell
    ) {
        $this->jobRepository         = $jobRepository;
        $this->pageRepository        = $pageRepository;
        $this->ruleRepository        = $ruleRepository;
        $this->warmerService         = $warmerService;
        $this->statusService         = $statusService;
        $this->cacheFillRateService  = $cacheFillRateService;
        $this->serverLoadRateService = $serverLoadRateService;
        $this->migrateService        = $migrateService;
        $this->config                = $config;
        $this->logger                = $logger;
        $this->dateFactory           = $dateFactory;
        $this->shell                 = $shell;

        $this->binPath = PHP_BINARY . ' ' . BP . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'magento ';
    }

    /**
     * {@inheritdoc}
     */
    public function run(JobInterface $job, $flag, $pageIds = [])
    {
        $ts = microtime(true);

        $this->flag = count($pageIds) ? 'variations' : $flag;

        $this->startJob($job);

        if (!$this->canRunJob()) {
            return $this->finishJob($job, JobInterface::STATUS_MISSED);
        }

        $this->migrateService->migrateData();
        $this->statusService->runPartialStatusUpdate();

        if (count($pageIds)) {
            $this->runByIds($job, $ts, $pageIds); //warming URIs variations
        } else {
            if ($this->config->isWarmVariations()) { //start background warming for URI variations
                $this->logger->info('Starting warming URL variations...');

                $ids = $this->getPageIdsByWarmRules(
                    $this->ruleRepository->getCollection()->setOrder(WarmRuleInterface::PRIORITY, "DESC")
                );

                if (count($ids)) {
                    $ids = implode(',', $ids);
                    $cmd = "nohup {$this->binPath} mirasvit:cache-warmer --warm --ids {$ids} >> /dev/null 2>&1 &";

                    try {
                        $this->shell->execute($cmd);
                    } catch (\Exception $e) {
                        $this->logger->info('Can\'t run background process: ' . $e->getMessage());
                    }


                } else {
                    $this->logger->info('No not cached variations found');
                }

                $this->logger->info('Continue warming...');
            }

            $this->runByWarmRules($job, $ts); //default warming
        }

        $this->logger->info('Execution Time', [round(microtime(true) - $ts, 1)]);

        $this->finishJob($job);

        return $this;
    }

    /**
     * @param JobInterface $job
     * @param int $ts
     * @param array $ids
     * @return $this
     */
    private function runByIds(JobInterface $job, $ts, $ids)
    {
        $this->logger->info('Warming variations');

        $collection = $this->getPagesVariationsCollectionByIds($ids);

        $this->logger->info('Possible variations: ' . $collection->count());

        return $this->warmCollection($job, $this->getPagesVariationsCollectionByIds($ids), $ts);
    }

    /**
     * @param JobInterface $job
     * @param int $ts
     * @return $this
     */
    private function runByWarmRules(JobInterface $job, $ts)
    {
        $jobRuleCollection = $this->ruleRepository->getCollection();
        $jobRuleCollection->setOrder(WarmRuleInterface::PRIORITY, "DESC");

        foreach ($jobRuleCollection as $rule) {
            $pages = $this->getPageCollection($rule);

            $errorCounter = 0;
            $this->logger->info("Using warm rule: #" . $rule->getId());

            $this->warmCollection($job, $pages, $ts, $rule);
        }

        return $this;
    }

    /**
     * @param JobInterface $job
     * @param Collection $collection
     * @param int $ts
     * @param WarmRuleInterface|null $rule
     * @return $this
     */
    private function warmCollection(JobInterface $job, Collection $collection, $ts, WarmRuleInterface $rule = null)
    {
        $errorCounter = 0;

        foreach ($this->warmerService->warmCollection($collection, $rule) as $status) {
            $this->logWarmStatus($job, $status);
            $this->handlePageStatus($status);

            if ($status->isError() && !$status->isSoftError()) {
                $errorCounter++;

                if ($errorCounter >= self::MAX_ERRORS_TO_STOP) {
                    $this->logError($job, 'Stopped execution. Reached errors limit.', [$errorCounter]);

                    return $this->finishJob($job, JobInterface::STATUS_ERROR);
                }
            }


            if ($this->isTimeout($ts)) {
                return;
            }
        }
    }

    /**
     * @param JobInterface $job
     * @return $this
     */
    private function startJob($job)
    {
        $this->logger->setJob($job);

        set_error_handler([$this, 'errorHandler']);

        $this->logger->info('Start job');

        $job->setStartedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT))
            ->setStatus(JobInterface::STATUS_RUNNING);

        $this->jobRepository->save($job);
        $this->logCacheFillRate($job)
            ->logServerLoadRate($job)
            ->logFlag($job);

        return $this;
    }

    /**
     * @param JobInterface $job
     * @return $this
     */
    private function logServerLoadRate($job)
    {
        $message = 'Server Load Rate';
        $rate    = $this->serverLoadRateService->getRate();

        $this->logger->info($message, [$rate]);

        $info             = $job->getInfo();
        $info[$message][] = $rate . '%';
        $job->setInfo($info);

        $this->jobRepository->save($job);

        return $this;
    }

    /**
     * @param JobInterface $job
     * @return $this
     */
    private function logCacheFillRate($job)
    {
        $message = 'Cache Fill Rate';
        $rate    = $this->cacheFillRateService->getRate();

        $this->logger->info($message, [$rate]);

        $info             = $job->getInfo();
        $info[$message][] = $rate . '%';
        $job->setInfo($info);

        $this->jobRepository->save($job);

        return $this;
    }

    /**
     * @param JobInterface $job
     * @return $this
     */
    private function logFlag($job)
    {
        $message = 'Trigger';
        $flag    = $this->flag;

        $this->logger->info($message, [$flag]);

        $info           = $job->getInfo();
        $info[$message] = $flag;
        $job->setInfo($info);

        $this->jobRepository->save($job);

        return $this;
    }

    /**
     * @return bool
     */
    private function canRunJob()
    {
        if (!$this->config->isPageCacheEnabled()) {
            $this->logger->warning('Page Cache is disabled');

            return false;
        }

        $serverLoadRate      = $this->serverLoadRateService->getRate();
        $serverLoadThreshold = $this->config->getServerLoadThreshold();

        if ($serverLoadRate > $serverLoadThreshold) {
            $this->logger->warning('Server load threshold reached', [
                'rate'      => $serverLoadRate,
                'threshold' => $serverLoadThreshold,
            ]);

            return false;
        }

        $cacheFillRate      = $this->cacheFillRateService->getRate();
        $cacheFillThreshold = $this->config->getCacheFillThreshold();

        if ($cacheFillRate > $cacheFillThreshold) {
            $this->logger->warning('Cache fill threshold reached', [
                'rate'      => $cacheFillRate,
                'threshold' => $cacheFillThreshold,
            ]);

            return false;
        }

        return true;
    }

    /**
     * @param JobInterface $job
     * @param string       $status
     * @return $this
     */
    private function finishJob($job, $status = null)
    {
        $job->setFinishedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));

        if (!$status) {
            $status = JobInterface::STATUS_COMPLETED;
        }

        $job->setStatus($status);

        $this->jobRepository->save($job);

        $this->logCacheFillRate($job)
            ->logServerLoadRate($job);

        $this->logger->info('Finish job');

        restore_error_handler();

        return $this;
    }


    /**
     * @param JobInterface   $job
     * @param PageWarmStatus $status
     * @return $this
     */
    private function logWarmStatus($job, $status)
    {
        $message = 'Warmed Pages';
        $this->logger->info($status->toString());

        $info           = $job->getInfo();
        $info[$message] = isset($info[$message]) ? $info[$message] + 1 : 1;
        $job->setInfo($info);

        return $this;
    }

    /**
     * @param JobInterface $job
     * @param string       $message
     * @param array|null   $context
     * @return $this
     */
    private function logError($job, $message, array $context = null)
    {
        $this->logger->error($message, $context);

        $info          = $job->getInfo();
        $info['Error'] = $message;
        $job->setInfo($info);

        $this->jobRepository->save($job);

        return $this;
    }

    /**
     * @param PageWarmStatus $status
     * @return $this
     */
    private function handlePageStatus(PageWarmStatus $status)
    {
        $page = $status->getPage();
        if ($status->isError()) {
            if ($status->isSoftError()) {
                $this->logger->warning('Remove page. Response code: ', ['code'=>$status->getCode(), 'url'=>$page->getUri()]);
                // Removing page directly from the database
                $this->pageRepository->deletePage($page);
                return $this;
            }
            if ($page->getAttempts() >= self::MAX_ATTEMPTS) {
                $this->logger->warning('Remove page (2).', ['code'=>$status->getCode(), 'url'=>$page->getUri()]);
                // Removing page directly from the database
                $this->pageRepository->deletePage($page);
            } else {
                $page->setAttempts($page->getAttempts() + 1);
                $this->pageRepository->save($page);
            }
        } elseif ($page->getAttempts() > 0) {
            $page->setAttempts(0);
            $this->pageRepository->save($page);
        }
        return $this;
    }

    /**
     * @param int $startTime
     * @return bool
     */
    private function isTimeout($startTime)
    {
        return (microtime(true) - $startTime) > $this->config->getJobRunThreshold();
    }

    /**
     * @param string $type
     * @param string $msg
     * @param string $file
     * @param string $line
     * @return void
     */
    public function errorHandler($type, $msg, $file, $line)
    {
        $message = $msg . " in " . $file . ":" . $line;
        $this->logger->err($message);

        $job = $this->logger->getJob();
        $this->finishJob($job, JobInterface::STATUS_ERROR);
    }

    /**
     * @param WarmRuleInterface $rule
     * @return PageInterface[]|\Mirasvit\CacheWarmer\Model\ResourceModel\Page\Collection
     */
    private function getPageCollection(WarmRuleInterface $rule)
    {
        $collection = $this->pageRepository->getCollection();
        $collection->getSelect()->where("main_rule_id = " . $rule->getId());
        $collection->getSelect()
            ->where("status = '' OR status = '".PageInterface::STATUS_PENDING."'
            OR (status = '".PageInterface::STATUS_UNCACHEABLE."' AND date_add(updated_at, INTERVAL 30 MINUTE) < NOW())");
        $collection->setOrder(PageInterface::POPULARITY, 'desc');
        $collection->getSelect()->limit(1000);

        return $collection;
    }

    /**
     * @param array $ids
     * @return PageInterface[]|\Mirasvit\CacheWarmer\Model\ResourceModel\Page\Collection
     */
    private function getPagesVariationsCollectionByIds($ids) {
        $uriHashArray = [];

        $collection = $this->pageRepository->getCollection()
            ->addFieldToSelect(PageInterface::URI_HASH)
            ->addFieldToFilter(PageInterface::ID, ['in' => $ids]);

        foreach ($collection as $page) {
            $uriHashArray[] = $page->getUriHash();
        }

        $variationsCollection = $this->pageRepository->getCollection()
            ->addFieldToFilter(PageInterface::URI_HASH, ['in' => $uriHashArray])
            ->addFieldToFilter(PageInterface::ID, ['nin' => $ids]);
        $variationsCollection->getSelect()->where("status = '' OR status = '".PageInterface::STATUS_PENDING."'
            OR (status = '".PageInterface::STATUS_UNCACHEABLE."' AND date_add(updated_at, INTERVAL 30 MINUTE) < NOW())");
        $variationsCollection->setOrder(PageInterface::POPULARITY, 'desc');
        $variationsCollection->getSelect()->limit(1000);

        return $variationsCollection;
    }

    /**
     * @param WarmRuleCollection $warmRules
     * @return array
     */
    private function getPageIdsByWarmRules(WarmRuleCollection $warmRules)
    {
        $warmRuleIds = [];
        $ids         = [];

        foreach ($warmRules as $rule) {
            $warmRuleIds[] = $rule->getId();
        }

        $collection = $this->pageRepository->getCollection();
        $collection->addFieldToFilter('main_rule_id', ['in' => $warmRuleIds]);
        $collection->getSelect()
            ->where("status = '' OR status = '".PageInterface::STATUS_PENDING."'
            OR (status = '".PageInterface::STATUS_UNCACHEABLE."' AND date_add(updated_at, INTERVAL 30 MINUTE) < NOW())");
        $collection->setOrder(PageInterface::POPULARITY, 'desc');
        $collection->getSelect()->limit(1000);

        foreach ($collection as $page) {
            $ids[] = $page->getId();
        }

        return $ids;
    }
}
