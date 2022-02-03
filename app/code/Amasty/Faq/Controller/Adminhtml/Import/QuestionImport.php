<?php

namespace Amasty\Faq\Controller\Adminhtml\Import;

use Amasty\Faq\Controller\Adminhtml\AbstractImport;

class QuestionImport extends AbstractImport
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_Faq::question_import');
        $resultPage->getConfig()->getTitle()->prepend(__('Import FAQ Questions'));
        $resultPage->addBreadcrumb(__('Import FAQ Questions'), __('Import FAQ Questions'));
        return $resultPage;
    }
}
