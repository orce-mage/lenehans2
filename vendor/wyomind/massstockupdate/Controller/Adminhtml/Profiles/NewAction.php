<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles;

/**
 * Class NewAction
 * @package Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles
 */
class NewAction extends \Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles
{

    /**
     * @return \Magento\Backend\Model\View\Result\Forward|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        return $this->_resultForwardFactory->create()->forward("edit");
    }
}
