<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Repository;

use Amasty\Stockstatus\Api\Data\RangeInterface;
use Amasty\Stockstatus\Api\RangeRepositoryInterface;
use Amasty\Stockstatus\Model\RangeFactory;
use Amasty\Stockstatus\Model\ResourceModel\Range as RangeResource;
use Amasty\Stockstatus\Model\ResourceModel\Range\CollectionFactory;
use Amasty\Stockstatus\Model\ResourceModel\Range\Collection;
use Magento\Framework\Api\SearchResultsInterface;
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
class RangeRepository implements RangeRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var RangeFactory
     */
    private $rangeFactory;

    /**
     * @var RangeResource
     */
    private $rangeResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $ranges;

    /**
     * @var CollectionFactory
     */
    private $rangeCollectionFactory;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        RangeFactory $rangeFactory,
        RangeResource $rangeResource,
        CollectionFactory $rangeCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->rangeFactory = $rangeFactory;
        $this->rangeResource = $rangeResource;
        $this->rangeCollectionFactory = $rangeCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(RangeInterface $range): RangeInterface
    {
        try {
            if ($range->getId()) {
                $range = $this->getById((int) $range->getId())->addData($range->getData());
            }
            $this->rangeResource->save($range);
            unset($this->ranges[$range->getId()]);
        } catch (\Exception $e) {
            if ($range->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save range with ID %1. Error: %2',
                        [$range->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new range. Error: %1', $e->getMessage()));
        }

        return $range;
    }

    /**
     * @inheritdoc
     */
    public function getNew(): RangeInterface
    {
        return $this->rangeFactory->create();
    }

    /**
     * @inheritdoc
     */
    public function getById(int $id): RangeInterface
    {
        if (!isset($this->ranges[$id])) {
            /** @var \Amasty\Stockstatus\Model\Range $range */
            $range = $this->rangeFactory->create();
            $this->rangeResource->load($range, $id);
            if (!$range->getId()) {
                throw new NoSuchEntityException(__('Range with specified ID "%1" not found.', $id));
            }
            $this->ranges[$id] = $range;
        }

        return $this->ranges[$id];
    }

    /**
     * @inheritdoc
     */
    public function delete(RangeInterface $range): bool
    {
        try {
            $this->rangeResource->delete($range);
            unset($this->ranges[$range->getId()]);
        } catch (\Exception $e) {
            if ($range->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove range with ID %1. Error: %2',
                        [$range->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove range. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById(int $id): bool
    {
        $rangeModel = $this->getById($id);
        $this->delete($rangeModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\Stockstatus\Model\ResourceModel\Range\Collection $rangeCollection */
        $rangeCollection = $this->rangeCollectionFactory->create();

        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $rangeCollection);
        }

        $searchResults->setTotalCount($rangeCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();

        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $rangeCollection);
        }

        $rangeCollection->setCurPage($searchCriteria->getCurrentPage());
        $rangeCollection->setPageSize($searchCriteria->getPageSize());

        $ranges = [];
        /** @var RangeInterface $range */
        foreach ($rangeCollection->getItems() as $range) {
            $ranges[] = $this->getById((int) $range->getId());
        }

        $searchResults->setItems($ranges);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $rangeCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $rangeCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $rangeCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection $rangeCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $rangeCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $rangeCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }

    /**
     * @param int $ruleId
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function removeByRuleId(int $ruleId): bool
    {
        try {
            $this->rangeResource->deleteByRuleId($ruleId);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(
                __(
                    'Unable to remove ranges for Rule with ID %1. Error: %2',
                    [$ruleId, $e->getMessage()]
                )
            );
        }

        return true;
    }
}
