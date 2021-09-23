<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Action\DataPrepare\Mapping;

use Amasty\ImportCore\Api\Config\Profile\EntitiesConfigInterface;
use Amasty\ImportCore\Api\Source\SourceDataStructureInterface;
use Amasty\ImportCore\Import\Config\EntityConfigProvider;

class MapProvider
{
    /**
     * @var EntityConfigProvider
     */
    private $entityConfigProvider;

    /**
     * @var array
     */
    private $entityMaps = [];

    /**
     * @var array
     */
    private $fieldsMaps = [];

    public function __construct(EntityConfigProvider $entityConfigProvider)
    {
        $this->entityConfigProvider = $entityConfigProvider;
    }

    /**
     * Get entity map
     *
     * @param SourceDataStructureInterface $dataStructure
     * @return array
     */
    public function getEntityMap(SourceDataStructureInterface $dataStructure)
    {
        $entityCode = $dataStructure->getEntityCode();
        if (!isset($this->entityMaps[$entityCode])) {
            $this->entityMaps[$entityCode] = [];

            $prefix = $dataStructure->getMap();
            if ($prefix) {
                foreach ($dataStructure->getFields() as $field) {
                    $this->entityMaps[$entityCode][$prefix . '.' . $field] = $field;
                }
            }
        }

        return $this->entityMaps[$entityCode];
    }

    /**
     * Get sub entities map.
     * Used for mapping sub entity prefix to sub entity code
     *
     * @param SourceDataStructureInterface $dataStructure
     * @return array
     */
    public function getSubEntitiesMap(SourceDataStructureInterface $dataStructure)
    {
        $subEntitiesMap = [];

        foreach ($dataStructure->getSubEntityStructures() as $subEntityStructure) {
            $subEntitiesMap[$subEntityStructure->getMap()] = $subEntityStructure->getEntityCode();
        }

        return $subEntitiesMap;
    }

    /**
     * Get fields map using entity config
     *
     * @param EntitiesConfigInterface $entitiesConfig
     * @return array
     */
    public function getFieldsMap(EntitiesConfigInterface $entitiesConfig)
    {
        $entityCode = $entitiesConfig->getEntityCode();

        if (!isset($this->fieldsMaps[$entityCode])) {
            $this->fieldsMaps[$entityCode] = [];

            foreach ($entitiesConfig->getFields() as $field) {
                $map = $field->getMap();
                if ($map) {
                    $this->fieldsMaps[$entityCode][$map] = $field->getName();
                }
            }
        }

        return $this->fieldsMaps[$entityCode];
    }
}
