<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Action\DataPrepare\Source;

use Amasty\ImportCore\Api\ImportProcessInterface;
use Amasty\ImportCore\Api\Source\SourceDataStructureInterface;
use Amasty\ImportCore\Import\Source\Data\DataStructureProvider;
use Amasty\ImportCore\Import\Source\SourceDataStructure;

class SourceDataProcessor
{
    /**
     * @var DataStructureProvider
     */
    private $dataStructureProvider;

    public function __construct(DataStructureProvider $dataStructureProvider)
    {
        $this->dataStructureProvider = $dataStructureProvider;
    }

    /**
     * Convert source data row to the structure suitable for import process
     *
     * @param ImportProcessInterface $importProcess
     * @param array $row
     * @return array
     */
    public function convertToImportProcessStructure(
        ImportProcessInterface $importProcess,
        array $row
    ): array {
        $dataStructure = $this->dataStructureProvider->getDataStructure(
            $importProcess->getEntityConfig(),
            $importProcess->getProfileConfig()
        );

        return $this->prepareSubEntitiesData($dataStructure, $row);
    }

    /**
     * Add sub entity system data keys and move sub entity rows into it
     *
     * @param SourceDataStructureInterface $dataStructure
     * @param array $row
     * @return array
     */
    private function prepareSubEntitiesData(
        SourceDataStructureInterface $dataStructure,
        array $row
    ): array {
        foreach ($dataStructure->getSubEntityStructures() as $subEntityStructure) {
            $key = $subEntityStructure->getMap();
            if (isset($row[$key])) {
                $row[SourceDataStructure::SUB_ENTITIES_DATA_KEY][$key] = [];
                foreach ($row[$key] as $subIndex => $subEntityRow) {
                    if ($this->isRowEmpty($subEntityRow)) {
                        unset($row[SourceDataStructure::SUB_ENTITIES_DATA_KEY][$key][$subIndex]);
                        continue;
                    }
                    $row[SourceDataStructure::SUB_ENTITIES_DATA_KEY][$key][$subIndex] = $this->prepareSubEntitiesData(
                        $subEntityStructure,
                        $subEntityRow
                    );
                }

                $row[SourceDataStructure::SUB_ENTITIES_DATA_KEY][$key] = array_values(
                    $row[SourceDataStructure::SUB_ENTITIES_DATA_KEY][$key]
                );
                unset($row[$key]);
            }
        }

        return $row;
    }

    private function isRowEmpty(array $row): bool
    {
        $empty = true;
        foreach ($row as $value) {
            if (is_array($value)) {
                foreach ($value as $subRow) {
                    if (!$this->isRowEmpty($subRow)) {
                        $empty = false;
                    }
                }
            } elseif (!empty($value)) {
                $empty = false;
            }
        }

        return $empty;
    }
}
