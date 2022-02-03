<?php

namespace Amasty\Faq\Controller\Adminhtml\Report;

use Magento\Framework\Controller\ResultFactory;
use Amasty\Faq\Controller\Adminhtml\AbstractReports;

class Index extends AbstractReports
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_Faq::report');
        $resultPage->getConfig()->getTitle()->prepend(__('FAQ Search Terms Report'));

        return $resultPage;
    }
}
