<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Import\DataHandling;

use Amasty\ImportCore\Api\Config\Entity\Field\ActionInterface;
use Amasty\ImportCore\Api\Config\Entity\Field\ActionInterfaceFactory;
use Amasty\ImportCore\Api\Modifier\FieldModifierInterface;
use Amasty\ImportCore\Import\DataHandling\FieldModifier\EmptyToNull;
use Amasty\ImportCore\Import\DataHandling\FieldModifier\Map;
use Amasty\ImportCore\Import\DataHandling\FieldModifier\Str2Float;
use Amasty\ImportCore\Import\Utils\Config\ArgumentConverter;
use Amasty\ImportExportCore\Api\Config\ConfigClass\ArgumentInterface;
use Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterfaceFactory;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Ui\Component\Form\Element\MultiSelect;

class FieldModifierResolver
{
    /**
     * @var ConfigClassInterfaceFactory
     */
    private $configClassFactory;

    /**
     * @var ActionInterfaceFactory
     */
    private $actionFactory;

    /**
     * @var ArgumentConverter
     */
    private $argumentsConverter;

    /**
     * @var array
     */
    private $attributeOptMapArgs = [];

    public function __construct(
        ConfigClassInterfaceFactory $configClassFactory,
        ActionInterfaceFactory $actionFactory,
        ArgumentConverter $argumentsConverter
    ) {
        $this->configClassFactory = $configClassFactory;
        $this->actionFactory = $actionFactory;
        $this->argumentsConverter = $argumentsConverter;
    }

    /**
     * Resolve fields actions using DB table column description (retrieved by DESCRIBE mysql command)
     *
     * @param array $fieldDetails
     * @param array $existingActions
     * @return ActionInterface[]
     */
    public function resolveByDbColumnInfo(array $fieldDetails, array $existingActions): array
    {
        $modifierConfigs = $this->getModifierConfigsByDbColInfo($fieldDetails);
        $modifierActions = [];

        foreach ($modifierConfigs as $modifierConfig) {
            $action = $this->actionFactory->create();
            $action->setConfigClass($modifierConfig)
                ->setGroup('beforeValidate');

            $modifierActions[] = $action;
        }

        return $this->mergeActions($modifierActions, $existingActions);
    }

    private function getModifierConfigsByDbColInfo(array $fieldDetails): array
    {
        $modifiers = [];
        switch ($fieldDetails['DATA_TYPE']) {
            case 'int':
                $modifiers[] = $this->createModifierConfig(EmptyToNull::class);
                break;
            case 'decimal':
                $modifiers[] = $this->createModifierConfig(EmptyToNull::class);
                $modifiers[] = $this->createModifierConfig(Str2Float::class);
                break;
            case 'timestamp':
                if ($fieldDetails['NULLABLE'] || !empty($fieldDetails['DEFAULT'])) {
                    $modifiers[] = $this->createModifierConfig(EmptyToNull::class);
                }
                break;
        }

        return $modifiers;
    }

    /**
     * Resolve fields actions for eav attribute
     *
     * @param AttributeInterface $attribute
     * @param array $existingActions
     * @return ActionInterface[]
     */
    public function resolveByEavAttribute(AttributeInterface $attribute, array $existingActions): array
    {
        $modifierActions = [];
        $modifierConfigs = $this->getModifierConfigsByEavAttr($attribute);
        foreach ($modifierConfigs as $modifierConfig) {
            $action = $this->actionFactory->create();
            $action->setConfigClass($modifierConfig)
                ->setGroup('beforeValidate');

            $modifierActions[] = $action;
        }

        return $this->mergeActions($modifierActions, $existingActions);
    }

    private function getModifierConfigsByEavAttr(AttributeInterface $attribute): array
    {
        $modifiers = [];
        /** @var AbstractAttribute $attribute */
        if ($attribute->isAllowedEmptyTextValue(AbstractAttribute::EMPTY_STRING)) {
            $modifiers[] = $this->createModifierConfig(EmptyToNull::class);
        }
        if ($attribute->getBackendType() == 'decimal') {
            $modifiers[] = $this->createModifierConfig(Str2Float::class);
        }

        if ($attribute->usesSource()) {
            $modifiers[] = $this->createModifierConfig(
                Map::class,
                $this->getAttributeOptionsMapArguments($attribute)
            );
        }

        return $modifiers;
    }

    /**
     * Get attribute options config arguments
     *
     * @param AttributeInterface $attribute
     * @return ArgumentInterface[]
     */
    private function getAttributeOptionsMapArguments($attribute)
    {
        $attributeCode = $attribute->getAttributeCode();
        if (!isset($this->attributeOptMapArgs[$attributeCode])) {
            $options = $attribute->getSource()
                ->getAllOptions();

            $map = [];
            foreach ($options as $index => $option) {
                $value = $option['value'] ?? '';
                if (is_string($value) && empty($value)) {
                    continue;
                }

                if (!is_array($value)) {
                    $map[(string)$option['label']] = $value;
                }
            }
            $this->attributeOptMapArgs[$attributeCode] = $this->argumentsConverter->toArguments(
                [
                    Map::MAP => $map,
                    Map::IS_MULTIPLE => $attribute->getFrontendInput() === MultiSelect::NAME
                ]
            );
        }

        return $this->attributeOptMapArgs[$attributeCode];
    }

    private function createModifierConfig($className, array $arguments = [])
    {
        return $this->configClassFactory->create([
            'baseType' => FieldModifierInterface::class,
            'name' => $className,
            'arguments' => $arguments
        ]);
    }

    private function mergeActions(array $modifierActions, array $existingActions): array
    {
        $existingActionNames = $this->getActionNames($existingActions);

        /** @var \Amasty\ImportCore\Api\Config\Entity\Field\ActionInterface $newAction */
        foreach ($modifierActions as $newAction) {
            if (in_array($newAction->getConfigClass()->getName(), $existingActionNames)) {
                continue;
            }
            $existingActions[] = $newAction;
        }

        return $existingActions;
    }

    private function getActionNames(array $actions): array
    {
        $names = [];
        foreach ($actions as $action) {
            $names[] = $action->getConfigClass()->getName();
        }

        return $names;
    }
}
