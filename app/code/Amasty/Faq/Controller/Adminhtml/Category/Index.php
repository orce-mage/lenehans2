<?php

namespace Amasty\Faq\Controller\Adminhtml\Category;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Amasty\Faq\Controller\Adminhtml\Category
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_Faq::category');
        $resultPage->getConfig()->getTitle()->prepend(__('FAQ Categories'));
        $resultPage->addBreadcrumb(__('Categories'), __('Categories'));

        return $resultPage;
    }
}
