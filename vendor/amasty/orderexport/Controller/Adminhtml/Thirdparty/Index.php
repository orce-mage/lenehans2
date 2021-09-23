<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Controller\Adminhtml\Thirdparty;

class Index extends \Amasty\Orderexport\Controller\Adminhtml\Thirdparty
{
    /**
     * Items list.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Amasty_Orderexport::thirdparty');
        $resultPage->getConfig()->getTitle()->prepend(__('3rd Party Link'));
        $resultPage->addBreadcrumb(__('Amasty'), __('Amasty'));
        $resultPage->addBreadcrumb(__('Efficient Order Export'), __('Efficient Order Export'));
        $resultPage->addBreadcrumb(__('Thirdparty'), __('3rd Party Link'));

        return $resultPage;
    }
}
