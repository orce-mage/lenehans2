<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Import\Source\Utils;

use Amasty\ImportCore\Api\Source\SourceDataStructureInterface;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Used to format header structure from CSV, ODS and XLSX type files for further converting
 */
class HeaderStructureProcessor
{
    const VIRTUAL_PREFIX_DELIMITER = '.';

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var array
     */
    private $colNumbersToSkip = [];

    /**
     * @var array
     */
    private $entitiesOrder;

    /**
     * @var array
     */
    private $entitiesKeyRanges = [];

    /**
     * @var SourceDataStructureInterface[]
     */
    private $dataStructuresByEntityMap = [];

    public function __construct(ArrayManager $arrayManager)
    {
        $this->arrayManager = $arrayManager;
    }

    public function getColNumbersToSkip(): array
    {
        return $this->colNumbersToSkip;
    }

    /**
     * Get structure of table header
     *
     * @param SourceDataStructureInterface $dataStructure
     * @param array $headerRow
     * @param string $prefixDelimiter
     * @return array
     */
    public function getHeaderStructure(
        SourceDataStructureInterface $dataStructure,
        array $headerRow,
        string $prefixDelimiter
    ): array {
        if (empty($prefixDelimiter)) {
            $headerRow = $this->normalizeHeaderRow($dataStructure, $headerRow);
            $prefixDelimiter = self::VIRTUAL_PREFIX_DELIMITER;
        }

        $flatHeaderStructure = [];
        $entitiesOrder = $this->getEntitiesOrder($headerRow, $prefixDelimiter);

        foreach ($entitiesOrder as $entityMap) {
            $entityDataStructure = $this->getDataStructureByEntityMap($dataStructure, $entityMap);

            $structureFields = [];
            if ($entityDataStructure) {
                foreach ($entityDataStructure->getFields() as $field) {
                    $headerValue = '';
                    if ($field == $entityDataStructure->getIdFieldName()) {
                        $headerValue .= FileRowToArrayConverter::ENTITY_ID_KEY;
                    }
                    if ($field == $entityDataStructure->getParentIdFieldName()) {
                        $headerValue .= FileRowToArrayConverter::PARENT_ID_KEY;
                    }

                    $flatHeaderStructure[$entityMap][$field] = $headerValue;
                    $structureFields[] = $field;
                }
            }

            $keysRange = $this->getEntityKeyRange($entityMap, $headerRow, $prefixDelimiter);
            if ($keysRange) {
                $this->skipCols(
                    $structureFields,
                    $headerRow,
                    $keysRange,
                    $prefixDelimiter,
                    $entityMap
                );
                $this->sortEntityCols(
                    $flatHeaderStructure,
                    $headerRow,
                    $keysRange,
                    $prefixDelimiter,
                    $entityMap
                );
            }
        }

        return $this->convertToNestedStructure($dataStructure, $flatHeaderStructure);
    }

    private function normalizeHeaderRow(SourceDataStructureInterface $dataStructure, array $headerRow): array
    {
        $normalizedFieldKeys = [];
        $entityMap = $this->getSortedEntityMaps($dataStructure);

        foreach ($entityMap as $entityPrefix) {
            foreach ($headerRow as $key => &$field) {
                if (strpos($field, $entityPrefix) === 0 && !in_array($key, $normalizedFieldKeys)) {
                    $field = preg_replace(
                        '/' . preg_quote($entityPrefix, '/') . '/',
                        $entityPrefix . self::VIRTUAL_PREFIX_DELIMITER,
                        $field,
                        1
                    );
                    $normalizedFieldKeys[] = $key;
                }
            }
        }

        return $headerRow;
    }

