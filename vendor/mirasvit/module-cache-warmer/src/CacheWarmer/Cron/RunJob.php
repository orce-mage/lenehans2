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

use Magento\Framework\Lock\LockManagerInterface;
use Mirasvit\CacheWarmer\Api\Data\JobInterface;
use Mirasvit\CacheWarmer\Api\Repository\JobRepositoryInterface;
use Mirasvit\CacheWarmer\Api\Service\JobServiceInterface;
use Mirasvit\CacheWarmer\Helper\LockManager;
use Mirasvit\CacheWarmer\Model\Config;
use Mirasvit\CacheWarmer\Model\JobFactory;

class RunJob
{
    private $jobRepository;

    private $jobService;

    private $config;

    private $lockManager;

    public function __construct(
        JobRepositoryInterface $jobRepository,
        JobServiceInterface $jobService,
        LockManager $lockManager,
        Config $config
    ) {
        $this->jobRepository = $jobRepository;
        $this->jobService    = $jobService;
        $this->lockManager   = $lockManager;
        $this->config        = $config;
    }

    /**
     * @return void
     */
    public function execute()
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        $lockName = 'mst-cache-warmer.cli.lock';

        if ($this->lockManager->lock($lockName)) {
            $collection = $this->jobRepository->getCollection();
            $collection->addFieldToFilter(JobInterface::STARTED_AT, ['null' => true]);

            foreach ($collection as $job) {
                $this->jobService->run($job, 'cron');
            }

            $this->lockManager->unlock($lockName);
        }
    }
}
