<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface RangeInterface extends ExtensibleDataInterface
{
    const ID = 'id';
    const RULE_ID = 'rule_id';
    const STATUS_ID = 'status_id';
    const FROM = 'qty_from';
    const TO = 'qty_to';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return \Amasty\Stockstatus\Api\Data\RangeInterface
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getRuleId(): int;

    /**
     * @param int $ruleId
     */
    public function setRuleId(int $ruleId): void;

    /**
     * @return int
     */
    public function getStatusId(): int;

    /**
     * @param int $statusId
     */
    public function setStatusId(int $statusId): void;

    /**
     * @return int
     */
    public function getFrom(): int;

    /**
     * @param int $from
     */
    public function setFrom(int $from): void;

    /**
     * @return int
     */
    public function getTo(): int;

    /**
     * @param int $to
     */
    public function setTo(int $to): void;
}
