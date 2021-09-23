<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\DataHandling;

use Amasty\ExportCore\Api\Config\Profile\FieldInterface;
use Amasty\ExportCore\Api\Config\Profile\ModifierInterface;

abstract class AbstractModifier
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getValue(ModifierInterface $modifier): array
    {
        $modifierData = [];
        foreach ($modifier->getArguments() as $argument) {
            $modifierData[$argument->getName()] = $argument->getValue();
        }
        $modifierData['select_value'] = $modifier->getModifierClass();
        $modifierData['label'] = $this->getLabel();

        return $modifierData;
    }

    public function prepareArguments(FieldInterface $field, $requestData): array
    {
        return [];
    }

    public function getJsConfig(): array
    {
        return [
            'component' => 'Amasty_ExportCore/js/fields/modifier',
            'template' => 'Amasty_ExportCore/fields/modifier'
        ];
    }

    public function getGroup(): string
    {
        return ModifierProvider::TEXT_GROUP;
    }

    public function prepareRowOptions(array $row)
    {
        return null;
    }

    abstract public function getLabel(): string;
}
