<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Action\DataPrepare\Mapping;

class Mapper
{
    public function mapData(array &$data, array ...$mappings)
    {
        $mergedMappings = $this->prepareMapping($mappings);
        if (count($mergedMappings)) {
            $this->remapColumns($data, $mergedMappings);
        }
    }

    public function mapRow(array &$row, array ...$mappings)
    {
        $mergedMappings = $this->prepareMapping($mappings);
        if (count($mergedMappings)) {
            $this->remapRow($row, $mergedMappings);
        }
    }

    /**
     * Prepare mapping input arguments
     *
     * @param array $mappings
     * @return array
     */
    private function prepareMapping(array $mappings)
    {
        switch (count($mappings)) {
            case 0:
                return [];
            case 1:
                return reset($mappings);
            default:
                return $this->mergeMappings($mappings);
        }
    }

    protected function remapColumns(array &$data, array $map)
    {
        foreach ($data as &$row) {
            $this->remapRow($row, $map);
        }
    }

    protected function remapRow(array &$row, array $map)
    {
        foreach ($map as $from => $to) {
            if (!isset($row[$from])) {
                continue;
            }
            $value = $row[$from];
            unset($row[$from]);
            $row[$to] = $value;
        }
    }

    protected function mergeMappings(array $mappings): array
    {
        $mergedMapping = $mappings[0];
        $mappingsCount = count($mappings);
        for ($i = 1; $i < $mappingsCount; $i++) {
            foreach ($mappings[$i] as $from => $to) {
                if (false !== ($key = array_search($from, $mergedMapping))) {
                    $mergedMapping[$key] = $to;
                } else {
                    $mergedMapping[$from] = $to;
                }
            }
        }

        return $mergedMapping;
    }
}
