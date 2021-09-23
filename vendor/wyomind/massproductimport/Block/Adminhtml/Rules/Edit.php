<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Block\Adminhtml\Rules;

/**
 * Class Grid
 * @package Wyomind\MassProductImport\Block\Adminhtml\Rules
 */
class Edit extends \Magento\Backend\Block\Template
{
    /**
     * @return mixed
     */
    public function getRuleId()
    {
        $ruleId=$this->getRequest()->getParam("id");
        return  $ruleId;
    }
}
