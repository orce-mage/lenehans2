<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Import\Behavior;

use Amasty\ImportCore\Api\Behavior\BehaviorResultInterface;

class BehaviorResult implements BehaviorResultInterface
{
    /**
     * @var array
     */
    private $newIds = [];

    /**
     * @var array
     */
    private $updatedIds = [];

    /**
     * @var array
     */
    private $deletedIds = [];

    /**
     * @return array
     */
    public function getNewIds(): array
    {
        return $this->newIds;
    }

    /**
     * @param array $newIds
     */
    public function setNewIds(array $newIds): void
    {
        $this->newIds = $newIds;
    }

    /**
     * @return array
     */
    public function getUpdatedIds(): array
    {
        return $this->updatedIds;
    }

    /**
     * @param array $updatedIds
     */
    public function setUpdatedIds(array $updatedIds): void
    {
        $this->updatedIds = $updatedIds;
    }

    /**
     * @return array
     */
    public function getDeletedIds(): array
    {
        return $this->deletedIds;
    }

    /**
     * @param array $deletedIds
     */
    public function setDeletedIds(array $deletedIds): void
    {
        $this->deletedIds = $deletedIds;
    }

    public function getAffectedIds(): array
    {
        return array_merge($this->getDeletedIds(), $this->getNewIds(), $this->getUpdatedIds());
    }

    public function merge(BehaviorResultInterface $anotherResult): void
    {
        $this->newIds = array_merge($this->newIds, $anotherResult->getNewIds());
        $this->updatedIds = array_merge($this->updatedIds, $anotherResult->getUpdatedIds());
        $this->deletedIds = array_merge($this->deletedIds, $anotherResult->getDeletedIds());

        sort($this->newIds);
        sort($this->updatedIds);
        sort($this->deletedIds);
    }
}
