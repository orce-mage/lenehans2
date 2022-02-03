<?php

namespace Amasty\Faq\Model;

use Amasty\Faq\Api\Data\TagInterface;
use Amasty\Faq\Api\TagRepositoryInterface;
use Amasty\Faq\Model\ResourceModel\Tag as TagResource;
use Amasty\Faq\Model\ResourceModel\Tag\Collection;
use Amasty\Faq\Model\ResourceModel\Tag\CollectionFactory;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TagRepository implements TagRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var TagFactory
     */
    private $tagFactory;

    /**
     * @var TagResource
     */
    private $tagResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $tags;

    /**
     * @var CollectionFactory
     */
    private $tagCollectionFactory;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        TagFactory $tagFactory,
        TagResource $tagResource,
        CollectionFactory $tagCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->tagFactory = $tagFactory;
        $this->tagResource = $tagResource;
        $this->tagCollectionFactory = $tagCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(TagInterface $tag)
    {
        try {
            if ($tag->getTagId()) {
                $tag = $this->getById($tag->getTagId())->addData($tag->getData());
            }
            $this->tagResource->save($tag);
            unset($this->tags[$tag->getTagId()]);
        } catch (\Exception $e) {
            if ($tag->getTagId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save tag with ID %1. Error: %2',
                        [$tag->getTagId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new tag. Error: %1', $e->getMessage()));
        }

        return $tag;
    }

    /**
     * @inheritdoc
     */
    public function getById($tagId)
    {
        if (!isset($this->tags[$tagId])) {
            /** @var \Amasty\Faq\Model\Tag $tag */
            $tag = $this->tagFactory->create();
            $this->tagResource->load($tag, $tagId);
            if (!$tag->getTagId()) {
                throw new NoSuchEntityException(__('Tag with specified ID "%1" not found.', $tagId));
            }
            $this->tags[$tagId] = $tag;
        }

        return $this->tags[$tagId];
    }

    /**
     * @inheritdoc
     */
    public function delete(TagInterface $tag)
    {
        try {
            $this->tagResource->delete($tag);
            unset($this->tags[$tag->getTagId()]);
        } catch (\Exception $e) {
            if ($tag->getTagId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove tag with ID %1. Error: %2',
                        [$tag->getTagId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove tag. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($tagId)
    {
        $tagModel = $this->getById($tagId);
        $this->delete($tagModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\Faq\Model\ResourceModel\Tag\Collection $tagCollection */
        $tagCollection = $this->tagCollectionFactory->create();
        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $tagCollection);
        }
        $searchResults->setTotalCount($tagCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $tagCollection);
        }
        $tagCollection->setCurPage($searchCriteria->getCurrentPage());
        $tagCollection->setPageSize($searchCriteria->getPageSize());
        $tags = [];
        /** @var TagInterface $tag */
        foreach ($tagCollection->getItems() as $tag) {
            $tags[] = $this->getById($tag->getId());
        }
        $searchResults->setItems($tags);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection  $tagCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $tagCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $tagCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection  $tagCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $tagCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $tagCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? 'DESC' : 'ASC'
            );
        }
    }
}
