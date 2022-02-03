<?php

namespace Amasty\Faq\Controller\Adminhtml\Question;

use Psr\Log\LoggerInterface;
use Magento\Backend\App\Action\Context;
use Amasty\Faq\Api\QuestionRepositoryInterface;

class Delete extends \Amasty\Faq\Controller\Adminhtml\AbstractQuestion
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var QuestionRepositoryInterface
     */
    private $repository;

    public function __construct(
        Context $context,
        LoggerInterface $logger,
        QuestionRepositoryInterface $repository
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        if ($id = (int)$this->getRequest()->getParam('id')) {
            try {
                $this->repository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('You deleted the question.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete item right now. Please review the log and try again.')
                );
                $this->logger->critical($e);
            }
        }

        return $this->_redirect('*/*/');
    }
}
