<?php

namespace Amasty\Faq\Model\ResourceModel\VisitStat;

use Amasty\Faq\Model\VisitStat;
use Amasty\Faq\Api\Data\VisitStatInterface;
use Amasty\Faq\Model\ResourceModel\VisitStat as VisitStatResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @method VisitStat[] getItems()
 */
class Collection extends AbstractCollection
{
    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _construct()
    {
        $this->_init(VisitStat::class, VisitStatResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    /**
     * Add hits column to collection
     *
     * @return $this
     */
    public function addHitsColumn()
    {
        $this->getSelect()
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns([
                VisitStatInterface::VISIT_ID,
                VisitStatInterface::SEARCH_QUERY,
                VisitStatInterface::STORE_IDS,
                VisitStatInterface::COUNT_OF_RESULT => 'max(' . VisitStatInterface::COUNT_OF_RESULT . ')',
                'hits' => 'COUNT(' . VisitStatInterface::SEARCH_QUERY . ')'
            ])
            ->group([VisitStat::SEARCH_QUERY, VisitStat::STORE_IDS]);

        return $this;
    }

    /**
     * Get SQL for get record count.
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset();
        $countSelect->from($this->getSelect());
        $countSelect->reset(\Magento\Framework\DB\Select::COLUMNS);

        $entityColumn = $this->getResource()->getIdFieldName();
        $countSelect->columns(new \Zend_Db_Expr(("COUNT( " . $entityColumn . ")")));

        return $countSelect;
    }
}
