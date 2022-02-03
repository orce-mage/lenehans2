<?php

namespace Amasty\Faq\Controller\Question;

use Amasty\Faq\Api\Data\QuestionInterface;
use Amasty\Faq\Api\QuestionRepositoryInterface;
use Amasty\Faq\Model\OptionSource\Question\Status;
use Amasty\Faq\Model\OptionSource\Question\Visibility;
use Amasty\Faq\Model\ResolveQuestionCategory;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;

class View extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var ResolveQuestionCategory
     */
    private $resolveQuestionCategory;

    /**
     * @var QuestionRepositoryInterface
     */
    private $repository;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var HttpContext
     */
    private $httpContext;

    public function __construct(
        Context $context,
        QuestionRepositoryInterface $repository,
        Registry $coreRegistry,
        Session $session,
        ResolveQuestionCategory $resolveQuestionCategory,
        HttpContext $httpContext
    ) {
        parent::__construct($context);
        $this->session = $session;
        $this->resolveQuestionCategory = $resolveQuestionCategory;
        $this->repository = $repository;
        $this->coreRegistry = $coreRegistry;
        $this->httpContext = $httpContext;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        try {
            $question = $this->repository->getById($this->_request->getParam('id'));
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->setController('index');
            $resultForward->forward('defaultNoRoute');

            return $resultForward;
        }

        $customerGroup = $this->httpContext->getValue(CustomerContext::CONTEXT_GROUP);

        if (!$question
            || $question->getStatus() != Status::STATUS_ANSWERED
            || $question->getVisibility() == Visibility::VISIBILITY_NONE
            || !$this->isAllowedForCustomerGroup($question, $customerGroup)
            || (
                $question->getVisibility() == Visibility::VISIBILITY_FOR_LOGGED
                && !$this->session->isLoggedIn()
            )
        ) {
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->setController('index');
            $resultForward->forward('defaultNoRoute');

            return $resultForward;
        }
        $this->coreRegistry->register('current_faq_question', $question);
        $this->coreRegistry->register('current_faq_question_id', $question->getQuestionId());

        $this->setCurrentCategory($question);

        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }

    /**
     * @param QuestionInterface $question
     * @param int $customerGroup
     *
     * @return bool
     */
    public function isAllowedForCustomerGroup(QuestionInterface $question, $customerGroup)
    {
        if ($question->getCustomerGroups() === '') {
            return true;
        }

        $customerGroups = explode(',', $question->getCustomerGroups());

        return in_array($customerGroup, $customerGroups);
    }

    /**
     * @param QuestionInterface $question
     */
    private function setCurrentCategory(QuestionInterface $question)
    {
        $categoryId = $this->resolveQuestionCategory->execute($question);

        $this->httpContext->setValue(
            \Amasty\Faq\Model\Context::CONTEXT_CATEGORY,
            $categoryId,
            0
        );

        $this->coreRegistry->register(
            'current_faq_category_id',
            $categoryId
        );
    }
}
