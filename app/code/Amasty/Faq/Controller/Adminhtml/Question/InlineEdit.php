<?php

namespace Amasty\Faq\Controller\Adminhtml\Question;

use Amasty\Faq\Api\Data\QuestionInterface;
use Amasty\Faq\Api\QuestionRepositoryInterface;

class InlineEdit extends \Amasty\Faq\Controller\Adminhtml\AbstractQuestion
{
    /**
     * @var QuestionRepositoryInterface
     */
    private $repository;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        QuestionRepositoryInterface $repository
    ) {
        parent::__construct($context);
        $this->repository = $repository;
    }

    /**
     * Inline edit action
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }
        foreach ($postItems as $questionId => $questionData) {
            /** @var \Amasty\Faq\Model\Question $question */
            $question = $this->repository->getById($questionId);
            try {
                $this->processData($question, $questionData);
                $this->repository->save($question);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithQuestionId($question, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithQuestionId($question, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithQuestionId(
                    $question,
                    __('Something went wrong while saving the question.')
                );
                $error = true;
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Prepare question before saving
     *
     * @param \Amasty\Faq\Model\Question $question
     * @param array $questionData
     */
    private function processData(\Amasty\Faq\Model\Question $question, array $questionData)
    {
        $question->setTitle((string)$questionData[QuestionInterface::TITLE]);
        if (isset($questionData[QuestionInterface::NAME])) {
            $question->setName((string)$questionData[QuestionInterface::NAME]);
        }
        if (isset($questionData[QuestionInterface::EMAIL])) {
            $question->setEmail((string)$questionData[QuestionInterface::EMAIL]);
        }
        $question->setUrlKey((string)$questionData[QuestionInterface::URL_KEY]);
        $question->setStatus((int)$questionData[QuestionInterface::STATUS]);
        $question->setVisibility((int)$questionData[QuestionInterface::VISIBILITY]);
        $question->setPosition((int)$questionData[QuestionInterface::POSITION]);
    }

    /**
     * Add question id to error message
     *
     * @param QuestionInterface $question
     * @param string $errorText
     * @return string
     */
    private function getErrorWithQuestionId(QuestionInterface $question, $errorText)
    {
        return '[Question ID: ' . $question->getQuestionId() . '] ' . $errorText;
    }
}