    /**
     * Mark cols that aren't specified in the data structure as skipped
     *
     * @param array $structureFields
     * @param array $headerRow
     * @param array $range
     * @param string $separator
     * @param string $entityMap
     * @return void
     */
    private function skipCols(
        array $structureFields,
        array $headerRow,
        array $range,
        string $separator,
        string $entityMap
    ) {
        $entityHeaderRow = $this->getHeaderRowSlice($headerRow, $range);

        foreach ($entityHeaderRow as $key => $col) {
            $explodedCol = explode($separator, $col);
            $explodedColCount = count($explodedCol);

            if ($entityMap == $explodedCol[0]
                || $entityMap == '' && $explodedColCount == 1
            ) {
                $field = $explodedColCount == 1
                    ? $explodedCol[0]
                    : $explodedCol[1];

                if (!in_array($field, $structureFields)) {
                    $this->colNumbersToSkip[] = $key;
                }
            }
        }
    }

    /**
     * Sort sort columns in flat structure according to the sort order in the source file
     *
     * @param array $flatStructure
     * @param array $headerRow
     * @param array $range
     * @param string $separator
     * @param string $entityMap
     * @return void
     */
    private function sortEntityCols(
        array &$flatStructure,
        array $headerRow,
        array $range,
        string $separator,
        string $entityMap
    ) {
        $entityHeaderRow = $this->getHeaderRowSlice($headerRow, $range);
        $entityFlatStructure = $flatStructure[$entityMap] ?? [];

        if (!empty($entityFlatStructure)) {
            $sortedFlatStructure = [];
            foreach ($entityHeaderRow as $key => $col) {
                $explodedCol = explode($separator, $col);
                $explodedColCount = count($explodedCol);

                if ($entityMap == $explodedCol[0]
                    || $entityMap == '' && $explodedColCount == 1
                ) {
                    $field = $explodedColCount == 1
                        ? $explodedCol[0]
                        : $explodedCol[1];

                    if (isset($entityFlatStructure[$field])) {
                        $sortedFlatStructure[$field] = $entityFlatStructure[$field];
                    }
                }
            }

            $flatStructure[$entityMap] = $sortedFlatStructure;
        }
    }

    /**
     * Get header row slice using specified range
     *
     * @param array $headerRow
     * @param array $range
     * @return array
     */
    private function getHeaderRowSlice(array $headerRow, array $range): array
    {
        $offset = $range['from'] ?? 0;
        $length = isset($range['to'])
            ? $range['to'] - $offset + 1
            : count($headerRow) - $offset + 1;

        return array_slice($headerRow, $offset, $length, true);
    }

    /**
     * Convert flat header structure to nested header structure
     *
     * @param SourceDataStructureInterface $dataStructure
     * @param array $flatHeaderStructure
     * @return array
     */
    private function convertToNestedStructure(
        SourceDataStructureInterface $dataStructure,
        array $flatHeaderStructure
    ): array {
        $nestedStructure = [];

        foreach ($flatHeaderStructure as $entityMap => $structureFields) {
            $path = $this->getSubEntityNestingPath($dataStructure, $entityMap);
            $nestedStructure = $this->arrayManager->set(
                $path,
                $nestedStructure,
                $structureFields
            );
        }

        return $nestedStructure[$dataStructure->getMap()] ?? [];
    }

    /**
     * Get entity nesting path in source data structure object
     *
     * @param SourceDataStructureInterface $dataStructure
     * @param string $entityMap
     * @return array
     */
    private function getSubEntityNestingPath(
        SourceDataStructureInterface $dataStructure,
        string $entityMap
    ): array {
        $path = [];
        $this->collectEntityNestingPath($dataStructure, $entityMap, $path);

        return $path;
    }

    /**
     * Collect entity nesting path
     *
     * @param SourceDataStructureInterface $dataStructure
     * @param string $entityMap
     * @param array $path
     * @param int $level
     * @param bool $found
     */
    private function collectEntityNestingPath(
        SourceDataStructureInterface $dataStructure,
        string $entityMap,
        array &$path,
        int $level = 0,
        bool &$found = false
    ) {
        if (!$found) {
            $candidateMap = $dataStructure->getMap() ?: '';
            $path[$level] = $candidateMap;

            if ($candidateMap == $entityMap) {
                $found = true;
                $path = array_slice($path, 0, $level + 1);
            } else {
                foreach ($dataStructure->getSubEntityStructures() as $subEntityStructure) {
                    $this->collectEntityNestingPath(
                        $subEntityStructure,
                        $entityMap,
                        $path,
                        $level + 1,
                        $found
                    );
                }
            }
        }
    }

