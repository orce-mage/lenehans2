<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Import\Utils\Config;

use Amasty\ImportExportCore\Api\Config\ConfigClass\ArgumentInterface;
use Amasty\ImportExportCore\Config\Xml\ArgumentsPrepare;

class ArgumentConverter
{
    /**
     * @var ArgumentsPrepare
     */
    private $argumentsPreparer;

    public function __construct(ArgumentsPrepare $argumentsPreparer)
    {
        $this->argumentsPreparer = $argumentsPreparer;
    }

    public function toArguments(array $data): array
    {
        $result = [];
        foreach ($data as $argName => $item) {
            if (is_array($item)) {
                $arguments = $this->arrayToArguments($item, $argName);
            } else {
                $arguments = $this->valueToArguments($item, $argName);
            }

            if (!empty($arguments)) {
                $result[] = $arguments[0];
            }
        }

        return $result;
    }

    /**
     * Convert simple value to arguments
     *
     * @param mixed $value
     * @param string $argName
     * @param string|null $xsiType
     * @return ArgumentInterface[]
     */
    public function valueToArguments($value, string $argName, string $xsiType = null): array
    {
        if (!$xsiType) {
            $xsiType = $this->getXsiType($value);
        }

        return $this->argumentsPreparer->execute(
            [
                $argName => [
                    'name' => $argName,
                    'xsi:type' => $xsiType,
                    'value' => $value
                ]
            ]
        );
    }

    /**
     * Convert data array to arguments
     *
     * @param array $array
     * @param string $argName
     * @return ArgumentInterface[]
     */
    public function arrayToArguments(array $array, $argName): array
    {
        $argumentData = $this->prepareArray($array, $argName);

        return count($argumentData['item'])
            ? $this->argumentsPreparer->execute([$argumentData])
            : [];
    }

    /**
     * Prepare data array for convert
     *
     * @param array $array
     * @param string $argName
     * @return array
     */
    private function prepareArray(array $array, $argName)
    {
        $result = [
            'name' => $argName,
            'xsi:type' => 'array',
            'item' => []
        ];
        foreach ($array as $index => $item) {
            if (is_array($item)) {
                $result['item'][] = $this->prepareArray($item, $index);
            } else {
                $result['item'][] = [
                    'name' => $index,
                    'xsi:type' => $this->getXsiType($item),
                    'value' => $item
                ];
            }
        }

        return $result;
    }

    /**
     * Get xsi:type of value
     *
     * @param mixed $value
     * @return string
     */
    private function getXsiType($value)
    {
        if (is_numeric($value) && !is_string($value)) {
            return 'number';
        }
        if (is_bool($value)) {
            return 'boolean';
        }

        return 'string';
    }
}
