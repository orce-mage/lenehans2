<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Number
 */


declare(strict_types=1);

namespace Amasty\Number\Model\Counter;

use Amasty\Number\Api\Data\CounterInterface;
use Magento\Framework\Model\AbstractModel;

class Counter extends AbstractModel implements CounterInterface
{
    protected function _construct()
    {
        $this->_init(\Amasty\Number\Model\Counter\ResourceModel\Counter::class);
        $this->setIdFieldName(CounterInterface::COUNTER_ID);
    }

    /**
     * @param int $incrementStep
     *
     * @return CounterInterface
     */
    public function incrementCounter(int $incrementStep): CounterInterface
    {
        if ($incrementStep >= 0) {
            $this->setCurrentValue($this->getCurrentValue() + $incrementStep);
        }

        return $this;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->_getData(CounterInterface::COUNTER_ID)
            ? (int)$this->_getData(CounterInterface::COUNTER_ID)
            : null;
    }

    /**
     * @return string
     */
    public function getEntityTypeId(): string
    {
        return (string)$this->_getData(CounterInterface::ENTITY_TYPE_ID);
    }

    /**
     * @param string $type
     * @return CounterInterface|Counter
     */
    public function setEntityTypeId(string $type)
    {
        return $this->setData(CounterInterface::ENTITY_TYPE_ID, $type);
    }

    /**
     * @return string
     */
    public function getScopeTypeId(): string
    {
        return (string)$this->_getData(CounterInterface::SCOPE_TYPE_ID);
    }

    /**
     * @param string $typeId
     * @return CounterInterface|Counter
     */
    public function setScopeTypeId(string $typeId)
    {
        return $this->setData(CounterInterface::SCOPE_TYPE_ID, $typeId);
    }

    /**
     * @return int
     */
    public function getScopeId(): int
    {
        return (int)$this->_getData(CounterInterface::SCOPE_ID);
    }

    /**
     * @param int $scopeId
     * @return CounterInterface|Counter
     */
    public function setScopeId(int $scopeId)
    {
        return $this->setData(CounterInterface::SCOPE_ID, $scopeId);
    }

    /**
     * @return int
     */
    public function getCurrentValue(): int
    {
        return (int)$this->_getData(CounterInterface::CURRENT_VALUE);
    }

    /**
     * @param int $counterValue
     * @return CounterInterface
     */
    public function setCurrentValue(int $counterValue): CounterInterface
    {
        $this->setData(CounterInterface::CURRENT_VALUE, $counterValue);
        return $this;
    }

    /**
     * @return string
     */
    public function getUpdatedAt(): string
    {
        return (string)$this->_getData(CounterInterface::UPDATED_AT);
    }

    /**
     * @param null $updatedAt
     * @return $this|mixed
     */
    public function setUpdatedAt($updatedAt = null)
    {
        $this->setData(CounterInterface::UPDATED_AT, $updatedAt);
        return $this;
    }
}
