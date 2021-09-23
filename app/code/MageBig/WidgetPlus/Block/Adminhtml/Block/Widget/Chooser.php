<?php
/**
 * Copyright © 2020 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MageBig\WidgetPlus\Block\Adminhtml\Block\Widget;

/**
 * CMS block chooser for Wysiwyg CMS widget
 */
class Chooser extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $_blockFactory;

    /**
     * @var \Magento\Cms\Model\ResourceModel\Block\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var string
     */
    protected $_template = 'MageBig_WidgetPlus::widget/grid/extended.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_blockFactory = $blockFactory;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Block construction, prepare grid params
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setDefaultSort('chooser_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setDefaultFilter(['chooser_is_active' => '1']);
    }

    /**
     * Prepare chooser element HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element Form Element
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function prepareElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $uniqId = $this->mathRandom->getUniqueHash($element->getId());
        $sourceUrl = $this->getUrl('cms/block_widget/chooser', ['uniq_id' => $uniqId]);

        $chooser = $this->getLayout()->createBlock(
            \Magento\Widget\Block\Adminhtml\Widget\Chooser::class
        )->setElement(
            $element
        )->setConfig(
            $this->getConfig()
        )->setFieldsetId(
            $this->getFieldsetId()
        )->setSourceUrl(
            $sourceUrl
        )->setUniqId(
            $uniqId
        );

        if ($element->getValue()) {
            $block = $this->_blockFactory->create()->load($element->getValue());
            if ($block->getId()) {
                $chooser->setLabel($this->escapeHtml($element->getValue()));
            }
        }

        $element->setData('after_element_html', $chooser->toHtml());
        return $element;
    }

    /**
     * Grid Row JS Callback
     *
     * @return string
     */
    public function getRowClickCallback()
    {
        $chooserJsObject = $this->getId();
        $js = '
            function (grid, event) {
                var trElement = Event.findElement(event, "tr");
                var blockId = trElement.down("td").innerHTML.replace(/^\s+|\s+$/g,"");
                var blockTitle = trElement.down("td").next().innerHTML;
                var blockIdentifier = trElement.down("td").next().next().innerHTML.replace(/^\s+|\s+$/g,"");
                ' .
            $chooserJsObject .
            '.setElementValue(blockIdentifier);
                ' .
            $chooserJsObject .
            '.setElementLabel(blockIdentifier);
                ' .
            $chooserJsObject .
            '.close();
            }
        ';
        return $js;
    }

    /**
     * Prepare Cms static blocks collection
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $this->setCollection($this->_collectionFactory->create());
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns for Cms blocks grid
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'chooser_id',
            ['header' => __('ID'), 'align' => 'right', 'index' => 'block_id', 'width' => 50]
        );

        $this->addColumn('chooser_title', ['header' => __('Title'), 'align' => 'left', 'index' => 'title']);

        $this->addColumn(
            'chooser_identifier',
            ['header' => __('Identifier'), 'align' => 'left', 'index' => 'identifier']
        );

        $this->addColumn(
            'chooser_store_id',
            [
                'header' => __('Store View'),
                'align' => 'left',
                'index' => 'store_id',
                'type' => 'store',
                'store_view' => true
            ]
        );

        $this->addColumn(
            'chooser_is_active',
            [
                'header' => __('Status'),
                'index' => 'is_active',
                'type' => 'options',
                'options' => [0 => __('Disabled'), 1 => __('Enabled')]
            ]
        );
        $this->addColumn(
            'chooser_actions',
            [
                'header' => __('Action'),
                'sortable' => false,
                'filter' => false,
                'renderer' => 'MageBig\WidgetPlus\Block\Adminhtml\Block\Grid\Renderer\Action',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('cms/block_widget/chooser', ['_current' => true]);
    }
}
