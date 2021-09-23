<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Import\Source;

use Amasty\ImportCore\Api\Source\SourceDataStructureInterface;
use Magento\Framework\DataObject;

class SourceDataStructure extends DataObject implements SourceDataStructureInterface
{
    const ENTITY_CODE = 'entity_code';
    const ID_FIELD_NAME = 'id_field_name';
    const PARENT_ID_FIELD_NAME = 'parent_id_field_name';
    const MAP = 'map';
    const FIELDS = 'fields';
    const FIELDS_MAP = 'fields_map';
    const SUB_ENTITY_STRUCTURES = 'sub_entity_structures';

    /**
     * Data key for sub entities data
     */
    const SUB_ENTITIES_DATA_KEY = '__sub_entities';

    public function getEntityCode()
    {
        return $this->getData(self::ENTITY_CODE);
    }

    public function getMap()
    {
        return $this->getData(self::MAP);
    }

    public function getFields()
    {
        return $this->getData(self::FIELDS) ?? [];
    }

    public function getFieldName($field)
    {
        $fieldsMap = $this->getData(self::FIELDS_MAP);

        return array_search($field, $fieldsMap);
    }

    public function getIdFieldName()
    {
        return $this->getData(self::ID_FIELD_NAME);
    }

    public function getParentIdFieldName()
    {
        return $this->getData(self::PARENT_ID_FIELD_NAME);
    }

    public function getSubEntityStructures()
    {
        return $this->getData(self::SUB_ENTITY_STRUCTURES) ?? [];
    }
}
