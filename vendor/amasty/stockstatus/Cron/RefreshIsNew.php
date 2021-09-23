<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Cron;

use Amasty\Stockstatus\Model\Indexer\Rule\RuleProcessor;
use Amasty\Stockstatus\Model\ResourceModel\Rule as RuleResource;

class RefreshIsNew
{
    /**
     * @var RuleProcessor
     */
    private $ruleProcessor;

    /**
     * @var RuleResource
     */
    private $ruleResource;

    public function __construct(
        RuleProcessor $ruleProcessor,
        RuleResource $ruleResource
    ) {
        $this->ruleProcessor = $ruleProcessor;
        $this->ruleResource = $ruleResource;
    }

    public function execute(): void
    {
        if ($ruleIds = $this->ruleResource->getWithNewCondition()) {
            $this->ruleProcessor->reindexList($ruleIds, true);
        }
    }
}
