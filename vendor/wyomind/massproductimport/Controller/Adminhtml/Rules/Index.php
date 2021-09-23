<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Controller\Adminhtml\Rules;

/**
 * Class Index
 * @package Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles
 */
class Index extends \Wyomind\MassProductImport\Controller\Adminhtml\RulesAbstract
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu("Magento_Backend::system");
        $resultPage->getConfig()->getTitle()->prepend(__("Mass Product Import & Update > Replacement Rules"));
        $resultPage->addBreadcrumb(__("Mass Product Import & Update > Rules"), __("Mass Product Import & Update > Replacement Rules"));

        return $resultPage;
    }
}
