<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model;

use Amasty\Stockstatus\Api\Data\RangeInterface;
use Amasty\Stockstatus\Model\ResourceModel\Range as RangeResource;
use Magento\Framework\Model\AbstractExtensibleModel;

class Range extends AbstractExtensibleModel implements RangeInterface
{
    public function _construct()
    {
        $this->_init(RangeResource::class);
    }

    public function getRuleId(): int
    {
        return $this->_getData(RangeInterface::RULE_ID);
    }

    public function setRuleId(int $ruleId): void
    {
        $this->setData(RangeInterface::RULE_ID, $ruleId);
    }

    public function getStatusId(): int
    {
        return (int) $this->_getData(RangeInterface::STATUS_ID);
    }

    public function setStatusId(int $statusId): void
    {
        $this->setData(RangeInterface::STATUS_ID, $statusId);
    }

    public function getFrom(): int
    {
        return (int) $this->_getData(RangeInterface::FROM);
    }

    public function setFrom(int $from): void
    {
        $this->setData(RangeInterface::FROM, $from);
    }

    public function getTo(): int
    {
        return (int) $this->_getData(RangeInterface::TO);
    }

    public function setTo(int $to): void
    {
        $this->setData(RangeInterface::TO, $to);
    }
}
