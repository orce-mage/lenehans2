<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Import\Source\Utils;

use Magento\Framework\Stdlib\ArrayManager;

/**
 * Used to convert read row from file to header structure format in CSV, ODS and XLSX type files
 */
class FileRowToArrayConverter
{
    const ENTITY_ID_KEY = '1';
    const PARENT_ID_KEY = '2';

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }

    public function convertRowToHeaderStructure(
        array $structure,
        array $rowData,
        int &$columnCounter = 0
    ): array {
        $convertedData = [];

        foreach ($structure as $key => $value) {
            if (is_array($value)) {
                $convertedData[$key][] = $this->convertRowToHeaderStructure(
                    $value,
                    $rowData,
                    $columnCounter
                );
            } else {
                $convertedData[$key] = $rowData[$columnCounter] ?? null;
                $columnCounter++;
            }
        }

        return $convertedData;
    }

    public function formatMergedSubEntities(array $rowData, array $structure, string $rowSeparator)
    {
        $mainEntityKey = $this->findIdKey(self::ENTITY_ID_KEY, $structure);

        if (!$mainEntityKey) {
            return $rowData;
        }
        $formattedData = [];

        foreach ($rowData as $key => $row) {
            if (is_array($row)) {
                $row = $this->processMergedSubEntity(
                    $rowData[$mainEntityKey],
                    $row[0],
                    $structure[$key],
                    $rowSeparator
                );
            }
            $formattedData[$key] = $row;
        }

        return $formattedData;
    }

    public function mergeRows(array $firstRow, array $secondRow, array $structure): array
    {
        $iterator = new \MultipleIterator();
        $iterator->attachIterator(new \ArrayIterator($firstRow));
        $iterator->attachIterator(new \ArrayIterator($secondRow));

        foreach ($iterator as $key => $row) {
            if (is_array($row[0]) && is_array($row[1])) {
                if (!$this->canMerge($row[0], $row[1], $this->getSubStructure($structure, $key[0]))) {
                    $firstRow[$key[0]][count($row[0])-1] = $this->mergeRows(
                        $row[0][count($row[0]) - 1] ?? $row[0],
                        $row[1][0] ?? $row[1],
                        $structure
                    );
                } else {
                    // phpcs:ignore Magento2.Functions.DiscouragedFunction
                    $firstRow[$key[0]] = call_user_func_array('array_merge', [$row[0], $row[1]]);
                }
            } else {
                $firstRow[$key[0]] = $row[0];
            }
        }

        return $firstRow;
    }

    protected function processMergedSubEntity(
        string $parentId,
        array $subEntityArray,
        array $subEntityStructure,
        string $rowSeparator
    ): array {
        $formattedData = [];
        $explodedSubEntities = [];
        $nestedSubEntities = [];
        $subEntityParentKey = $this->findIdKey(self::PARENT_ID_KEY, $subEntityStructure);
        $subEntityMainKey = $this->findIdKey(self::ENTITY_ID_KEY, $subEntityStructure);

        foreach ($subEntityArray as $key => $row) {
            if (is_array($row)) {
                $nestedSubEntities[$key] = $row;
                continue;
            }
            $explodedSubEntities[$key] = explode($rowSeparator, $row);
        }

        if (!isset($explodedSubEntities[$subEntityParentKey])) {
            throw new \LogicException('We can\'t match parent element with child.');
        }
        $explodedSubEntitiesCount = count($explodedSubEntities[$subEntityParentKey]);
        for ($i = 0; $i < $explodedSubEntitiesCount; $i++) {
            if ($explodedSubEntities[$subEntityParentKey][$i] != $parentId) {
                continue;
            }
            foreach ($explodedSubEntities as $key => $row) {
                $formattedData[$i][$key] = $row[$i];
            }
        }
        $formattedData = array_values($formattedData);

        if (count($nestedSubEntities) > 0) {
            foreach ($formattedData as &$row) {
                foreach ($nestedSubEntities as $subEntityKey => $subEntityData) {
                    $row[$subEntityKey] = $this->processMergedSubEntity(
                        $row[$subEntityMainKey],
                        $subEntityData[0],
                        $subEntityStructure[$subEntityKey],
                        $rowSeparator
                    );
                }
            }
        }

        return $formattedData;
    }

    protected function findIdKey(string $keyType, array $structure)
    {
        foreach ($structure as $key => $value) {
            if (!is_array($value) && strpos($value, $keyType) !== false) {
                return $key;
            }
        }

        return false;
    }

    protected function canMerge(array $firstRow, array $secondRow, array $structure): bool
    {
        $iterator = new \MultipleIterator();
        $iterator->attachIterator(new \ArrayIterator($firstRow));
        $iterator->attachIterator(new \ArrayIterator($secondRow));
        $idFieldName = $this->findIdKey(self::ENTITY_ID_KEY, $structure);

        foreach ($iterator as $key => $row) {
            if (is_array($row[0]) && is_array($row[1])) {
                return $this->canMerge($row[0], $row[1], $structure);
            }
            if (!empty($secondRow[$idFieldName])) {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }

    private function getSubStructure(array $structure, string $key)
    {
        return $this->arrayManager->get($this->arrayManager->findPath($key, $structure), $structure);
    }
}
