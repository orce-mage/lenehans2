<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\MassProductImport\Block\Adminhtml\Rules\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\CatalogRule\Block\Adminhtml\Edit\GenericButton;
/**
 * Class ImportButton
 * @package Wyomind\MassProductImport\Block\Adminhtml\Rules\Edit
 */
class ImportButton extends GenericButton implements ButtonProviderInterface
{
    public function __construct(\Wyomind\MassProductImport\Helper\Delegate $wyomind, \Magento\Backend\Block\Widget\Context $context, \Magento\Framework\Registry $registry)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $registry);
    }
    /**
     * @return array
     */
    public function getButtonData()
    {
        if ($this->_request->getParam("id")) {
            return ['label' => __('Import a rule set from a csv file'), 'class' => '', 'on_click' => "require(['wyomind_MassImportAndUpdate_rules'], function (rules) { rules.import(); });"];
        }
    }
}