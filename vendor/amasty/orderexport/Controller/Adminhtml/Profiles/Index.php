<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Controller\Adminhtml\Profiles;

class Index extends \Amasty\Orderexport\Controller\Adminhtml\Profiles
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
        $resultPage->setActiveMenu('Amasty_Orderexport::profiles');
        $resultPage->getConfig()->getTitle()->prepend(__('Profiles'));
        $resultPage->addBreadcrumb(__('Amasty'), __('Amasty'));
        $resultPage->addBreadcrumb(__('Efficient Order Export'), __('Efficient Order Export'));
        $resultPage->addBreadcrumb(__('Profiles'), __('Profiles'));

        return $resultPage;
    }
}
