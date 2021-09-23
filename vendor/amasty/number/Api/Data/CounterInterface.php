<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Number
 */


namespace Amasty\Number\Api\Data;

interface CounterInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const COUNTER_ID = 'counter_id';
    const ENTITY_TYPE_ID = 'entity_type_id';
    const SCOPE_TYPE_ID = 'scope_type_id';
    const SCOPE_ID = 'scope_id';
    const CURRENT_VALUE = 'current_value';
    const UPDATED_AT = 'updated_at';
    /**#@-*/

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return string
     */
    public function getEntityTypeId(): string;

    /**
     * @param string $type
     *
     * @return CounterInterface
     */
    public function setEntityTypeId(string $type);

    /**
     * @return string
     */
    public function getScopeTypeId(): string;

    /**
     * @param string $typeId
     *
     * @return CounterInterface
     */
    public function setScopeTypeId(string $typeId);

    /**
     * Get counter-associated scope ID
     *
     * @return int
     */
    public function getScopeId(): int;

    /**
     * @param int $scopeId
     *
     * @return CounterInterface
     */
    public function setScopeId(int $scopeId);

    /**
     * @return int
     */
    public function getCurrentValue(): int;

    /**
     * @param int $counterValue
     *
     * @return CounterInterface
     */
    public function setCurrentValue(int $counterValue);

    /**
     * @return string
     */
    public function getUpdatedAt(): string;

    /**
     * @param null $updatedAt
     *
     * @return mixed
     */
    public function setUpdatedAt($updatedAt = null);
}
