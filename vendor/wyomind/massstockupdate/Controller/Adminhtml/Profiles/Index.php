<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles;

/**
 * Class Index
 * @package Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles
 */
class Index extends \Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles
{

    /**
     * @var string
     */
    public $name = "Mass Stock Update";

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {

        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu("Magento_Backend::system_convert");
        $resultPage->getConfig()->getTitle()->prepend(__($this->name . ' > Profiles'));
        $resultPage->addBreadcrumb(__($this->name), __($this->name));

        return $resultPage;
    }
}
