<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Template\Type\Csv\Utils;

class ConvertRowTo2DimensionalArray
{
    public function convert(array $row, array $headerStructure)
    {
        $result = [];

        $this->fillRowResult($result, [$row], $headerStructure);
        $resultOutput = [];
        foreach ($result as $resultRow) {
            end($resultRow);
            $outputRow = $resultRow + array_fill(0, key($resultRow) + 1, '');
            ksort($outputRow);

            $resultOutput[] = $outputRow;
        }

        return $resultOutput;
    }

    public function fillRowResult(
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
