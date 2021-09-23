<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Api\Config\Relation;

interface RelationInterface
{
    // Built-in relation types
    const TYPE_ONE_TO_MANY = 'one_to_many';
    const TYPE_MANY_TO_MANY = 'many_to_many';

    /**
     * Entity Code of sub-entity
     * @return string
     */
    public function getChildEntityCode(): string;

    /**
     * Parent field name where all sub-entity data goes
     * @return string
     */
    public function getSubEntityFieldName(): string;

    /**
     * Implementation specific arguments
     * @return array
     */
    public function getArguments(): array;

    /**
     * One of built-in types or class name for custom relation implementation.
     * Custom class must implement \Amasty\ExportCore\Api\Config\Entity\SubEntityCollectorInterface
     * @return string
     */
    public function getType(): string;

    /**
     * @return \Amasty\ExportCore\Api\Config\Relation\RelationInterface[]
     */
    public function getRelations(): ?array;

    /**
     * @param \Amasty\ExportCore\Api\Config\Relation\RelationInterface[]|null $relations
     *
     * @return \Amasty\ExportCore\Api\Config\Relation\RelationInterface
     */
    public function setRelations(?array $relations): RelationInterface;
}
