<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Action\DataPrepare\Mapping;

use Amasty\ImportCore\Api\ActionInterface;
use Amasty\ImportCore\Api\Config\Profile\EntitiesConfigInterface;
use Amasty\ImportCore\Api\ImportProcessInterface;
use Amasty\ImportCore\Api\Source\SourceDataStructureInterface;
use Amasty\ImportCore\Import\Source\Data\DataStructureProvider;
use Amasty\ImportCore\Import\Source\SourceDataStructure;

class MappingAction implements ActionInterface
{
    /**
     * @var Mapper
     */
    private $mapper;

    /**
     * @var MapProvider
     */
    private $mapProvider;

    /**
     * @var DataStructureProvider
     */
    private $dataStructureProvider;

    /**
     * @var SourceDataStructureInterface
     */
    private $dataStructure;

    public function __construct(
        Mapper $mapper,
        MapProvider $mapProvider,
        DataStructureProvider $dataStructureProvider
    ) {
        $this->mapper = $mapper;
        $this->mapProvider = $mapProvider;
        $this->dataStructureProvider = $dataStructureProvider;
    }

    public function execute(ImportProcessInterface $importProcess): void
    {
        $data = $importProcess->getData();

        $this->mapper->mapData($data, $this->mapProvider->getEntityMap($this->dataStructure));
        $this->mapSubEntityKeys($data, $this->dataStructure);

        $this->mapper->mapData(
            $data,
            $this->mapProvider->getFieldsMap(
                $importProcess->getProfileConfig()->getEntitiesConfig()
            )
        );
        $this->mapSubEntityFields($data, $importProcess->getProfileConfig()->getEntitiesConfig());

        $importProcess->setData($data);

        if ($importProcess->getBatchNumber() == 1) {
            $importProcess->addInfoMessage((string)__('The data is being mapped.'));
        }
    }

    public function initialize(ImportProcessInterface $importProcess): void
    {
        $this->dataStructure = $this->dataStructureProvider->getDataStructure(
            $importProcess->getEntityConfig(),
            $importProcess->getProfileConfig()
        );
    }

    private function mapSubEntityKeys(array &$data, SourceDataStructureInterface $dataStructure)
    {
        foreach ($data as &$row) {
            if (isset($row[SourceDataStructure::SUB_ENTITIES_DATA_KEY])) {
                $this->mapper->mapRow(
                    $row[SourceDataStructure::SUB_ENTITIES_DATA_KEY],
                    $this->mapProvider->getSubEntitiesMap($dataStructure)
                );

                foreach ($dataStructure->getSubEntityStructures() as $subEntityStructure) {
                    $subEntityCode = $subEntityStructure->getEntityCode();
                    if (isset($row[SourceDataStructure::SUB_ENTITIES_DATA_KEY][$subEntityCode])) {
                        $this->mapSubEntityKeys(
                            $row[SourceDataStructure::SUB_ENTITIES_DATA_KEY][$subEntityCode],
                            $subEntityStructure
                        );
                    }
                }
            }
        }
    }

    private function mapSubEntityFields(array &$data, EntitiesConfigInterface $entitiesConfig)
    {
        foreach ($data as &$row) {
            if (isset($row[SourceDataStructure::SUB_ENTITIES_DATA_KEY])) {
                foreach ($row[SourceDataStructure::SUB_ENTITIES_DATA_KEY] as $subEntityCode => &$subEntityRows) {
                    if ($subEntityConfig = $this->getEntityConfigByCode($subEntityCode, $entitiesConfig)) {
                        $this->mapper->mapData(
                            $subEntityRows,
                            $this->mapProvider->getFieldsMap($subEntityConfig)
                        );

                        $this->mapSubEntityFields($subEntityRows, $subEntityConfig);
                    }
                }
            }
        }
    }

    private function getEntityConfigByCode(
        string $subEntityCode,
        EntitiesConfigInterface $entitiesConfig
    ): ?EntitiesConfigInterface {
        foreach ($entitiesConfig->getSubEntitiesConfig() as $subEntityConfig) {
            if ($subEntityConfig->getEntityCode() == $subEntityCode) {
                return $subEntityConfig;
            }
        }

        return null;
    }
}
