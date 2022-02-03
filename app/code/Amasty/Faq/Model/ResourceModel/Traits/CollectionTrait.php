<?php

namespace Amasty\Faq\Model\ResourceModel\Traits;

use Magento\Framework\DB\Helper\Mysql\Fulltext;

trait CollectionTrait
{
    /**
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $this->renderFilters();
        if ($this->getQueryText()) {
            $this->getSelect()->group('main_table.' . $this->getIdFieldName());
        }
    }

    protected function renderFilters()
    {
        if ($this->getQueryText()) {
            $allColumns = $this->getFulltextIndexColumns($this, $this->getMainTable());
            $this->setQueryText('%' . $this->getQueryText() . '%');
            foreach ($allColumns as $key => $column) {
                if ($key < 1) {
                    $this->getSelect()->where($column . ' LIKE ?', $this->getQueryText());
                    continue;
                }
                $this->getSelect()->orWhere($column . ' LIKE ?', $this->getQueryText());
            }
        }
    }

    /**
     * @param $collection
     * @param $indexTable
     *
     * @return array
     */
    private function getFulltextIndexColumns($collection, $indexTable)
    {
        $indexes = $collection->getConnection()->getIndexList($indexTable);
        $columns = [];
        foreach ($indexes as $index) {
            if (strtoupper($index['INDEX_TYPE']) == 'FULLTEXT') {
                foreach ($index['COLUMNS_LIST'] as $column) {
                    $columns[] = $column;
                }
            }
        }

        return $columns;
    }

    /**
     * @param string $query
     *
     * @return $this
     */
    public function addSearchFilter($query)
    {
        $this->setQueryText(trim($this->getQueryText() . ' ' . $query));

        return $this;
    }

    /**
     * @return array
     */
    public function getIndexFulltextValues()
    {
        $fulltextValues = [];
        foreach ($this->getItems() as $id => $item) {
            $fulltextString = '';
            $indexColumns = $this->getFulltextIndexColumns($this, $this->getMainTable());
            foreach ($indexColumns as $indexColumn) {
                if ($item->getData($indexColumn)) {
                    $fulltextString .= ' ' . trim($item->getData($indexColumn));
                }
            }

            $fulltextValues[$id] = trim($fulltextString);
        }

        return $fulltextValues;
    }

    /**
     * @return string
     */
    public function getQueryText()
    {
        return $this->queryText;
    }

    /**
     * @param $queryText
     * @return $this
     */
    public function setQueryText($queryText)
    {
        $this->queryText = $queryText;

        return $this;
    }
}
