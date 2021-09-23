<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\MassStockUpdate\Block\Adminhtml\Profiles;

/**
 * Class Edit
 * @package Wyomind\MassStockUpdate\Block\Adminhtml\Profiles
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var string
     */
    public $module = "MassStockUpdate";
    public function __construct(\Wyomind\MassStockUpdate\Helper\Delegate $wyomind, \Magento\Backend\Block\Widget\Context $context, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $data);
    }
    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('model')->getId()) {
            return __("Edit Profile '%1'", $this->escapeHtml($this->_coreRegistry->registry('model')->getName()));
        } else {
            return __('New Profile');
        }
    }
    /**
     *
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Wyomind_MassStockUpdate';
        $this->_controller = 'adminhtml_profiles';
        parent::_construct();
        $this->removeButton('save');
        $this->addButton('run', ['label' => __('Run Profile Now'), 'class' => 'save action-primary', 'onclick' => "jQuery('#run_i').val('1');  jQuery('#edit_form').submit();"]);
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $this->addButton('export', ['label' => __('Export'), 'class' => 'add', "onclick" => "setLocation('" . $this->getUrl('*/*/export', ['id' => $id]) . "')"]);
            $this->addButton('duplicate', ['label' => __('Duplicate'), 'class' => 'add ', 'onclick' => "jQuery('#id').remove();jQuery('#enabled').val(0); jQuery('#back_i').val('1'); jQuery('#edit_form').submit();"]);
        }
        $this->addButton('save', ['label' => __('Save Profile'), 'class' => 'save', 'data_attribute' => ['mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']]]], -100);
        $this->updateButton('delete', 'label', __('Delete'));
    }
    /**
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return "";
    }
}