<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Import\Source\Data;

use Amasty\ImportCore\Api\Config\EntityConfigInterface;
use Amasty\ImportCore\Api\Config\ProfileConfigInterface;
use Amasty\ImportCore\Api\Config\Profile\EntitiesConfigInterface as ProfileEntitiesConfigInterface;
use Amasty\ImportCore\Api\Config\Relation\RelationConfigInterface;
use Amasty\ImportCore\Api\Source\SourceDataStructureInterface;
use Amasty\ImportCore\Import\Config\RelationConfigProvider;
use Amasty\ImportCore\Import\Source\SourceDataStructure;
use Amasty\ImportCore\Import\Source\SourceDataStructureFactory;

class DataStructureProvider
{
    /**
     * @var RelationConfigProvider
     */
    private $relationConfigProvider;

    /**
     * @var DataStructureBuilder
     */
    private $dataStructureBuilder;

    /**
     * @var SourceDataStructureFactory
     */
    private $dataStructureFactory;

    /**
     * @var SourceDataStructureInterface
     */
    private $dataStructure;

    public function __construct(
        RelationConfigProvider $relationConfigProvider,
        DataStructureBuilder $dataStructureBuilder,
        SourceDataStructureFactory $dataStructureFactory
    ) {
        $this->relationConfigProvider = $relationConfigProvider;
        $this->dataStructureBuilder = $dataStructureBuilder;
        $this->dataStructureFactory = $dataStructureFactory;
    }

    /**
     * Get source data structure using entity config and profile config
     *
     * @param EntityConfigInterface $entityConfig
     * @param ProfileConfigInterface $profileConfig
     * @return SourceDataStructureInterface
     */
    public function getDataStructure(
        EntityConfigInterface $entityConfig,
        ProfileConfigInterface $profileConfig
    ): SourceDataStructureInterface {
        if (!$this->dataStructure) {
            $this->dataStructure = $this->dataStructureFactory->create(
                [
                    'data' => $this->dataStructureBuilder->buildEntityDataStructure(
                        $entityConfig,
                        $profileConfig,
                        $this->getSubEntitiesDataStructures(
                            $profileConfig->getEntitiesConfig(),
                            $this->relationConfigProvider->get($profileConfig->getEntityCode())
                        )
                    )
                ]
            );
        }

        return $this->dataStructure;
    }

    /**
     * @param ProfileEntitiesConfigInterface $profileEntities
     * @param RelationConfigInterface[] $relationConfigs
     * @return array
     */
    private function getSubEntitiesDataStructures(
        ProfileEntitiesConfigInterface $profileEntities,
        array $relationConfigs
    ): array {
        $dataStructures = [];

        foreach ($profileEntities->getSubEntitiesConfig() as $subEntityConfig) {
            $relationConfig = $this->getRelationConfigByChildEntityCode(
                $subEntityConfig->getEntityCode(),
                $relationConfigs
            );

            if ($relationConfig) {
                $structureData = $this->dataStructureBuilder->buildSubEntityDataStructure(
                    $subEntityConfig,
                    $relationConfig,
                    $this->getSubEntitiesDataStructures($subEntityConfig, $relationConfig->getRelations() ?: [])
                );

                /** @var SourceDataStructure $dataStructure */
                $dataStructure = $this->dataStructureFactory->create(
                    ['data' => $structureData]
                );

                $map = $dataStructure->getMap();
                if ($map) {
                    $dataStructures[$map] = $dataStructure;
                } else {
                    $dataStructures[] = $dataStructure;
                }
            }
        }

        return $dataStructures;
    }

    /**
     * Get relation config by child entity code
     *
     * @param string $entityCode
     * @param RelationConfigInterface[] $relationConfigs
     * @return RelationConfigInterface|null
     */
    private function getRelationConfigByChildEntityCode(
        $entityCode,
        array $relationConfigs
    ) {
        foreach ($relationConfigs as $relationConfig) {
            if ($relationConfig->getChildEntityCode() == $entityCode) {
                return $relationConfig;
            }
        }

        return null;
    }
}
