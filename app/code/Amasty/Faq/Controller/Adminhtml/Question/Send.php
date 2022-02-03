<?php

namespace Amasty\Faq\Controller\Adminhtml\Question;

use Amasty\Faq\Controller\Adminhtml\AbstractQuestion;
use Amasty\Faq\Model\Emails\NotifierProvider;
use Magento\Backend\App\Action\Context;
use Amasty\Faq\Api\QuestionRepositoryInterface;
use Amasty\Faq\Model\TagFactory;

class Send extends AbstractQuestion
{
    /**
     * @var QuestionRepositoryInterface
     */
    private $repository;

    /**
     * @var NotifierProvider
     */
    private $notifierProvider;

    public function __construct(
        Context $context,
        QuestionRepositoryInterface $repository,
        NotifierProvider $notifierProvider
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->notifierProvider = $notifierProvider;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        if ($questionId = $this->getRequest()->getParam('id')) {
            try {
                $notifier = $this->notifierProvider->get(NotifierProvider::TYPE_CUSTOMER);
                if ($notifier) {
                    $notifier->notify($this->repository->getById((int)$questionId));
                    $this->messageManager->addSuccessMessage(
                        __('You saved the item. Answer sent to Customer\'s Email.')
                    );
                }
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This question no longer exists.'));
                return $this->_redirect('*/*/');
            }
        }

        return $this->_redirect('*/*/');
    }
}
