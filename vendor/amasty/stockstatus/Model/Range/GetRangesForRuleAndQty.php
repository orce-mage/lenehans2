<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Range;

use Amasty\Stockstatus\Api\Data\RangeInterface;
use Amasty\Stockstatus\Api\RangeRepositoryInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;

class GetRangesForRuleAndQty
{
    /**
     * @var RangeRepositoryInterface
     */
    private $rangeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    public function __construct(
        RangeRepositoryInterface $rangeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->rangeRepository = $rangeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * @param int $ruleId
     * @param float $qty
     * @return RangeInterface[]|null
     */
    public function execute(int $ruleId, float $qty): ?array
    {
        return $this->rangeRepository->getList($this->getSearchCriteria($ruleId, $qty))->getItems();
    }

    private function getSearchCriteria(int $ruleId, float $qty): SearchCriteria
    {
        $sortByQtyFrom = $this->sortOrderBuilder
            ->setField(RangeInterface::FROM)
            ->setAscendingDirection()
            ->create();
        $sortByQtyAsc = $this->sortOrderBuilder
            ->setField(RangeInterface::TO)
            ->setAscendingDirection()
            ->create();
        $this->searchCriteriaBuilder->addSortOrder($sortByQtyFrom);
        $this->searchCriteriaBuilder->addSortOrder($sortByQtyAsc);

        $this->searchCriteriaBuilder->addFilter(RangeInterface::RULE_ID, $ruleId);
        $this->searchCriteriaBuilder->addFilter(RangeInterface::FROM, $qty, 'lteq');
        $this->searchCriteriaBuilder->addFilter(RangeInterface::TO, $qty, 'gteq');

        return $this->searchCriteriaBuilder->create();
    }
}
