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

use Magento\Framework\DB\Select;
use Mirasvit\CacheWarmer\Api\Data\PageInterface;
use Mirasvit\CacheWarmer\Api\Data\WarmRuleInterface;
use Mirasvit\CacheWarmer\Api\Repository\PageRepositoryInterface;
use Mirasvit\CacheWarmer\Api\Repository\WarmRuleRepositoryInterface;
use Mirasvit\CacheWarmer\Helper\Serializer;

class WarmRuleService
{
    private $ruleRepository;

    private $serializer;

    private $compatibilityService;

    private $pageRepository;

    public function __construct(
        PageRepositoryInterface $pageRepository,
        WarmRuleRepositoryInterface $ruleRepository,
        \Mirasvit\Core\Service\CompatibilityService $compatibilityService,
        Serializer $serializer
    ) {
        $this->ruleRepository       = $ruleRepository;
        $this->pageRepository       = $pageRepository;
        $this->compatibilityService = $compatibilityService;
        $this->serializer           = $serializer;
    }

    /**
     * @param PageInterface     $page
     * @param WarmRuleInterface $rule
     * @return PageInterface
     */
    public function modifyPage(PageInterface $page, WarmRuleInterface $rule = null)
    {
        if (!$rule) {
            return $page;
        }

        if (!$rule->getHeaders()) {
            return $page;
        }
        $p = clone $page;
        if ($rule->getHeaders()) {
            $p->setHeaders($rule->getHeaders());
        }
        return $p;
    }

    /**
     * @return void
     */
    public function refreshPagesByRules()
    {
        $jobRuleCollection = $this->ruleRepository->getCollection()->setOrder(WarmRuleInterface::PRIORITY);
        $versions          = [];
        foreach ($jobRuleCollection as $rule) {
            $versions[] = $rule->getConditionsSerialized();
        }
        $version = sha1(implode("|", $versions));

        $pageCollection = $this->pageRepository->getCollection();
        $pageCollection->getSelect()
            ->where(PageInterface::WARM_RULE_VERSION .
                " != ? OR ISNULL(" . PageInterface::WARM_RULE_VERSION
                . ") OR main_rule_id IS NULL", $version)
            ->order(PageInterface::POPULARITY . ' DESC')
            ->limit(10000);

        while ($page = $pageCollection->fetchItem()) {
            /** @var PageInterface $page */
            $ruleIds = [];
            $mainRuleId = null;

            foreach ($jobRuleCollection as $jobRule) {
                if ($jobRule->getRule()->validate($page)) {
                    $ruleIds[] = $jobRule->getId();
                    if (!$mainRuleId) {
                        $mainRuleId = $jobRule->getId();
                    }
                }
            }

            $page->setWarmRuleIds($ruleIds);
            $page->setMainRuleId($mainRuleId);
            $page->setWarmRuleVersion($version);
            $this->pageRepository->save($page);
        }
    }
}
