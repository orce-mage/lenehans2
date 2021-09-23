<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Block\Adminhtml\Profiles\Edit\History;

use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Catalog\Model\Product;

class Grid extends Extended
{
    /**
     * @var \Amasty\Orderexport\Model\History
     */
    protected $_historyModel;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = NULL;

    /**
     * @param \Magento\Backend\Block\Template\Context   $context
     * @param \Magento\Backend\Helper\Data              $backendHelper
     * @param \Magento\Framework\Registry               $coreRegistry
     * @param \Amasty\Orderexport\Model\History         $modelHistory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Amasty\Orderexport\Model\History $modelHistory,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->_historyModel  = $modelHistory;
        $this->_coreRegistry  = $coreRegistry;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Set grid params
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('amorderexport_history_grid');
        $this->setDefaultSort('run_at', 'desc');
        $this->setUseAjax(true);
    }

    /**
     * Prepare collection
     *
     * @return Extended
     */
    protected function _prepareCollection()
    {
        $model      = $this->_coreRegistry->registry('current_amasty_orderexport');
        $collection = $this->_historyModel->getCollection();

        if ($model && $model->getId()) {
            $collection->addFieldToFilter('profile_id', $model->getId());
        } else {
            $id = $this->getRequest()->getParam('id');
            if ($id > 0) {
                $collection->addFieldToFilter('profile_id', $id);
            }
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Add columns to grid
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'run_at',
            [
                'header'           => __('Run At'),
                'index'            => 'run_at',
                'header_css_class' => 'col-run_at',
                'column_css_class' => 'col-run_at',
                'type'             => 'datetime',
            ]
        );

        $this->addColumn(
            'file_size',
            [
                'header'   => __('File Size'),
                'index'    => 'file_size',
                'type'     => 'number',
                'renderer' => 'Amasty\Orderexport\Block\Adminhtml\History\Column\Render\FileSize',
            ]
        );

        $this->addColumn(
            'exported_file',
            [
                'header'   => __('Exported File'),
                'renderer' => 'Amasty\Orderexport\Block\Adminhtml\History\Column\Render\Download\File',
                'filter'   => false,
            ]
        );

        $this->addColumn(
            'exported_archive',
            [
                'header'   => __('Exported Archive'),
                'renderer' => 'Amasty\Orderexport\Block\Adminhtml\History\Column\Render\Download\Archive',
                'filter'   => false,
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Retrieve grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->_getData(
            'grid_url'
        ) ? $this->_getData(
            'grid_url'
        ) : $this->getUrl(
            'amasty_orderexport/profiles/grid',
            ['_current' => true]
        );
    }
}
