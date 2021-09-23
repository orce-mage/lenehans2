<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\SubEntity\Relation;

use Amasty\ExportCore\Api\Config\Relation\RelationInterface;
use Magento\Framework\DataObject;

class RelationConfig extends DataObject implements RelationInterface
{
    const CHILD_ENTITY_CODE = 'child_entity';
    const SUB_ENTITY_FIELD_NAME = 'sub_entity_field_name';
    const ARGUMENTS = 'arguments';
    const TYPE = 'type';
    const RELATIONS = 'relations';

    public function getChildEntityCode(): string
    {
        return (string)$this->getData(self::CHILD_ENTITY_CODE);
    }

    public function getSubEntityFieldName(): string
    {
        return (string)$this->getData(self::SUB_ENTITY_FIELD_NAME);
    }

    public function getArguments(): array
    {
        return $this->getData(self::ARGUMENTS) ?: [];
    }

    public function getType(): string
    {
        return (string)$this->getData(self::TYPE);
    }

    public function getRelations(): ?array
    {
        return $this->getData(self::RELATIONS);
    }

    public function setRelations(?array $relations): RelationInterface
    {
        $this->setData(self::RELATIONS, $relations);

        return $this;
    }
}
