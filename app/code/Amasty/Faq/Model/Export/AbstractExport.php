<?php

namespace Amasty\Faq\Model\Export;

use Amasty\Faq\Model\ResourceModel\Category\CollectionFactory;
use Magento\ImportExport\Model\Export as ModelExport;
use Magento\ImportExport\Model\Export\AbstractEntity;

abstract class AbstractExport extends AbstractEntity
{
    /**
     * @var \Amasty\Faq\Model\ResourceModel\Question\Collection
     */
    protected $collection;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var array
     */
    protected $exportAttributeCodes = [];

    /**
     * @return \Amasty\Faq\Model\ResourceModel\Question\Collection
     */
    protected function _getEntityCollection()
    {
        if ($this->collection === null) {
            $this->collection = $this->collectionFactory->create();
            $this->addAttributesToCollection();
        }

        return $this->collection;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $item
     */
    public function exportItem($item)
    {
        $row = $item->toArray();
        $this->getWriter()->writeRow($row);
    }

    /**
     * @return string
     */
    public function export()
    {
        $writer = $this->getWriter();
        $writer->setHeaderCols($this->_getHeaderColumns());
        $this->_exportCollectionByPages($this->_getEntityCollection());

        return $writer->getContents();
    }

    /**
     * @return array
     */
    protected function _getExportAttributeCodes()
    {
        if (!$this->exportAttributeCodes) {
            $skipAttr = $this->_parameters[ModelExport::FILTER_ELEMENT_SKIP];
            foreach (static::COLUMNS as $column) {
                if (array_search($column, $skipAttr) === false) {
                    $this->exportAttributeCodes[] = $column;
                }
            }
        }

        return $this->exportAttributeCodes;
    }

    /**
     * @return array
     */
    protected function _getHeaderColumns()
    {
        return $this->_getExportAttributeCodes();
    }
}
