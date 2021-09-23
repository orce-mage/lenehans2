<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Api\Config\Relation;

interface RelationConfigInterface
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
     * Identity field name of parent entity
     * @return string
     */
    public function getParentFieldName(): string;

    /**
     * Field name used for linking with parent entity
     * @return string
     */
    public function getChildFieldName(): string;

    /**
     * Implementation specific arguments
     * @return array
     */
    public function getArguments(): array;

    /**
     * One of built-in types or class name for custom relation implementation.
     * @return string
     */
    public function getType(): string;

    /**
     * @return bool
     */
    public function isSkipRelationFieldsUpdate(): bool;

    /**
     * @return RelationValidationInterface|null
     */
    public function getValidation();

    /**
     * @return RelationActionInterface|null
     */
    public function getAction();

    /**
     * @return \Amasty\ImportCore\Api\Config\Entity\Field\Configuration\PreselectedInterface|null
     */
    public function getPreselected();

    /**
     * @return \Amasty\ImportCore\Api\Config\Relation\RelationConfigInterface[]|null
     */
    public function getRelations(): ?array;

    /**
     * @param \Amasty\ImportCore\Api\Config\Relation\RelationConfigInterface[]|null $relations
     * @return \Amasty\ImportCore\Api\Config\Relation\RelationConfigInterface
     */
    public function setRelations(?array $relations): RelationConfigInterface;
}
