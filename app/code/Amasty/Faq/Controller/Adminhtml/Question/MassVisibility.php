<?php

namespace Amasty\Faq\Controller\Adminhtml\Question;

use Amasty\Faq\Api\Data\QuestionInterface;

class MassVisibility extends \Amasty\Faq\Controller\Adminhtml\AbstractMassAction
{
    /**
     * @param QuestionInterface $question
     */
    protected function itemAction(QuestionInterface $question)
    {
        $question->setVisibility($this->getRequest()->getParam('visibility'));
        $this->repository->save($question);
    }
}
