<?php

namespace Amasty\Faq\Model\Import\Category\Behaviors;

use Amasty\Faq\Api\CategoryRepositoryInterface;
use Amasty\Faq\Api\ImportExport\CategoryInterface;
use Amasty\Faq\Model\CategoryFactory;
use Amasty\Faq\Model\ResourceModel\Category\InsertDummyCategory;
use Amasty\Faq\Model\ResourceModel\Question\CollectionFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class AddUpdate extends AbstractBehavior
{
    /**
     * @var \Amasty\Faq\Model\Import\Category\Behaviors\Add
     */
    private $addCategory;

    public function __construct(
        Add $addCategory,
        CategoryRepositoryInterface $repository,
        CategoryFactory $categoryFactory,
        CollectionFactory $questionCollectionFactory,
        InsertDummyCategory $dummyCategory,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($repository, $categoryFactory, $questionCollectionFactory, $dummyCategory, $storeManager);
        $this->addCategory = $addCategory;
    }

    /**
     * @param array $importData
     *
     * @return void
     */
    public function execute(array $importData)
    {
        $this->setStores();
        $categoriesToCreate = [];
        foreach ($importData as $categoryData) {
            $category = null;
            $categoryData[CategoryInterface::CATEGORY_ID] = (int)$categoryData[CategoryInterface::CATEGORY_ID];
            if (!empty($categoryData[CategoryInterface::CATEGORY_ID])) {
                try {
                    $category = $this->repository->getById($categoryData[CategoryInterface::CATEGORY_ID]);
                } catch (NoSuchEntityException $e) {
                    $dummyCategory = $this->categoryFactory->create();
                    $dummyCategory->setCategoryId($categoryData[CategoryInterface::CATEGORY_ID]);
                    $this->dummyCategory->save($dummyCategory);
                    try {
                        $category = $this->repository->getById($categoryData[CategoryInterface::CATEGORY_ID]);
                    } catch (NoSuchEntityException $e) {
                        null;
                    }
                }

                if ($category) {
                    $this->setCategoryData($category, $categoryData);
                    try {
                        $this->repository->save($category);
                    } catch (CouldNotSaveException $e) {
                        null;
                    }
                }
            } else {
                $categoriesToCreate[] = $categoryData;
            }
        }

        if (!empty($categoriesToCreate)) {
            $this->addCategory->execute($categoriesToCreate);
        }
    }
}
