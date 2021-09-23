<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Import\Source\Utils;

class ConvertRowToArray
{
    /**
     * @var array
     */
    private $headerStructure;

    /**
     * Converts row to header structure
     *
     * @param array $row
     * @return array
     */
    public function convert(array $row)
    {
        $result = [];

        $this->fillRowResult($result, [$row], $this->headerStructure);
        $resultOutput = [];
        foreach ($result as $resultRow) {
            end($resultRow);
            $outputRow = $resultRow + array_fill(0, key($resultRow) + 1, '');
            ksort($outputRow);

            $resultOutput[] = $outputRow;
        }

        return $resultOutput;
    }

    /**
     * Get header row for specified entities data
     *
     * @param array $data
     * @return array
     */
    public function getHeaderRow(array $data): array
    {
        if (empty($data)) {
            return [];
        }

        $headerRow = [];
        $headerStructure = $this->initHeaderStructure($data);
        $this->collectHeaderRow($headerRow, $headerStructure);

        return $headerRow;
    }

    /**
     * Collect header row
     *
     * @param array $headerRow
     * @param array $structure
     * @param string $prefix
     * @return void
     */
    private function collectHeaderRow(
        array &$headerRow,
        array $structure,
        $prefix = ''
    ): void {
        foreach ($structure as $key => $structureItem) {
            if (is_array($structureItem)) {
                $this->collectHeaderRow($headerRow, $structureItem, $key);
            } else {
                $headerRow[] = !empty($prefix) ? "{$prefix}.{$key}" : $key;
            }
        }
    }

    /**
     * Init header structure based on entities data
     *
     * @param array $data
     * @return array
     */
    private function initHeaderStructure(array $data)
    {
        $this->headerStructure = [];

        foreach ($data as $row) {
            $rowStructure = $this->getRowStructure($row);
            $this->headerStructure = $this->mergeRowStructures($this->headerStructure, $rowStructure);
        }

        return $this->headerStructure;
    }

    /**
     * Get header structure of specified data row
     *
     * @param array $row
     * @return array
     */
    private function getRowStructure(array $row)
    {
        $rowStructure = [];

        foreach ($row as $key => $value) {
            if (is_array($value)) {
                $subStructure = [];
                foreach ($value as $subRow) {
                    $subStructure = $this->mergeRowStructures(
                        $subStructure,
                        $this->getRowStructure($subRow)
                    );
                }

                $rowStructure[$key] = $subStructure;
            } else {
                $rowStructure[$key] = '';
            }
        }

        return $rowStructure;
    }

    /**
     * Merge header structures of different rows
     *
     * @param array $destStructure
     * @param array $sourceStructure
     * @return array
     */
    private function mergeRowStructures(array $destStructure, array $sourceStructure): array
    {
        foreach ($sourceStructure as $key => $structure) {
            if (!isset($destStructure[$key])) {
                $destStructure = $this->addRowStructure(
                    $destStructure,
                    $key,
                    $structure
                );
            } elseif (is_array($structure)) {
                if (!is_array($destStructure[$key])) {
                    $destStructure = $this->addRowStructure(
                        $destStructure,
                        $key,
                        $structure
                    );
                } else {
                    $destStructure = $this->addRowStructure(
                        $destStructure,
                        $key,
                        $this->mergeRowStructures($destStructure[$key], $structure)
                    );
                }
            }
        }

        return $destStructure;
    }

    /**
     * Add row structure
     *
     * @param array $destStructure
     * @param string $key
     * @param array|string $structure
     * @return array
     */
    private function addRowStructure(array $destStructure, string $key, $structure): array
    {
        list($fields, $subStructures) = $this->splitStructure($destStructure);
        if (is_array($structure)) {
            $subStructures[$key] = $structure;
        } else {
            $fields[$key] = $structure;
        }

        return $this->joinStructure([$fields, $subStructures]);
    }

    /**
     * Splits header structure on parts: fields and sub-entities
     *
     * @param array $structure
     * @return array
     */
    private function splitStructure(array $structure): array
    {
        $parts = [[], []];

        foreach ($structure as $key => $structureItem) {
            if (!is_array($structureItem)) {
                $parts[0][$key] = $structureItem;
            } else {
                $parts[1][$key] = $structureItem;
            }
        }

        return $parts;
    }

    /**
     * Joins header structure from parts
     *
     * @param array $parts
     * @return array
     */
    private function joinStructure(array $parts): array
    {
        return array_merge($parts[0], $parts[1]);
    }

    private function fillRowResult(
        &$result,
        $rows,
        $headerStructure,
        $lineCounter = 0,
        $level = 0,
        $offset = 0
    ) {
        $curOffset = $offset;
        foreach ($rows as $row) {
            $offset = $curOffset;
            $curLine = $nextLine = $lineCounter;
            foreach ($headerStructure as $field => $subentityStructure) {
                if (is_array($subentityStructure)) {
                    [$offset, $maxLine] = $this->fillRowResult(
                        $result,
                        !empty($row[$field]) ? $row[$field] : [[]],
                        $subentityStructure,
                        $lineCounter,
                        $level + 1,
                        $offset
                    );
                    $nextLine = max($nextLine + 1, $maxLine) - 1;
                } else {
                    $result[$curLine][$offset] = $row[$field] ?? '';
                    $offset++;
                }
            }
            $lineCounter = $nextLine + 1;
        }

        return [$offset, $lineCounter];
    }
}
