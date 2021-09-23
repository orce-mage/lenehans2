<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Rule;

use Amasty\Stockstatus\Api\Data\RuleInterface;
use Amasty\Stockstatus\Api\Rule\GetByProductIdAndStoreIdInterface;
use Amasty\Stockstatus\Api\RuleRepositoryInterface;
use Amasty\Stockstatus\Model\ResourceModel\RuleIndex;
use Amasty\Stockstatus\Model\Source\Status;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class GetRuleForProduct implements GetByProductIdAndStoreIdInterface
{
    /**
     * @var array
     */
    private $rulesCache = [];

    /**
     * @var RuleIndex
     */
    private $ruleIndex;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    public function __construct(
        RuleRepositoryInterface $ruleRepository,
        RuleIndex $ruleIndex,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        CustomerSession $customerSession
    ) {
        $this->ruleIndex = $ruleIndex;
        $this->ruleRepository = $ruleRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->customerSession = $customerSession;
    }

    public function execute(int $productId, int $storeId): ?RuleInterface
    {
        if (!isset($this->rulesCache[$storeId][$productId])) {
            $ruleIds = $this->ruleIndex->getAppliedRules($productId, $storeId);

            /** @var RuleInterface[] $rules */
            $rules = $this->ruleRepository->getList($this->getSearchCriteria($ruleIds))->getItems();

            $appliedRule = null;
            foreach ($rules as $ruleCandidate) {
                if ($this->checkAdditionalConditions($ruleCandidate)) {
                    $appliedRule = $ruleCandidate;
                    break;
                }
            }

            $this->rulesCache[$storeId][$productId] = $appliedRule;
        }

        return $this->rulesCache[$storeId][$productId];
    }

    private function getSearchCriteria(array $ruleIds): SearchCriteria
    {
        $this->sortOrderBuilder->setField(RuleInterface::PRIORITY);
        $this->sortOrderBuilder->setAscendingDirection();
        $orderByPriority = $this->sortOrderBuilder->create();

        $this->searchCriteriaBuilder->addFilter(RuleInterface::ID, $ruleIds, 'in');
        $this->searchCriteriaBuilder->addFilter(RuleInterface::STATUS, Status::ACTIVE, 'eq');
        $this->searchCriteriaBuilder->addSortOrder($orderByPriority);

        return $this->searchCriteriaBuilder->create();
    }

    /**
     * Check non-indexed conditions for rule before apply.
     * @param RuleInterface $rule
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function checkAdditionalConditions(RuleInterface $rule): bool
    {
        return in_array($this->customerSession->getCustomerGroupId(), $rule->getCustomerGroups());
    }
}
