<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\MassStockUpdate\Block\Adminhtml\Profiles\Edit\Tab;

/**
 * Class Cron
 * @package Wyomind\MassStockUpdate\Block\Adminhtml\Profiles\Edit\Tab
 */
class Cron extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var string
     */
    public $_module = "massstockupdate";
    public function __construct(\Wyomind\MassStockUpdate\Helper\Delegate $wyomind, \Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\FormFactory $formFactory, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $registry, $formFactory, $data);
    }
    /**
     * @return \Wyomind\Framework\Helper\type
     */
    public function getCronInterval()
    {
        return $this->_framework->getStoreConfig($this->_module . "/settings/cron_interval");
    }
    /**
     * @return mixed
     */
    public function getCronSettings()
    {
        $model = $this->_coreRegistry->registry('profile');
        return $model->getCronSettings();
    }
    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabLabel()
    {
        return __('Scheduled tasks');
    }
    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabTitle()
    {
        return __('Scheduled tasks');
    }
    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }
    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
    /**
     * @return \Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('profile');
        $form = $this->_formFactory->create();
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}