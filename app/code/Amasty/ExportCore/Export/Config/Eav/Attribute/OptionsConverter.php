<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */

declare(strict_types=1);

namespace Amasty\ExportCore\Export\Config\Eav\Attribute;

use Amasty\ImportExportCore\Api\Config\ConfigClass\ArgumentInterface;
use Amasty\ImportExportCore\Config\Xml\ArgumentsPrepare;

class OptionsConverter
{
    /**
     * @var ArgumentsPrepare
     */
    private $argumentsPreparer;

    public function __construct(ArgumentsPrepare $argumentsPreparer)
    {
        $this->argumentsPreparer = $argumentsPreparer;
    }

    /**
     * Convert option array to config argument instances
     *
     * @param array $options
     * @param string $argumentName
     * @return ArgumentInterface[]
     */
    public function toConfigArguments(array $options, $argumentName)
    {
        $argumentData = $this->prepareForConvert($options, $argumentName);
        return count($argumentData['item'])
            ? $this->argumentsPreparer->execute([$argumentData])
            : [];
    }

    public function getConfigArgumentDataType($attribute)
    {
        $argumentData = [
            'name' => 'dataType',
            'xsi:type' => 'string',
            'value' => $attribute->getFrontendInput()
        ];

        return $this->argumentsPreparer->execute([$argumentData]);
    }

    /**
     * Prepare option for convert
     *
     * @param array $options
     * @param string $argumentName
     * @return array
     */
    private function prepareForConvert(array $options, $argumentName)
    {
        $result = [
            'name' => $argumentName,
            'xsi:type' => 'array',
            'item' => []
        ];
        foreach ($options as $index => $option) {
            $value = $option['value'] ?? '';
            if (is_string($value) && empty($value)) {
                continue;
            }

            if (is_array($value)) {
                $result['item'][] = $this->prepareForConvert($value, $index);
            } else {
                $result['item'][] = [
                    'name' => $index,
                    'xsi:type' => 'array',
                    'item' => [
                        [
                            'name' => 'value',
                            'xsi:type' => $this->getXsiType($value),
                            'value' => $value
                        ],
                        [
                            'name' => 'label',
                            'xsi:type' => 'string',
                            'value' => (string)$option['label']
                        ]
                    ]
                ];
            }
        }
        return $result;
    }

    /**
     * Get xsi:type of option value
     *
     * @param mixed $value
     * @return string
     */
    private function getXsiType($value)
    {
        if (is_numeric($value) && !is_string($value)) {
            return 'number';
        }
        return 'string';
    }
}
