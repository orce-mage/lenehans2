<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Api\Data;

interface StockstatusInformationInterface
{
    const RULE_ID = 'rule_id';
    const STATUS_ID = 'id';
    const STATUS_MESSAGE = 'message';
    const STATUS_ICON = 'icon';
    const TOOLTIP_TEXT = 'tooltip_text';

    /**
     * @param int|null $ruleId
     * @return void
     */
    public function setRuleId(?int $ruleId): void;

    /**
     * @return int|null
     */
    public function getRuleId(): ?int;

    /**
     * @param int|null $id
     * @return void
     */
    public function setStatusId(?int $id): void;

    /**
     * @return int|null
     */
    public function getStatusId(): ?int;

    /**
     * @param string $message
     * @return void
     */
    public function setStatusMessage(string $message): void;

    /**
     * @return string
     */
    public function getStatusMessage(): string;

    /**
     * @param string|null $iconUrl
     * @return void
     */
    public function setStatusIcon(?string $iconUrl): void;

    /**
     * @return string|null
     */
    public function getStatusIcon(): ?string;

    /**
     * @return string|null
     */
    public function getTooltipText(): ?string;

    /**
     * @param string|null $tooltipText
     * @return mixed
     */
    public function setTooltipText(?string $tooltipText): void;
}
