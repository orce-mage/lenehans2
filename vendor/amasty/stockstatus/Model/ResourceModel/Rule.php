<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\ResourceModel;

use Amasty\Stockstatus\Api\Data\RuleInterface;
use Amasty\Stockstatus\Model\Rule\Condition\Product\IsNew;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Rule extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(RuleInterface::MAIN_TABLE, RuleInterface::ID);
    }

    /**
     * Retrieve all rules which used is new period in conditions.
     *
     * @return array
     */
    public function getWithNewCondition(): array
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable(),
            [RuleInterface::ID]
        )->where(
            sprintf('%s like \'%%%s%%\'', RuleInterface::CONDITIONS_SERIALIZED, IsNew::ATTRIBUTE_CODE)
        );

        return $this->getConnection()->fetchCol($select);
    }
}
