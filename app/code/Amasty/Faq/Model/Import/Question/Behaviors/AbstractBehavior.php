<?php

namespace Amasty\Faq\Model\Import\Question\Behaviors;

use Amasty\Base\Model\Import\AbstractImport;
use Amasty\Faq\Api\ImportExport\CategoryInterface;
use Amasty\Faq\Api\ImportExport\QuestionInterface;
use Amasty\Faq\Api\QuestionRepositoryInterface;
use Amasty\Faq\Model\OptionSource\Question\Status;
use Amasty\Faq\Model\OptionSource\Question\Visibility;
use Amasty\Faq\Model\QuestionFactory;
use Amasty\Faq\Model\ResourceModel\Category\CollectionFactory;
use Amasty\Faq\Model\ResourceModel\Question\InsertDummyQuestion;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

abstract class AbstractBehavior implements \Amasty\Base\Model\Import\Behavior\BehaviorInterface
{
    /**
     * @var array
     */
    protected $stores = [];

    /**
     * @var QuestionRepositoryInterface
     */
    protected $repository;

    /**
     * @var QuestionFactory
     */
    protected $questionFactory;

    /**
     * @var CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var InsertDummyQuestion
     */
    protected $dummyQuestion;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        QuestionRepositoryInterface $repository,
        QuestionFactory $questionFactory,
        CollectionFactory $categoryCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        InsertDummyQuestion $dummyQuestion,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->repository = $repository;
        $this->questionFactory = $questionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->dummyQuestion = $dummyQuestion;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param \Amasty\Faq\Model\Question $question
     * @param array                      $questionData
     */
    protected function setQuestionData(\Amasty\Faq\Model\Question $question, $questionData = [])
    {
        $question->setTitle($questionData[QuestionInterface::QUESTION])
            ->setAnswer($questionData[QuestionInterface::ANSWER])
            ->setUrlKey(strtolower($questionData[QuestionInterface::URL_KEY]));

        if (!empty($questionData[QuestionInterface::SHORT_ANSWER])) {
            $question->setShortAnswer($questionData[QuestionInterface::SHORT_ANSWER]);
        }

        if (!empty($questionData[QuestionInterface::NAME])) {
            $question->setName($questionData[QuestionInterface::NAME]);
        }

        if (!empty($questionData[QuestionInterface::EMAIL])) {
            $question->setEmail($questionData[QuestionInterface::EMAIL]);
        }

        $stores = [];
        if (!empty($questionData[QuestionInterface::STORE_CODES])) {
            $storeCodes = explode(
                AbstractImport::MULTI_VALUE_SEPARATOR,
                $questionData[QuestionInterface::STORE_CODES]
            );
            foreach ($storeCodes as $code) {
                $stores[] = $this->stores[trim($code)];
            }
        }
        if (empty($stores)) {
            $stores[] = $this->storeManager->getDefaultStoreView()->getId();
        }
        $question->setData(QuestionInterface::STORES, $stores);

        if (!empty($questionData[QuestionInterface::STATUS])
            && $questionData[QuestionInterface::STATUS] == Status::STATUS_ANSWERED
        ) {
            $question->setStatus(Status::STATUS_ANSWERED);
        } else {
            $question->setStatus(Status::STATUS_PENDING);
        }

        if (empty($questionData[QuestionInterface::VISIBILITY])) {
            $questionData[QuestionInterface::VISIBILITY] = Visibility::VISIBILITY_NONE;
        }
        switch ((int)$questionData[QuestionInterface::VISIBILITY]) {
            case Visibility::VISIBILITY_PUBLIC:
                $question->setVisibility(Visibility::VISIBILITY_PUBLIC);
                break;
            case Visibility::VISIBILITY_FOR_LOGGED:
                $question->setVisibility(Visibility::VISIBILITY_FOR_LOGGED);
                break;
            default:
                $question->setVisibility(Visibility::VISIBILITY_NONE);
                break;
        }

        if (!empty($questionData[QuestionInterface::META_TITLE])) {
            $question->setMetaTitle($questionData[QuestionInterface::META_TITLE]);
        }

        if (!empty($questionData[QuestionInterface::META_DESCRIPTION])) {
            $question->setMetaDescription($questionData[QuestionInterface::META_DESCRIPTION]);
        }

        if (!empty($questionData[QuestionInterface::POSITION])) {
            $question->setPosition((int)$questionData[QuestionInterface::POSITION]);
        } else {
            $question->setPosition(0);
        }

        $categories = [];
        if (!empty($questionData[QuestionInterface::CATEGORY_IDS])) {
            $categoryIds = explode(
                AbstractImport::MULTI_VALUE_SEPARATOR,
                $questionData[QuestionInterface::CATEGORY_IDS]
            );
            foreach ($categoryIds as &$categoryId) {
                $categoryId = (int)trim($categoryId);
            }
            $categoryCollection = $this->categoryCollectionFactory->create();
            $categoryCollection->addFieldToFilter(CategoryInterface::CATEGORY_ID, ['in' => $categoryIds]);
            $categoryCollection->addFieldToSelect([CategoryInterface::CATEGORY_ID]);
            foreach ($categoryCollection->getData() as $category) {
                $categories[] = $category[CategoryInterface::CATEGORY_ID];
            }
        }
        $question->setData(QuestionInterface::CATEGORIES, $categories);

        $products = [];
        if (!empty($questionData[QuestionInterface::PRODUCT_SKUS])) {
            $productSkus = explode(
                AbstractImport::MULTI_VALUE_SEPARATOR,
                $questionData[QuestionInterface::PRODUCT_SKUS]
            );
            foreach ($productSkus as &$productSku) {
                $productSku = trim($productSku);
            }
            $productCollection = $this->productCollectionFactory->create();
            $productCollection->addFieldToFilter(ProductInterface::SKU, ['in' => $productSkus]);
            $productCollection->addFieldToSelect(['entity_id']);
            foreach ($productCollection->getData() as $product) {
                $products[] = $product['entity_id'];
            }
        }
        $question->setData('product_ids', $products);

        $question->setData('tag_ids', []);
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
