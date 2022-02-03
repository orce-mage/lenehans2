<?php

namespace Amasty\Faq\Controller\Adminhtml\Question;

use Amasty\Faq\Api\QuestionRepositoryInterface;
use Amasty\Faq\Model\QuestionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;

class Edit extends \Amasty\Faq\Controller\Adminhtml\AbstractQuestion
{
    /**
     * @var QuestionRepositoryInterface
     */
    private $repository;

    /**
     * @var QuestionFactory
     */
    private $questionFactory;

    /**
     * @var Registry
     */
    private $coreRegistry;

    public function __construct(
        Context $context,
        QuestionRepositoryInterface $repository,
        QuestionFactory $questionFactory,
        Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->questionFactory = $questionFactory;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Edit action
     */
    public function execute()
    {
        if ($id = (int)$this->getRequest()->getParam('id')) {
            try {
                $model = $this->repository->getById($id);
                if ($model->getEmail()) {
                    $this->coreRegistry->register('canSendCustomerEmail', true);
                }
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This question no longer exists.'));
                return $this->_redirect('*/*/edit', ['id' => $id]);
            }
        } else {
            $model = $this->questionFactory->create();
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_Faq::question');
        $resultPage->addBreadcrumb(__('Questions'), __('Questions'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getTitle() : __('New Question'));

        return $resultPage;
    }
}
