<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller\Adminhtml\Reviews;

use Amasty\Storelocator\Controller\Adminhtml\Reviews;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Index
 */
class Index extends Reviews
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_Storelocator::reviews');
        $resultPage->getConfig()->getTitle()->prepend(__('Locations Reviews'));
        $resultPage->addBreadcrumb(__('Locations Reviews'), __('Locations Reviews'));

        return $resultPage;
    }
}
