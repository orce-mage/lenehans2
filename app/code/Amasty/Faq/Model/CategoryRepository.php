<?php

namespace Amasty\Faq\Model;

use Amasty\Faq\Api\CategoryRepositoryInterface;
use Amasty\Faq\Api\Data\CategoryInterface;
use Amasty\Faq\Api\Data\CategorySearchResultsInterfaceFactory;
use Amasty\Faq\Model\ResourceModel\Category as CategoryResource;
use Amasty\Faq\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryRepository implements CategoryRepositoryInterface
{
    /**
     * @var CategorySearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var CategoryResource
     */
    private $categoryResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $categories;

    /**
     * @var CollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    public function __construct(
        CategorySearchResultsInterfaceFactory $searchResultsFactory,
        CategoryFactory $categoryFactory,
        CategoryResource $categoryResource,
        CollectionFactory $categoryCollectionFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->categoryFactory = $categoryFactory;
        $this->categoryResource = $categoryResource;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritdoc
     */
    public function save(CategoryInterface $category)
    {
        try {
            if ($category->getCategoryId()) {
                $category = $this->getById($category->getCategoryId())->addData($category->getData());
            }
            $this->categoryResource->save($category);
            unset($this->categories[$category->getCategoryId()]);
        } catch (\Exception $e) {
            if ($category->getCategoryId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save category with ID %1. Error: %2',
                        [$category->getCategoryId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new category. Error: %1', $e->getMessage()));
        }

        return $category;
    }

    /**
     * @inheritdoc
     */
    public function getById($categoryId)
    {
        if (!isset($this->categories[$categoryId])) {
            /** @var \Amasty\Faq\Model\Category $category */
            $category = $this->categoryFactory->create();
            $this->categoryResource->load($category, $categoryId);
            if (!$category->getCategoryId()) {
                throw new NoSuchEntityException(__('Category with specified ID "%1" not found.', $categoryId));
            }
            $this->categories[$categoryId] = $category;
        }

        return $this->categories[$categoryId];
    }

    /**
     * @inheritdoc
     */
    public function delete(CategoryInterface $category)
    {
        try {
            $this->categoryResource->delete($category);
            unset($this->categories[$category->getCategoryId()]);
        } catch (\Exception $e) {
            if ($category->getCategoryId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove category with ID %1. Error: %2',
                        [$category->getCategoryId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove category. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($categoryId)
    {
        $categoryModel = $this->getById($categoryId);
        $this->delete($categoryModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $categoryCollection = $this->categoryCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $categoryCollection);
        $searchResults->setTotalCount($categoryCollection->getSize());

        $categories = [];
        /** @var CategoryInterface $category */
        foreach ($categoryCollection->getItems() as $category) {
            $categories[] = $this->getById($category->getId());
        }
        $searchResults->setItems($categories);

        return $searchResults;
    }
}
