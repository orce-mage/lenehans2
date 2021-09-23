<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller\Adminhtml\Attributes;

/**
 * Class Index
 */
class Index extends \Amasty\Storelocator\Controller\Adminhtml\Attributes
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
        $resultPage->setActiveMenu('Amasty_Storelocator::attributes');
        $resultPage->getConfig()->getTitle()->prepend(__('Location Attributes'));
        $resultPage->addBreadcrumb(__('Location Attributes'), __('Location Attributes'));

        return $resultPage;
    }
}
