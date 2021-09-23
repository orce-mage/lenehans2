<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Controller\Adminhtml\Rules;

/**
 * Class NewAction
 * @package Wyomind\MassProductImport\Controller\Adminhtml\Rules
 */
class NewAction extends \Wyomind\MassProductImport\Controller\Adminhtml\RulesAbstract
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        return $this->_resultForwardFactory->create()->forward("edit");
    }
}
