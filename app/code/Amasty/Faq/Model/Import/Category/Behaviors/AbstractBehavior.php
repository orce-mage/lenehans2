<?php

namespace Amasty\Faq\Model\Import\Category\Behaviors;

use Amasty\Base\Model\Import\AbstractImport;
use Amasty\Faq\Api\CategoryRepositoryInterface;
use Amasty\Faq\Api\Data\QuestionInterface;
use Amasty\Faq\Api\ImportExport\CategoryInterface;
use Amasty\Faq\Model\CategoryFactory;
use Amasty\Faq\Model\OptionSource\Category\Status;
use Amasty\Faq\Model\ResourceModel\Category\InsertDummyCategory;
use Amasty\Faq\Model\ResourceModel\Question\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

abstract class AbstractBehavior implements \Amasty\Base\Model\Import\Behavior\BehaviorInterface
{
    /**
     * @var CategoryRepositoryInterface
     */
    protected $repository;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var array
     */
    protected $stores = [];

    /**
     * @var CollectionFactory
     */
    protected $questionCollectionFactory;

    /**
     * @var InsertDummyCategory
     */
    protected $dummyCategory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        CategoryRepositoryInterface $repository,
        CategoryFactory $categoryFactory,
        CollectionFactory $questionCollectionFactory,
        InsertDummyCategory $dummyCategory,
        StoreManagerInterface $storeManager
    ) {
        $this->repository = $repository;
        $this->categoryFactory = $categoryFactory;
        $this->storeManager = $storeManager;
        $this->questionCollectionFactory = $questionCollectionFactory;
        $this->dummyCategory = $dummyCategory;
    }

    /**
     * @param \Amasty\Faq\Model\Category $category
     * @param array                      $categoryData
     */
    protected function setCategoryData(\Amasty\Faq\Model\Category $category, $categoryData = [])
    {
        $category->setTitle($categoryData[CategoryInterface::TITLE])
            ->setUrlKey(strtolower($categoryData[CategoryInterface::URL_KEY]));

        $stores = [];
        if (!empty($categoryData[CategoryInterface::STORE_CODES])) {
            $storeCodes = explode(
                AbstractImport::MULTI_VALUE_SEPARATOR,
                $categoryData[CategoryInterface::STORE_CODES]
            );
            foreach ($storeCodes as $code) {
                $stores[] = $this->stores[trim($code)];
            }
        }
        if (empty($stores)) {
            $stores[] = $this->storeManager->getDefaultStoreView()->getId();
        }
        $category->setData('store_ids', $stores);

        if (!empty($categoryData[CategoryInterface::STATUS])
            && $categoryData[CategoryInterface::STATUS] == Status::STATUS_ENABLED
        ) {
            $category->setStatus(Status::STATUS_ENABLED);
        } else {
            $category->setStatus(Status::STATUS_DISABLED);
        }

        if (!empty($categoryData[CategoryInterface::META_TITLE])) {
            $category->setMetaTitle($categoryData[CategoryInterface::META_TITLE]);
        }

        if (!empty($categoryData[CategoryInterface::META_DESCRIPTION])) {
            $category->setMetaDescription($categoryData[CategoryInterface::META_DESCRIPTION]);
        }

        if (!empty($categoryData[CategoryInterface::POSITION])) {
            $category->setPosition((int)$categoryData[CategoryInterface::POSITION]);
        } else {
            $category->setPosition(0);
        }

        $questions = [];
        if (!empty($categoryData[CategoryInterface::QUESTION_IDS])) {
            $questionIds = explode(
                AbstractImport::MULTI_VALUE_SEPARATOR,
                $categoryData[CategoryInterface::QUESTION_IDS]
            );
            foreach ($questionIds as &$questionId) {
                $questionId = (int)trim($questionId);
            }
            $questionCollection = $this->questionCollectionFactory->create();
            $questionCollection->addFieldToFilter(QuestionInterface::QUESTION_ID, ['in' => $questionIds]);
            $questionCollection->addFieldToSelect([QuestionInterface::QUESTION_ID]);
            foreach ($questionCollection->getData() as $question) {
                $questions[] = $question[QuestionInterface::QUESTION_ID];
            }
        }
        $category->setData('questions', $questions);
    }

    /**
     * @return void
     */
    protected function setStores()
    {
        if (empty($this->stores)) {
            $stores = $this->storeManager->getStores(true);
            foreach ($stores as $store) {
                $this->stores[$store->getCode()] = $store->getId();
            }
        }
    }
}
