<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Extensions\Range;

use Amasty\Stockstatus\Api\Data\RangeInterface;
use Amasty\Stockstatus\Api\RangeRepositoryInterface;
use Amasty\Stockstatus\Model\Rule;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class ReadHandler implements ExtensionInterface
{
    /**
     * @var RangeRepositoryInterface
     */
    private $rangeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    public function __construct(
        RangeRepositoryInterface $rangeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->rangeRepository = $rangeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param Rule|object $entity
     * @param array $arguments
     * @return Rule|bool|object|void
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->isActivateQtyRanges()) {
            $extensionAttributes = $entity->getExtensionAttributes();
            $extensionAttributes->setRanges($this->getRangesByRuleId((int) $entity->getId()));
        }

        return $entity;
    }

    /**
     * @param int $ruleId
     * @return RangeInterface[]
     */
    private function getRangesByRuleId(int $ruleId): array
    {
        $this->searchCriteriaBuilder->addFilter(RangeInterface::RULE_ID, $ruleId);
        return $this->rangeRepository->getList($this->searchCriteriaBuilder->create())->getItems();
    }
}
