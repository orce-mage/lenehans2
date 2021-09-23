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
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Wyomind\MassProductImport\Model\ResourceModel\Rules\CollectionFactory
     */
    public $_collectionFactory;
    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Wyomind\MassProductImport\Model\ResourceModel\Rules\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Backend\Helper\Data $backendHelper, \Wyomind\MassProductImport\Model\ResourceModel\Rules\CollectionFactory $collectionFactory, array $data = [])
    {
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }
    protected function _construct()
    {
        parent::_construct();
        $this->setId('MassProductImportGrid');
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
        $this->addColumn('name', ['header' => __('Rule name'), 'align' => 'left', 'index' => 'name']);
        $this->addColumn('action', ['header' => __('Actions'), 'align' => 'left', 'index' => 'action', 'filter' => false, 'sortable' => false, 'width' => '120px', 'renderer' => 'Wyomind\\MassProductImport\\Block\\Adminhtml\\Rules\\Renderer\\Action']);
        return parent::_prepareColumns();
    }
    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return "";
    }
}