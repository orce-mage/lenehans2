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
use Mirasvit\CacheWarmer\Api\Service\StatusServiceInterface;
use Mirasvit\CacheWarmer\Api\Service\PageServiceInterface;
use Mirasvit\CacheWarmer\Api\Repository\PageRepositoryInterface;
use Mirasvit\CacheWarmer\Api\Data\PageInterface;
use Mirasvit\CacheWarmer\Api\Repository\WarmRuleRepositoryInterface;
use Mirasvit\CacheWarmer\Api\Data\WarmRuleInterface;
use Mirasvit\CacheWarmer\Model\Config;
use Mirasvit\CacheWarmer\Logger\Logger;

class StatusService implements StatusServiceInterface
{
    /**
     * @var PageServiceInterface
     */
    private $pageService;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var WarmRuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * StatusService constructor.
     * @param PageServiceInterface $pageService
     * @param PageRepositoryInterface $pageRepository
     * @param WarmRuleRepositoryInterface $ruleRepository
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(
        PageServiceInterface $pageService,
        PageRepositoryInterface $pageRepository,
        WarmRuleRepositoryInterface $ruleRepository,
        Config $config,
        Logger $logger
    ) {
        $this->pageService = $pageService;
        $this->pageRepository = $pageRepository;
        $this->ruleRepository = $ruleRepository;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @return void
     */
    public function runFullStatusUpdate()
    {
        $collection = $this->pageRepository->getCollection();
        foreach ($collection as $page) {
            $this->pageService->isCached($page);
        }
        /** mp comment start */
        echo "Updated ".$collection->count()." pages\n";
        /** mp comment end */
    }

    /**
     * @return void
     */
    public function runPartialStatusUpdate()
    {
        $this->logger->info("Start cache status update");
        $jobRuleCollection = $this->ruleRepository->getCollection();
        $jobRuleCollection->setOrder(WarmRuleInterface::PRIORITY, "DESC");

        $start = time();
        $timelimit = $this->config->getJobRunThreshold()/2;

        foreach ($jobRuleCollection as $rule) {
            $collection = $this->pageRepository->getCollection();
            $collection->getSelect()->where("status = ''");
            $collection->getSelect()->where("FIND_IN_SET(?,warm_rule_ids)", $rule->getId());
            $collection->getSelect()->limit(100);

            if ($collection->count()) {
                foreach ($collection as $page) {
                    $this->pageService->isCached($page);
                    if (time() - $start < $timelimit) {
                        break 2;
                    }
                }
            } else {
                while (time() - $start < $timelimit) {
                    $updatedCnt = $this->runUpdate($rule->getId());
                    if ($updatedCnt < 5) { //near 5% is allowed mistake
                        break;
                    }
                }
            }
        }
        while (time() - $start < $timelimit) {
            $updatedCnt = $this->runUpdate(0);
            if ($updatedCnt < 5) { //near 5% is allowed mistake
                break;
            }
        }
        $this->logger->info("Finish cache status update");
    }

    /**
     * @param int $ruleId
     * @return int
     */
    protected function runUpdate($ruleId)
    {
        $collection = $this->pageRepository->getCollection();
        if ($ruleId) {
            $collection->getSelect()->where("FIND_IN_SET(?,warm_rule_ids)", $ruleId);
        } else {
            $collection->getSelect()->where("warm_rule_ids = ''");
        }
        $collection->setOrder(PageInterface::UPDATED_AT, "asc");
        $collection->getSelect()->limit(100);
        $updatedCnt = 0;
        foreach ($collection as $page) {
            $before = $page->getStatus();
            $this->pageService->isCached($page);
            if ($page->getStatus() != $before) {
                $updatedCnt++;
            }
        }
        return $updatedCnt;
    }
}
