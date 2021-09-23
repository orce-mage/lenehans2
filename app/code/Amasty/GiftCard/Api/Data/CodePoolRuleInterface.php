<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Api\Data;

interface CodePoolRuleInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const RULE_ID = 'rule_id';
    const CODE_POOL_ID = 'code_pool_id';
    const CONDITIONS_SERIALIZED = 'conditions_serialized';
    /**#@-*/

    /**
     * @param int $id
     *
     * @return \Amasty\GiftCard\Api\Data\CodePoolRuleInterface
     */
    public function setRuleId(int $id): \Amasty\GiftCard\Api\Data\CodePoolRuleInterface;

    /**
     * @return int
     */
    public function getRuleId(): int;

    /**
     * @param int $id
     *
     * @return \Amasty\GiftCard\Api\Data\CodePoolRuleInterface
     */
    public function setCodePoolId(int $id): \Amasty\GiftCard\Api\Data\CodePoolRuleInterface;

    /**
     * @return int
     */
    public function getCodePoolId(): int;

    /**
     * @param string $conditions
     *
     * @return \Amasty\GiftCard\Api\Data\CodePoolRuleInterface
     */
    public function setConditionsSerialized(string $conditions): \Amasty\GiftCard\Api\Data\CodePoolRuleInterface;

    /**
     * @return string|null
     */
    public function getConditionsSerialized();
}
