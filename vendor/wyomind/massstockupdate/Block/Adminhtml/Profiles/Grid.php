<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\MassStockUpdate\Block\Adminhtml\Profiles;

/**
 * Class Grid
 * @package Wyomind\MassStockUpdate\Block\Adminhtml\Profiles
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var string
     */
    public $module = "MassStockUpdate";
    /**
     * @var \Wyomind\MassStockUpdate\Model\ResourceModel\Profiles\CollectionFactory
     */
    public $_collectionFactory;
    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Wyomind\MassStockUpdate\Model\ResourceModel\Profiles\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Backend\Helper\Data $backendHelper, \Wyomind\MassStockUpdate\Model\ResourceModel\Profiles\CollectionFactory $collectionFactory, array $data = [])
    {
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }
    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return "";
    }
    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('MassStockUpdateGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
    }
    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('id', ['header' => __('ID'), 'align' => 'right', 'width' => '50px', 'index' => 'id', 'filter' => false]);
        $this->addColumn('name', ['header' => __('Profile name'), 'align' => 'left', 'index' => 'name']);
        $classHelper = "\\Wyomind\\" . $this->module . "\\Helper\\Data";
        $this->addColumn('file_system_type', ['header' => __('File location'), 'align' => 'left', 'index' => 'file_system_type', 'type' => 'options', 'options' => [$classHelper::LOCATION_MAGENTO => __('Magento file system'), $classHelper::LOCATION_FTP => __('Ftp server'), $classHelper::LOCATION_URL => __('Url'), $classHelper::LOCATION_WEBSERVICE => __('Web service'), $classHelper::LOCATION_DROPBOX => __('Dropbox')]]);
        $this->addColumn('file_type', ['header' => __('File type'), 'align' => 'left', 'index' => 'file_type', 'type' => 'options', 'options' => [$classHelper::CSV => __('csv'), $classHelper::XML => __('xml'), $classHelper::JSON => __('json')]]);
        $this->addColumn('status', ['header' => __('Status'), 'align' => 'left', 'index' => 'status', 'renderer' => 'Wyomind\\' . $this->module . '\\Block\\Adminhtml\\Progress\\Status']);
        $this->addColumn('imported_at', ['header' => __('Last import'), 'align' => 'left', 'index' => 'imported_at', 'width' => '80px', 'type' => "datetime", 'renderer' => 'Wyomind\\' . $this->module . '\\Block\\Adminhtml\\Profiles\\Renderer\\Datetime']);
        $this->addColumn('action', ['header' => __('Actions'), 'align' => 'left', 'index' => 'action', 'filter' => false, 'sortable' => false, 'width' => '120px', 'renderer' => 'Wyomind\\' . $this->module . '\\Block\\Adminhtml\\Profiles\\Renderer\\Action']);
        return parent::_prepareColumns();
    }
}