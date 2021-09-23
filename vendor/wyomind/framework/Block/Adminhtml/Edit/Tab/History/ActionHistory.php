<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Block\Adminhtml\Edit\Tab\History;

class ActionHistory extends \Magento\Backend\Block\Widget\Grid\Extended implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Wyomind\Framework\Model\ResourceModel\ActionHistory\CollectionFactory
     */
    protected $actionCollectionFactory;

    /**
     * @var string|null
     */
    protected $tableName;

    /**
     * @var string|null
     */
    protected $module;

    /**
     * @var string|null
     */
    protected $entity;

    /**
     * ActionHistory constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Wyomind\Framework\Model\ResourceModel\ActionHistory\CollectionFactory $actionCollectionFactory
     * @param string|null $tableName
     * @param string|null $module
     * @param string|null $entity
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Registry $coreRegistry,
        \Wyomind\Framework\Model\ResourceModel\ActionHistory\CollectionFactory $actionCollectionFactory,
        $tableName = null,
        $module = null,
        $entity = null,
        $data = []
    ) {
    
        $this->resource = $resource;
        $this->coreRegistry = $coreRegistry;
        $this->tableName = $tableName;
        $this->module = $module;
        $this->entity = $entity;
        $this->actionCollectionFactory = $actionCollectionFactory;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Action History');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Action History');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('action_history_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('desc');
        $this->setDefaultFilter(['action_type' => 'generate']);
        $this->setUseAjax(true);
    }

    /**
     * Return URL link to Tab content
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'wyomind_framework/history_action/grid',
            [
                '_current' => true,
                'current_entity_id' => $this->coreRegistry->registry('current_entity_id'),
                'tableName' => $this->tableName,
                'module' => $this->module,
                'entity' => $this->entity
            ]
        );
    }

    /**
     * Apply various selection filters to prepare the action history grid collection.
     * @return $this
     */
    protected function _prepareCollection()
    {
        $entityId = $this->coreRegistry->registry('current_entity_id');
        $tableName = $this->resource->getTableName($this->tableName);
        $collection = $this->actionCollectionFactory->create(['tableName' => $tableName]);
        $collection->addFieldToFilter(\Wyomind\Framework\Model\ActionHistory::ENTITY_ID, $entityId);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @inheritdoc
     */
    protected function _prepareColumns()
    {
        $this->addColumn('action_id', ['header' => __('ID'), 'width' => '100', 'index' => 'action_id']);

        $this->addColumn('version_id', [
            'header' => __('Version ID'),
            'index' => 'version_id',
        ]);

        $this->addColumn('origin', [
            'header' => __('Origin'),
            'index' => 'origin',
            'renderer' => 'Wyomind\Framework\Block\Adminhtml\Edit\Tab\History\Renderer\Origin',
            'type' => 'options',
            'options' => [
                \Wyomind\Framework\Api\Data\ActionHistoryInterface::ORIGIN_CRON => __('Cron'),
                \Wyomind\Framework\Api\Data\ActionHistoryInterface::ORIGIN_BACKEND => __('Backend'),
                \Wyomind\Framework\Api\Data\ActionHistoryInterface::ORIGIN_CLI => __('CLI'),
                \Wyomind\Framework\Api\Data\ActionHistoryInterface::ORIGIN_API => __('API')
            ]
        ]);

        $this->addColumn('username', [
            'header' => __('Username'),
            'index' => 'username',
        ]);

        $this->addColumn('action_type', [
            'header' => __('Action'),
            'index' => 'action_type',
            'type' => 'options',
            'options' => [
                '' => __('All actions'),
                'create' => __('Create'),
                'generate' => __('Generate'),
                'update' => __('Update'),
                'delete' => __('Delete')
            ]
        ]);

        $this->addColumn('details', [
            'header' => __('Details'),
            'index' => 'details',
            'renderer' => 'Wyomind\Framework\Block\Adminhtml\Edit\Tab\History\Renderer\Details',
            'filter' => false,
            'sortable' => false
        ]);

        $this->addColumn('created_at', [
            'header' => __('Date'),
            'index' => 'created_at',
            'type' => 'datetime'
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @return string|null
     */
    public function getActionId()
    {
        return $this->coreRegistry->registry(\Wyomind\Framework\Api\Data\ActionHistoryInterface::ID);
    }

     /**
     * {@inheritdoc}
     */
    public function getRowUrl($item)
    {
        return false;
    }
}
