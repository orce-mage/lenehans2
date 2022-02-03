<?php

namespace Amasty\Faq\Model;

use Amasty\Faq\Api\Data\QuestionInterface;
use Amasty\Faq\Api\Data\QuestionSearchResultsInterfaceFactory;
use Amasty\Faq\Api\QuestionRepositoryInterface;
use Amasty\Faq\Model\OptionSource\Question\Status;
use Amasty\Faq\Model\ResourceModel\Question as QuestionResource;
use Amasty\Faq\Model\ResourceModel\Question\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuestionRepository implements QuestionRepositoryInterface
{
    /**
     * @var QuestionSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var QuestionFactory
     */
    private $questionFactory;

    /**
     * @var QuestionResource
     */
    private $questionResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $questions;

    /**
     * @var CollectionFactory
     */
    private $questionCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    public function __construct(
        QuestionSearchResultsInterfaceFactory $searchResultsFactory,
        QuestionFactory $questionFactory,
        QuestionResource $questionResource,
        CollectionFactory $questionCollectionFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->questionFactory = $questionFactory;
        $this->questionResource = $questionResource;
        $this->questionCollectionFactory = $questionCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritdoc
     */
    public function save(QuestionInterface $question)
    {
        try {
            $question = $this->prepareQuestionForSave($question);

            $this->questionResource->save($question);
            unset($this->questions[$question->getQuestionId()]);
        } catch (\Exception $e) {
            if ($question->getQuestionId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save question with ID %1. Error: %2',
                        [$question->getQuestionId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new question. Error: %1', $e->getMessage()));
        }

        return $question;
    }

    /**
     * @inheritdoc
     */
    public function getById($questionId)
    {
        if (!isset($this->questions[$questionId])) {
            /** @var \Amasty\Faq\Model\Question $question */
            $question = $this->questionFactory->create();
            $this->questionResource->load($question, $questionId);
            if (!$question->getQuestionId()) {
                throw new NoSuchEntityException(__('Question with specified ID "%1" not found.', $questionId));
            }
            $this->questions[$questionId] = $question;
        }

        return $this->questions[$questionId];
    }

    /**
     * @inheritdoc
     */
    public function delete(QuestionInterface $question)
    {
        try {
            $this->questionResource->delete($question);
            unset($this->questions[$question->getQuestionId()]);
        } catch (\Exception $e) {
            if ($question->getQuestionId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove question with ID %1. Error: %2',
                        [$question->getQuestionId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove question. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($questionId)
    {
        $questionModel = $this->getById($questionId);
        $this->delete($questionModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $questionCollection = $this->questionCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $questionCollection);
        $searchResults->setTotalCount($questionCollection->getSize());

        $questions = [];
        /** @var QuestionInterface $question */
        foreach ($questionCollection->getItems() as $question) {
            $questions[] = $this->getById($question->getId());
        }
        $searchResults->setItems($questions);

        return $searchResults;
    }

    /**
     * @param QuestionInterface $question
     *
     * @return QuestionInterface|mixed
     */
    private function prepareQuestionForSave(QuestionInterface $question)
    {
        if ($question->getQuestionId()) {
            $savedQuestion = $this->getById($question->getQuestionId());

            $changeStatus = $question->getAnswer()
                && !$savedQuestion->getAnswer()
                && $question->getStatus() == $savedQuestion->getStatus();

            $savedQuestion->addData($question->getData());

            if ($changeStatus) {
                $savedQuestion->setStatus(Status::STATUS_ANSWERED);
            }

            return $savedQuestion;
        }

        return $question;
    }
}
