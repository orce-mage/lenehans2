<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Block\Adminhtml;
 
class Rules extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_rules';
        $this->_blockGroup = 'Wyomind_MassProductImport';
        $this->_headerText = __('Manage Replacement Rules');
        parent::_construct();
        
        $this->updateButton('add', 'label', __('Create a new Rule'));
    }
}
