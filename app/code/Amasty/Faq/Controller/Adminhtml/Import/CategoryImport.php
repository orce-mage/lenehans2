<?php

namespace Amasty\Faq\Controller\Adminhtml\Import;

use Amasty\Faq\Controller\Adminhtml\AbstractImport;

class CategoryImport extends AbstractImport
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_Faq::category_import');
        $resultPage->getConfig()->getTitle()->prepend(__('Import FAQ Categories'));
        $resultPage->addBreadcrumb(__('Import FAQ Categories'), __('Import FAQ Categories'));
        return $resultPage;
    }
}