    /**
     * Get entities order in the source file
     *
     * @param array $headerRow
     * @param string $prefixDelimiter
     * @return array
     */
    private function getEntitiesOrder(array $headerRow, string $prefixDelimiter): array
    {
        if (!$this->entitiesOrder) {
            $this->collectSourceEntitiesMeta($headerRow, $prefixDelimiter);
        }

        return $this->entitiesOrder;
    }

    /**
     * Get entity key range in the source file
     *
     * @param string $entityMap
     * @param array $headerRow
     * @param string $prefixDelimiter
     * @return array|null
     */
    private function getEntityKeyRange(
        string $entityMap,
        array $headerRow,
        string $prefixDelimiter
    ): ?array {
        if (empty($this->entitiesKeyRanges[$entityMap])) {
            $this->collectSourceEntitiesMeta($headerRow, $prefixDelimiter);
        }

        return $this->entitiesKeyRanges[$entityMap] ?? null;
    }

    /**
     * Collect entities metadata.
     * Metadata here - extra information based on format/reader specific features:
     *  - entities order in the source file;
     *  - entities data key ranges
     *
     * @param array $headerRow
     * @param string $prefixDelimiter
     * @return void
     */
    private function collectSourceEntitiesMeta(array $headerRow, string $prefixDelimiter)
    {
        $this->entitiesOrder = [];

        $previousEntityMap = null;
        foreach ($headerRow as $key => $field) {
            $explodedField = explode($prefixDelimiter, $field);

            $entityMap = isset($explodedField[1])
                ? $explodedField[0]
                : '';
            if (!in_array($entityMap, $this->entitiesOrder)) {
                $this->entitiesOrder[] = $entityMap;
            }

            if (!isset($this->entitiesKeyRanges[$entityMap])) {
                $this->entitiesKeyRanges[$entityMap] = ['from' => $key];
            }
            if ($entityMap != $previousEntityMap
                && $previousEntityMap !== null
            ) {
                $this->entitiesKeyRanges[$previousEntityMap]['to'] = $key - 1;
            }

            $previousEntityMap = $entityMap;
        }
    }

    private function getSortedEntityMaps(SourceDataStructureInterface $dataStructure)
    {
        if (!$this->dataStructuresByEntityMap) {
            $this->collectDataStructuresByEntityMap($dataStructure);
        }

        $entityMap = array_filter(array_keys($this->dataStructuresByEntityMap));
        usort($entityMap, function ($a, $b) {
            return strlen($b) <=> strlen($a);
        });

        return $entityMap;
    }

    /**
     * Get data structure using entity map value
     *
     * @param SourceDataStructureInterface $dataStructure
     * @param string $entityMap
     * @return SourceDataStructureInterface|null
     */
    private function getDataStructureByEntityMap(
        SourceDataStructureInterface $dataStructure,
        string $entityMap
    ): ?SourceDataStructureInterface {
        if (!$this->dataStructuresByEntityMap) {
            $this->collectDataStructuresByEntityMap($dataStructure);
        }

        return $this->dataStructuresByEntityMap[$entityMap] ?? null;
    }

    /**
     * Collect data structures keyed by entity map
     *
     * @param SourceDataStructureInterface $dataStructure
     * @return void
     */
    private function collectDataStructuresByEntityMap(SourceDataStructureInterface $dataStructure)
    {
        $this->dataStructuresByEntityMap[$dataStructure->getMap()] = $dataStructure;

        foreach ($dataStructure->getSubEntityStructures() as $subEntityStructure) {
            $this->collectDataStructuresByEntityMap($subEntityStructure);
        }
    }
}
