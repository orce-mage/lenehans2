<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Backend\Rule\Initialization;

use Amasty\Stockstatus\Api\Data\RangeInterface;
use Amasty\Stockstatus\Api\Data\RuleInterface;
use Amasty\Stockstatus\Api\RangeRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class RuleProcessor
{
    /**
     * @var RangeRepositoryInterface
     */
    private $rangeRepository;

    /**
     * @var RetrieveData
     */
    private $retrieveData;

    public function __construct(
        RangeRepositoryInterface $rangeRepository,
        RetrieveData $retrieveData
    ) {
        $this->rangeRepository = $rangeRepository;
        $this->retrieveData = $retrieveData;
    }

    public function execute(RuleInterface $rule, array $inputRuleData)
    {
        if ($rule->isActivateQtyRanges()) {
            $this->populateRanges($rule, $inputRuleData);
        }
    }

    private function populateRanges(RuleInterface $rule, array $inputRuleData): void
    {
        /** @var RangeInterface[] $ranges */
        $ranges = [];
        $newRanges = [];
        foreach ($this->retrieveData->execute($inputRuleData) as $rangeData) {
            $rangeId = (int)($rangeData[RangeInterface::ID] ?? 0);
            try {
                $range = $this->rangeRepository->getById($rangeId);
            } catch (NoSuchEntityException $e) {
                $range = $this->rangeRepository->getNew();
            }
            $range->addData($rangeData);
            $ranges[] = $range;
            $newRanges[] = $range->getId();
        }

        $oldRanges = $rule->getExtensionAttributes()->getRanges() ?: [];
        // update ranges array with deleted ranges
        foreach ($oldRanges as $range) {
            if (!in_array($range->getId(), $newRanges)) {
                $range->isDeleted(true);
                $ranges[] = $range;
            }
        }

        $extensionAttributes = $rule->getExtensionAttributes();
        $extensionAttributes->setRanges($ranges);
    }
}
