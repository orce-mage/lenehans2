<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\DataHandling\FieldModifier;

use Amasty\ExportCore\Api\Config\Profile\FieldInterface;
use Amasty\ExportCore\Api\Config\Profile\ModifierInterface;
use Amasty\ExportCore\Api\FieldModifier\FieldModifierInterface;
use Amasty\ExportCore\Export\DataHandling\AbstractModifier;
use Amasty\ExportCore\Export\DataHandling\ModifierProvider;
use Amasty\ExportCore\Export\Utils\Config\ArgumentConverter;

class Replace extends AbstractModifier implements FieldModifierInterface
{
    /**
     * @var ArgumentConverter
     */
    private $argumentConverter;

    public function __construct($config, ArgumentConverter $argumentConverter)
    {
        parent::__construct($config);
        $this->argumentConverter = $argumentConverter;
    }

    public function transform($value): string
    {
        if (!isset($this->config['from_input_value'])
            || !is_string($this->config['from_input_value'])
            || !is_string($value)
        ) {
            return $value;
        }
        $replaceTo = $this->config['to_input_value'] ?? '';

        return str_replace($this->config['from_input_value'], $replaceTo, $value);
    }

    public function getValue(ModifierInterface $modifier): array
    {
        $modifierData = [];
        foreach ($modifier->getArguments() as $argument) {
            $modifierData['value'][$argument->getName()] = $argument->getValue();
        }
        $modifierData['select_value'] = $modifier->getModifierClass();

        return $modifierData;
    }

    public function prepareArguments(FieldInterface $field, $requestData): array
    {
        $arguments = [];
        if (!empty($requestData['value']['from_input_value'])) {
            $arguments[] = $this->argumentConverter->valueToArguments(
                (string)$requestData['value']['from_input_value'],
                'from_input_value',
                'string'
            );
        }
        if (!empty($requestData['value']['to_input_value'])) {
            $arguments[] = $this->argumentConverter->valueToArguments(
                (string)$requestData['value']['to_input_value'],
                'to_input_value',
                'string'
            );
        }

        return array_merge([], ...$arguments);
    }

    public function getGroup(): string
    {
        return ModifierProvider::TEXT_GROUP;
    }

    public function getLabel(): string
    {
        return __('Replace')->getText();
    }

    public function getJsConfig(): array
    {
        return [
            'component' => 'Amasty_ExportCore/js/fields/modifier',
            'template' => 'Amasty_ExportCore/fields/modifier',
            'childTemplate' => 'Amasty_ExportCore/fields/2inputs-modifier',
            'childComponent' => 'Amasty_ExportCore/js/fields/modifier-field'
        ];
    }
}
