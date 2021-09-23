<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Number
 */


declare(strict_types=1);

namespace Amasty\Number\Model;

use Amasty\Number\Api\Data\CounterInterface;
use Amasty\Number\Model\Counter\Scope\CounterScope;
use Amasty\Number\Model\Counter\Scope\CounterScopeResolver;
use Magento\Framework\DataObject;
use Magento\Sales\Api\Data\OrderInterface;

class SequenceStorage
{
    /**
     * @var CounterScopeResolver
     */
    private $counterScopeResolver;

    /**
     * @var CounterInterface|null
     */
    private $counterToReset;

    /**
     * @var array
     */
    private $scope = [];

    /**
     * @var string|null
     */
    private $entityType = null;

    /**
     * @var DataObject|null
     */
    private $entity = null;

    /**
     * @var OrderInterface|null
     */
    private $order = null;

    /**
     * @var array
     */
    private $counterSteps = [];

    public function __construct(
        CounterScopeResolver $counterScopeResolver
    ) {
        $this->counterScopeResolver = $counterScopeResolver;
    }

    /**
     * @param string $type
     * @return mixed|null
     */
    public function getModifiedCounterStep(string $type)
    {
        if (isset($this->counterSteps[$type])) {
            return $this->counterSteps[$type];
        }

        return null;
    }

    /**
     * @param string $type
     * @param int $step
     * @return $this
     */
    public function setModifiedCounterStep(string $type, int $step)
    {
        $this->counterSteps[$type] = $step;

        return $this;
    }

    /**
     * @param string $type
     * @return CounterScope
     */
    public function getCounterScope(string $type): CounterScope
    {
        if (!isset($this->scope[$type])) {
            $this->scope[$type] = $this->counterScopeResolver->resolveCounterScope($type, $this->order);
        }

        return $this->scope[$type];
    }

    /**
     * @param CounterScope $counterScope
     * @param string $type
     * @return $this
     */
    public function setCounterScope(CounterScope $counterScope, string $type)
    {
        $this->scope[$type] = $counterScope;

        return $this;
    }

    /**
     * @return CounterInterface|null
     */
    public function getCounterToReset()
    {
        return $this->counterToReset;
    }

    /**
     * @param CounterInterface $counter
     * @return $this
     */
    public function setCounterToReset(CounterInterface $counter)
    {
        $this->counterToReset = $counter;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * @param $entityType
     * @return $this
     */
    public function setEntityType($entityType)
    {
        $this->entityType = $entityType;

        return $this;
    }

    /**
     * @param DataObject $entity
     * @return $this
     */
    public function setEntity(DataObject $entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return DataObject|null
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param OrderInterface $order
     * @return $this
     */
    public function setOrder(OrderInterface $order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return OrderInterface|null
     */
    public function getOrder()
    {
        return $this->order;
    }
}
