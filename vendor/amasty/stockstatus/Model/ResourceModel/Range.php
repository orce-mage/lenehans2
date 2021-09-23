<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\ResourceModel;

use Amasty\Stockstatus\Api\Data\RangeInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Range extends AbstractDb
{
    const MAIN_TABLE = 'amasty_stockstatus_ranges';

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, RangeInterface::ID);
    }

    /**
     * @param int $ruleId
     * @throws LocalizedException
     */
    public function deleteByRuleId(int $ruleId): void
    {
        $this->getConnection()->delete(
            $this->getMainTable(),
            [sprintf('%s IN (?)', RangeInterface::RULE_ID) => $ruleId]
        );
    }
}
