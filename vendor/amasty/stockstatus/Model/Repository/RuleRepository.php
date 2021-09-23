<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Repository;

use Amasty\Stockstatus\Api\Data\RuleInterface;
use Amasty\Stockstatus\Api\RuleRepositoryInterface;
use Amasty\Stockstatus\Model\RuleFactory;
use Amasty\Stockstatus\Model\ResourceModel\Rule as RuleResource;
use Amasty\Stockstatus\Model\ResourceModel\Rule\CollectionFactory;
use Amasty\Stockstatus\Model\ResourceModel\Rule\Collection;
use Amasty\Stockstatus\Model\Source\Status;
use Magento\Framework\EntityManager\Operation\Read\ReadExtensions;
use Magento\Framework\EntityManager\Operation\Update\UpdateExtensions;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RuleRepository implements RuleRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var RuleResource
     */
    private $ruleResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $rules;

    /**
     * @var CollectionFactory
     */
    private $ruleCollectionFactory;

    /**
     * @var ReadExtensions
     */
    private $readExtensions;

    /**
     * @var UpdateExtensions
     */
    private $updateExtensions;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        RuleFactory $ruleFactory,
        RuleResource $ruleResource,
        CollectionFactory $ruleCollectionFactory,
        ReadExtensions $readExtensions,
        UpdateExtensions $updateExtensions
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->ruleFactory = $ruleFactory;
        $this->ruleResource = $ruleResource;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->readExtensions = $readExtensions;
        $this->updateExtensions = $updateExtensions;
    }

    /**
     * @inheritdoc
     */
    public function save(RuleInterface $rule)
    {
        try {
            if ($rule->getId()) {
                $rule = $this->getById($rule->getId())->addData($rule->getData());
            }
            $this->ruleResource->save($rule);
            $this->updateExtensions->execute($rule);
            unset($this->rules[$rule->getId()]);
        } catch (\Exception $e) {
            if ($rule->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save rule with ID %1. Error: %2',
                        [$rule->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new rule. Error: %1', $e->getMessage()));
        }

        return $rule;
    }

    /**
     * @inheritdoc
     */
    public function getById($id, bool $withExtensions = false)
    {
        if (!isset($this->rules[$id])) {
            /** @var \Amasty\Stockstatus\Model\Rule $rule */
            $rule = $this->ruleFactory->create();
            $this->ruleResource->load($rule, $id);
            if (!$rule->getId()) {
                throw new NoSuchEntityException(__('Rule with specified ID "%1" not found.', $id));
            }
            $this->rules[$id] = $rule;
            if ($withExtensions) {
                $this->readExtensions->execute($rule);
            }
        }

        return $this->rules[$id];
    }

    /**
     * @inheritdoc
     */
    public function getNew()
    {
        return $this->ruleFactory->create();
    }

    /**
     * @inheritdoc
     */
    public function delete(RuleInterface $rule)
    {
        try {
            $this->ruleResource->delete($rule);
            unset($this->rules[$rule->getId()]);
        } catch (\Exception $e) {
            if ($rule->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove rule with ID %1. Error: %2',
                        [$rule->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove rule. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($id)
    {
        $ruleModel = $this->getById($id);
        $this->delete($ruleModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria, bool $withExtensions = false)
    {
        $searchResults = $this->searchResultsFactory->create();

        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\Stockstatus\Model\ResourceModel\Rule\Collection $ruleCollection */
        $ruleCollection = $this->ruleCollectionFactory->create();

        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $ruleCollection);
        }

        $searchResults->setTotalCount($ruleCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();

        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $ruleCollection);
        }

        $ruleCollection->setCurPage($searchCriteria->getCurrentPage());
        $ruleCollection->setPageSize($searchCriteria->getPageSize());

        $rules = [];
        /** @var RuleInterface $rule */
        foreach ($ruleCollection->getItems() as $rule) {
            $rules[] = $this->getById($rule->getId());
            if ($withExtensions) {
                $this->readExtensions->execute($rule);
            }
        }

        $searchResults->setItems($rules);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection  $ruleCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $ruleCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $ruleCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection  $ruleCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $ruleCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $ruleCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function duplicate(RuleInterface $rule): RuleInterface
    {
        $newRule = clone $rule;
        $newRule->setId(null);
        if ($newRule->isActivateQtyRanges()) {
            foreach ($newRule->getExtensionAttributes()->getRanges() as $range) {
                $range->setId(null);
            }
        }
        $newRule->setName(sprintf('Copy of %s', $newRule->getName()));
        $newRule->setStatus(Status::INACTIVE);

        return $this->save($newRule);
    }
}
