<?php

namespace Amasty\Faq\Block\Adminhtml\Export;

use Amasty\Faq\Model\Export\Category\Export as CategoryExport;
use Amasty\Faq\Model\Export\Question\Export as QuestionExport;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data as HelperData;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\DataObject;
use Magento\ImportExport\Model\Export;

class Filter extends Extended
{
    /**
     * @var bool
     */
    private $isQuestion;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        Context $context,
        HelperData $backendHelper,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setRowClickCallback(null);
        $this->setId('export_filter_grid');
        $this->setPagerVisibility(false);
        $this->setDefaultLimit(null);
        $this->setUseAjax(true);
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumn(
            'skip',
            [
                'header' => __('Exclude'),
                'type' => 'checkbox',
                'name' => 'skip',
                'field_name' => Export::FILTER_ELEMENT_SKIP . '[]',
                'filter' => false,
                'sortable' => false,
                'index' => 'field',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id data-grid-checkbox-cell'
            ]
        );
        $this->addColumn(
            'field',
            [
                'header' => __('Field'),
                'sortable' => false,
                'filter' => false,
                'field_name' => 'code',
                'getter' => 'getField'
            ]
        );

        return $this;
    }

    /**
     * @param bool $isQuestion
     */
    public function setIsQuestion($isQuestion)
    {
        $this->isQuestion = $isQuestion;
    }

    /**
     * @param \Magento\Catalog\Model\Product|DataObject $row
     * @return bool
     */
    public function getRowUrl($row)
    {
        return false;
    }

    /**
     * @return \Magento\Framework\Data\Collection
     */
    protected function _prepareCollection()
    {
        $collection = $this->collectionFactory->create();
        $data = $this->isQuestion ? QuestionExport::COLUMNS : CategoryExport::COLUMNS;
        foreach ($data as $option) {
            $varienObject = new DataObject();
            $item = [
                'field' => $option,
            ];
            $varienObject->setData($item);
            $collection->addItem($varienObject);
        }
        $this->setCollection($collection);
        return $this->getCollection();
    }
}
