<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Ui\DataProvider\Rule\Form\Data\Range;

use Amasty\Stockstatus\Api\Data\RangeInterface;
use Amasty\Stockstatus\Api\RangeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;

class MainRangeProvider implements RangeProviderInterface
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
     * @return RangeInterface[]
     */
    public function execute(int $ruleId): array
    {
        $this->searchCriteriaBuilder->addFilter(RangeInterface::RULE_ID, $ruleId);
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

        return $this->rangeRepository->getList($this->searchCriteriaBuilder->create())->getItems();
    }
}
