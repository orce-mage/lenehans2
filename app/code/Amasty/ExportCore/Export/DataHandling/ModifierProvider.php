<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\DataHandling;

use Amasty\ExportCore\Api\Config\EntityConfigInterface;
use Amasty\ExportCore\Api\FieldModifier\FieldModifierInterface;
use Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterfaceFactory;
use Amasty\ImportExportCore\Config\ConfigClass\Factory;

class ModifierProvider
{
    const TEXT_GROUP = 'text';
    const NUMERIC_GROUP = 'numeric';
    const DATE_GROUP = 'date';
    const CUSTOM_GROUP = 'custom';

    private $defaultModifiers = [
        // text
        \Amasty\ExportCore\Export\DataHandling\FieldModifier\Append::class,
        \Amasty\ExportCore\Export\DataHandling\FieldModifier\Prepend::class,
        \Amasty\ExportCore\Export\DataHandling\FieldModifier\Trim::class,
        \Amasty\ExportCore\Export\DataHandling\FieldModifier\Uppercase::class,
        \Amasty\ExportCore\Export\DataHandling\FieldModifier\Lowercase::class,
        \Amasty\ExportCore\Export\DataHandling\FieldModifier\Capitalize::class,
        \Amasty\ExportCore\Export\DataHandling\FieldModifier\CapitalizeEachWord::class,
        \Amasty\ExportCore\Export\DataHandling\FieldModifier\Strip::class,
        \Amasty\ExportCore\Export\DataHandling\FieldModifier\Replace::class,
        \Amasty\ExportCore\Export\DataHandling\FieldModifier\ReplaceFirst::class,
        \Amasty\ExportCore\Export\DataHandling\FieldModifier\DefaultValue::class,

        // numeric
        \Amasty\ExportCore\Export\DataHandling\FieldModifier\Number\Price::class,
        \Amasty\ExportCore\Export\DataHandling\FieldModifier\Number\Absolute::class,
        \Amasty\ExportCore\Export\DataHandling\FieldModifier\Number\Round::class,
        \Amasty\ExportCore\Export\DataHandling\FieldModifier\Number\Plus::class,
        \Amasty\ExportCore\Export\DataHandling\FieldModifier\Number\Minus::class,
        \Amasty\ExportCore\Export\DataHandling\FieldModifier\Number\Multiple::class,
        \Amasty\ExportCore\Export\DataHandling\FieldModifier\Number\Divide::class,
        \Amasty\ExportCore\Export\DataHandling\FieldModifier\Number\Modulo::class,
        \Amasty\ExportCore\Export\DataHandling\FieldModifier\Number\Truncate::class,
        \Amasty\ExportCore\Export\DataHandling\FieldModifier\Number\Ceil::class,
        \Amasty\ExportCore\Export\DataHandling\FieldModifier\Number\Floor::class,

        // date
        \Amasty\ExportCore\Export\DataHandling\FieldModifier\Date::class
    ];

    /**
     * @var ConfigClassInterfaceFactory
     */
    private $configClassFactory;

    /**
     * @var Factory
     */
    private $factory;

    public function __construct(
        ConfigClassInterfaceFactory $configClassFactory,
        Factory $factory
    ) {
        $this->configClassFactory = $configClassFactory;
        $this->factory = $factory;
    }

    public function getAllModifiers(EntityConfigInterface $entityConfig): array
    {
        $modifiers = $this->defaultModifiers;

        foreach ($entityConfig->getFieldsConfig()->getFields() as $fieldConfig) {
            if ($fieldConfig->getActions()) {
                foreach ($fieldConfig->getActions() as $action) {
                    if ($action->getConfigClass()) {
                        $modifiers[] = $action->getConfigClass()->getName();
                    }
                }
            }
        }

        return $modifiers;
    }

    public function getAllModifiersByGroups(EntityConfigInterface $entityConfig, string $fieldName): array
    {
        return array_merge(
            $this->getDefaultModifiersByGroups(),
            [$this->getEntityModifiersByGroups($entityConfig, $fieldName)]
        );
    }

