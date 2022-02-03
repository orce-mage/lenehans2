<?php

namespace Amasty\Faq\Model\Import\Question\Behaviors;

use Amasty\Faq\Api\ImportExport\QuestionInterface;
use Amasty\Faq\Api\QuestionRepositoryInterface;
use Amasty\Faq\Model\QuestionFactory;
use Amasty\Faq\Model\ResourceModel\Category\CollectionFactory;
use Amasty\Faq\Model\ResourceModel\Question\InsertDummyQuestion;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class AddUpdate extends AbstractBehavior
{
    /**
     * @var Add
     */
    private $addQuestion;

    public function __construct(
        Add $addQuestion,
        QuestionRepositoryInterface $repository,
        QuestionFactory $questionFactory,
        CollectionFactory $categoryCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        InsertDummyQuestion $dummyQuestion,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct(
            $repository,
            $questionFactory,
            $categoryCollectionFactory,
            $productCollectionFactory,
            $dummyQuestion,
            $storeManager,
            $scopeConfig
        );
        $this->addQuestion = $addQuestion;
    }

    /**
     * @param array $importData
     *
     * @return void
     */
    public function execute(array $importData)
    {
        $this->setStores();
        $questionsToCreate = [];
        foreach ($importData as $questionData) {
            $question = null;
            $questionData[QuestionInterface::QUESTION_ID] = (int)$questionData[QuestionInterface::QUESTION_ID];
            if (!empty($questionData[QuestionInterface::QUESTION_ID])) {
                try {
                    $question = $this->repository->getById($questionData[QuestionInterface::QUESTION_ID]);
                } catch (NoSuchEntityException $e) {
                    $dummyQuestion = $this->questionFactory->create();
                    $dummyQuestion->setQuestionId($questionData[QuestionInterface::QUESTION_ID]);
                    $this->dummyQuestion->save($dummyQuestion);
                    try {
                        $question = $this->repository->getById($questionData[QuestionInterface::QUESTION_ID]);
                    } catch (NoSuchEntityException $e) {
                        null;
                    }
                }

                if ($question) {
                    $this->setQuestionData($question, $questionData);
                    try {
                        $this->repository->save($question);
                    } catch (CouldNotSaveException $e) {
                        null;
                    }
                }
            } else {
                $questionsToCreate[] = $questionData;
            }
        }

        if (!empty($questionsToCreate)) {
            $this->addQuestion->execute($questionsToCreate);
        }
    }
}
