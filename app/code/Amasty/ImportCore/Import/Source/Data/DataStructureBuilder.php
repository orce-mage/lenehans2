<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Import\Source\Data;

use Amasty\ImportCore\Api\Config\EntityConfigInterface;
use Amasty\ImportCore\Api\Config\Profile\EntitiesConfigInterface as ProfileEntitiesConfigInterface;
use Amasty\ImportCore\Api\Config\Profile\FieldInterface;
use Amasty\ImportCore\Api\Config\ProfileConfigInterface;
use Amasty\ImportCore\Api\Config\Relation\RelationConfigInterface;
use Amasty\ImportCore\Api\Source\SourceDataStructureInterface;
use Amasty\ImportCore\Import\Config\EntityConfigProvider;
use Amasty\ImportCore\Import\Source\SourceDataStructure;

class DataStructureBuilder
{
    /**
     * @var EntityConfigProvider
     */
    private $entityConfigProvider;

    public function __construct(
        EntityConfigProvider $entityConfigProvider
    ) {
        $this->entityConfigProvider = $entityConfigProvider;
    }

    /**
     * Build entity data structure
     *
     * @param EntityConfigInterface $entityConfig
     * @param ProfileConfigInterface $profileConfig
     * @param SourceDataStructureInterface[] $subEntityStructures
     * @return array
     */
    public function buildEntityDataStructure(
        EntityConfigInterface $entityConfig,
        ProfileConfigInterface $profileConfig,
        array $subEntityStructures = []
    ): array {
        $dataStructure = [
            SourceDataStructure::ENTITY_CODE => $entityConfig->getEntityCode(),
            SourceDataStructure::FIELDS => $this->getFieldNames($profileConfig->getEntitiesConfig()),
            SourceDataStructure::FIELDS_MAP => $this->getFieldsMap($profileConfig->getEntitiesConfig()),
            SourceDataStructure::SUB_ENTITY_STRUCTURES => $subEntityStructures,
            SourceDataStructure::ID_FIELD_NAME => $this->getIdFieldName($entityConfig)
        ];

        $map = $profileConfig->getEntitiesConfig()->getMap();
        if ($map) {
            $dataStructure[SourceDataStructure::MAP] = $map;
        }

        return $dataStructure;
    }

    /**
     * Build sub entity data structure
     *
     * @param ProfileEntitiesConfigInterface $profileSubEntityConfig
     * @param RelationConfigInterface $relationConfig
     * @param SourceDataStructureInterface[] $subEntityStructures
     * @return array
     */
    public function buildSubEntityDataStructure(
        ProfileEntitiesConfigInterface $profileSubEntityConfig,
        RelationConfigInterface $relationConfig,
        array $subEntityStructures = []
    ): array {
        $entityCode = $profileSubEntityConfig->getEntityCode();
        $idField = $this->getIdFieldName($this->entityConfigProvider->get($entityCode));

        $map = $profileSubEntityConfig->getMap() ?: $relationConfig->getSubEntityFieldName();

        return [
            SourceDataStructure::ENTITY_CODE => $entityCode,
            SourceDataStructure::ID_FIELD_NAME => $idField,
            SourceDataStructure::PARENT_ID_FIELD_NAME => $relationConfig->getChildFieldName(),
            SourceDataStructure::MAP => $map,
            SourceDataStructure::FIELDS => $this->getFieldNames($profileSubEntityConfig),
            SourceDataStructure::FIELDS_MAP => $this->getFieldsMap($profileSubEntityConfig),
            SourceDataStructure::SUB_ENTITY_STRUCTURES => $subEntityStructures
        ];
    }

    /**
     * Get field names using fields config and profile fields
     *
     * @param ProfileEntitiesConfigInterface $entitiesConfig
     * @return array
     */
    private function getFieldNames(ProfileEntitiesConfigInterface $entitiesConfig): array
    {
        $fields = $entitiesConfig->getFields();

        /**
         * @param FieldInterface $field
         * @return string
         */
        $getFieldNameCallback = function (FieldInterface $field) {
            return $field->getMap() ?: $field->getName();
        };

        return array_map($getFieldNameCallback, $fields);
    }

    /**
     * Get fields map
     *
     * @param ProfileEntitiesConfigInterface $entitiesConfig
     * @return array
     */
    private function getFieldsMap(ProfileEntitiesConfigInterface $entitiesConfig): array
    {
        $fieldsMap = [];

        foreach ($entitiesConfig->getFields() as $field) {
            $fieldsMap[$field->getName()] = $field->getMap();
        }

        return $fieldsMap;
    }

    private function getIdFieldName(EntityConfigInterface $entityConfig): ?string
    {
        foreach ($entityConfig->getFieldsConfig()->getFields() as $field) {
            if ($field->isIdentity()) {
                return $field->getName();
            }
        }

        return null;
    }
}
