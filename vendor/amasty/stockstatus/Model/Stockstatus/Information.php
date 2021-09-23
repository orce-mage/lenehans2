<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Stockstatus;

use Amasty\Stockstatus\Api\Data\StockstatusInformationInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

class Information extends AbstractExtensibleObject implements StockstatusInformationInterface
{
    public function setRuleId(?int $ruleId): void
    {
        $this->setData(static::RULE_ID, $ruleId);
    }

    public function getRuleId(): ?int
    {
        return $this->_get(static::RULE_ID);
    }

    public function setStatusId(?int $id): void
    {
        $this->setData(static::STATUS_ID, $id);
    }

    public function getStatusId(): ?int
    {
        return $this->_get(static::STATUS_ID);
    }

    public function setStatusMessage(string $message): void
    {
        $this->setData(static::STATUS_MESSAGE, $message);
    }

    public function getStatusMessage(): string
    {
        return (string) $this->_get(static::STATUS_MESSAGE);
    }

    public function setStatusIcon(?string $iconUrl): void
    {
        $this->setData(static::STATUS_ICON, $iconUrl);
    }

    public function getStatusIcon(): ?string
    {
        return $this->_get(static::STATUS_ICON);
    }

    public function getTooltipText(): ?string
    {
        return $this->_get(self::TOOLTIP_TEXT);
    }

    public function setTooltipText(?string $tooltipText): void
    {
        $this->setData(self::TOOLTIP_TEXT, $tooltipText);
    }
}
