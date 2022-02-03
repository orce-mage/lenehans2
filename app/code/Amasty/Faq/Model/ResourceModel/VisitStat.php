<?php

namespace Amasty\Faq\Model\ResourceModel;

use Amasty\Faq\Api\Data\VisitStatInterface;
use Amasty\Faq\Setup\Operation;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class VisitStat extends AbstractDb
{
    public function _construct()
    {
        $this->_init(Operation\CreateViewStatTables::TABLE_NAME, VisitStatInterface::VISIT_ID);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function clearTable()
    {
        $this->getConnection()->truncateTable($this->getMainTable());
    }
}
