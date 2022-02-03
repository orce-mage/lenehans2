<?php

namespace Amasty\Faq\Controller\Adminhtml\Question;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Amasty\Faq\Controller\Adminhtml\AbstractQuestion
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_Faq::question');
        $resultPage->getConfig()->getTitle()->prepend(__('FAQ Questions'));
        $resultPage->addBreadcrumb(__('Questions'), __('Questions'));

        return $resultPage;
    }
}
