<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Api\Behavior;

interface BehaviorResultInterface
{
    /**
     * @return array
     */
    public function getNewIds(): array;

    /**
     * @param array $ids
     */
    public function setNewIds(array $ids): void;

    /**
     * @return array
     */
    public function getUpdatedIds(): array;

    /**
     * @param array $ids
     */
    public function setUpdatedIds(array $ids): void;

    /**
     * @return array
     */
    public function getDeletedIds(): array;

    /**
     * @param array $ids
     */
    public function setDeletedIds(array $ids): void;

    /**
     * @return array
     */
    public function getAffectedIds(): array;

    public function merge(BehaviorResultInterface $anotherResult): void;
}
