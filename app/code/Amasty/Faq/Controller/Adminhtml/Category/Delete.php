<?php

namespace Amasty\Faq\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;
use Psr\Log\LoggerInterface;

class Delete extends \Amasty\Faq\Controller\Adminhtml\Category
{
    /**
     * @var \Amasty\Faq\Api\CategoryRepositoryInterface
     */
    private $repository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Action\Context $context,
        \Amasty\Faq\Api\CategoryRepositoryInterface $repository,
        LoggerInterface $logger
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
                $this->messageManager->addSuccessMessage(__('You deleted the category.'));

                return $this->_redirect('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $this->_redirect('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete item right now. Please review the log and try again.')
                );
                $this->logger->critical($e);

                return $this->_redirect('*/*/edit', ['id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a item to delete.'));

        return $this->_redirect('*/*/');
    }
}
