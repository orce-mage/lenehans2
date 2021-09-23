<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Import\Config;

use Amasty\ImportCore\Api\Config\Relation\RelationConfigInterface;
use Magento\Framework\DataObject;

class RelationConfig extends DataObject implements RelationConfigInterface
{
    const CHILD_ENTITY_CODE = 'child_entity';
    const SUB_ENTITY_FIELD_NAME = 'sub_entity_field_name';
    const PARENT_FIELD_NAME = 'parent_field_name';
    const CHILD_FIELD_NAME = 'child_field_name';
    const ARGUMENTS = 'arguments';
    const TYPE = 'type';
    const SKIP_RELATION_FILES_UPDATE = 'skip_relation_fields_update';
    const VALIDATION = 'validation';
    const ACTION = 'action';
    const PRESELECTED = 'preselected';
    const RELATIONS = 'relations';

    public function getChildEntityCode(): string
    {
        return (string)$this->getData(self::CHILD_ENTITY_CODE);
    }

    public function getSubEntityFieldName(): string
    {
        return (string)$this->getData(self::SUB_ENTITY_FIELD_NAME);
    }

    public function getParentFieldName(): string
    {
        return (string)$this->getData(self::PARENT_FIELD_NAME);
    }

    public function getChildFieldName(): string
    {
        return (string)$this->getData(self::CHILD_FIELD_NAME);
    }

    public function getArguments(): array
    {
        return $this->getData(self::ARGUMENTS) ?: [];
    }

    public function getType(): string
    {
        return (string)$this->getData(self::TYPE);
    }

    public function isSkipRelationFieldsUpdate(): bool
    {
        return (bool)$this->getData(self::SKIP_RELATION_FILES_UPDATE);
    }

    public function getValidation()
    {
        return $this->getData(self::VALIDATION);
    }

    public function getAction()
    {
        return $this->getData(self::ACTION);
    }

    public function getPreselected()
    {
        return $this->getData(self::PRESELECTED);
    }

    public function getRelations(): ?array
    {
        return $this->getData(self::RELATIONS);
    }

    public function setRelations(?array $relations): RelationConfigInterface
    {
        return $this->setData(self::RELATIONS, $relations);
    }
}