    public function getDefaultModifiersByGroups(): array
    {
        $defaultModifiers = [];
        foreach ($this->defaultModifiers as $modifier) {
            $modifierObject = $this->getModifierObject($modifier);
            $modifierGroup = $modifierObject->getGroup();
            if (!isset($defaultModifiers[$modifierGroup]['value'])) {
                $defaultModifiers[$modifierGroup] = [
                    'label' => $this->getGroupLabel($modifierGroup),
                    'type'  => $modifierGroup,
                    'value' => []
                ];
            }
            $valueLabelArray = [
                'label' => $modifierObject->getLabel(),
                'value' => $modifier
            ];
            array_push($defaultModifiers[$modifierGroup]['value'], $valueLabelArray);
        }

        return array_values($defaultModifiers);
    }

    private function getEntityModifiersByGroups(EntityConfigInterface $entityConfig, string $fieldName): array
    {
        $entityModifiers = $this->getEntityFieldModifiers($entityConfig, $fieldName);

        return [
            'label' => __('Custom Modifiers')->getText(),
            'type'  => self::CUSTOM_GROUP,
            'value' => array_values(array_unique($entityModifiers, SORT_REGULAR))
        ];
    }

    public function getEntityFieldModifiers(EntityConfigInterface $entityConfig, string $fieldName): array
    {
        $entityFieldModifiers = [];
        foreach ($entityConfig->getFieldsConfig()->getFields() as $fieldConfig) {
            if ($fieldConfig->getName() === $fieldName && !empty($fieldConfig->getActions())) {
                foreach ($fieldConfig->getActions() as $action) {
                    if (!$action->getConfigClass()) {
                        continue;
                    }

                    $modifierObject = $this->getModifierObject(
                        $action->getConfigClass()->getName(),
                        $action->getConfigClass()->getArguments()
                    );
                    $entityFieldModifiers[] = [
                        'label' => $modifierObject->getLabel(),
                        'value' => $action->getConfigClass()->getName(),
                        'eavEntityType' => $this->findArgumentByName(
                            $action->getConfigClass()->getArguments(),
                            ActionConfigBuilder::EAV_ENTITY_TYPE_CODE
                        ),
                        'optionSource' => $this->findArgumentByName(
                            $action->getConfigClass()->getArguments(),
                            ActionConfigBuilder::OPTION_SOURCE
                        )
                    ];
                }
            }
        }

        return $entityFieldModifiers;
    }

    public function getEntityFieldModifiersValue(EntityConfigInterface $entityConfig, string $fieldName): array
    {
        $entityFieldModifiers = [];
        foreach ($entityConfig->getFieldsConfig()->getFields() as $fieldConfig) {
            if ($fieldConfig->getName() === $fieldName && !empty($fieldConfig->getActions())) {
                foreach ($fieldConfig->getActions() as $action) {
                    if (!$action->getConfigClass()
                        || !$this->isPreselected($action->getConfigClass()->getArguments())
                    ) {
                        continue;
                    }

                    $entityFieldModifiers[] = [
                        'select_value'    => $action->getConfigClass()->getName(),
                        'eavEntityType' => $this->findArgumentByName(
                            $action->getConfigClass()->getArguments(),
                            ActionConfigBuilder::EAV_ENTITY_TYPE_CODE
                        ),
                        'optionSource' => $this->findArgumentByName(
                            $action->getConfigClass()->getArguments(),
                            ActionConfigBuilder::OPTION_SOURCE
                        )
                    ];
                }
            }
        }

        return $entityFieldModifiers;
    }

    private function getModifierObject(string $modifierClass, array $arguments = [])
    {
        $class = $this->configClassFactory->create([
            'baseType'  => FieldModifierInterface::class,
            'name'      => $modifierClass,
            'arguments' => $arguments
        ]);

        return $this->factory->createObject($class);
    }

    private function isPreselected(array $arguments): bool
    {
        return (bool)$this->findArgumentByName($arguments, ActionConfigBuilder::IS_PRESELECTED);
    }

    private function findArgumentByName(array $arguments, string $name)
    {
        foreach ($arguments as $argument) {
            if ($argument->getName() == $name) {
                return $argument->getValue();
            }
        }

        return '';
    }

    public function getGroupLabel(string $group): string
    {
        $groupLabels = [
            self::TEXT_GROUP => __('Text Modifiers')->getText(),
            self::NUMERIC_GROUP => __('Numeric Modifiers')->getText(),
            self::DATE_GROUP => __('Date Modifiers')->getText(),
            self::CUSTOM_GROUP => __('Custom Modifiers')->getText()
        ];

        return $groupLabels[$group] ?? __('Custom Modifiers')->getText();
    }
}
