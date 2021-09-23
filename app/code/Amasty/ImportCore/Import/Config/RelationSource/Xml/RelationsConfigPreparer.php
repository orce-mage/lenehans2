<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Import\Config\RelationSource\Xml;

use Amasty\ImportCore\Api\Config\Entity\Field\Configuration\PreselectedInterface;
use Amasty\ImportCore\Api\Config\Entity\Field\Configuration\PreselectedInterfaceFactory;
use Amasty\ImportCore\Api\Config\Relation\RelationActionInterface;
use Amasty\ImportCore\Api\Config\Relation\RelationActionInterfaceFactory;
use Amasty\ImportCore\Api\Config\Relation\RelationConfigInterface;
use Amasty\ImportCore\Api\Config\Relation\RelationConfigInterfaceFactory;
use Amasty\ImportCore\Api\Config\Relation\RelationValidationInterface;
use Amasty\ImportCore\Api\Config\Relation\RelationValidationInterfaceFactory;
use Amasty\ImportCore\Api\Modifier\RelationModifierInterface;
use Amasty\ImportCore\Api\Validation\RelationValidatorInterface;
use Amasty\ImportCore\Import\Utils\MetadataSearcher;
use Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterfaceFactory;
use Amasty\ImportExportCore\Config\Xml\ArgumentsPrepare;
use Magento\Framework\Data\Argument\InterpreterInterface;

class RelationsConfigPreparer
{
    const PARENT_FIELD_NAME = 'parent_field_name';
    const CHILD_FIELD_NAME = 'child_field_name';
    const PARENT_ENTITY_NAME = 'parent_entity_name';
    const CHILD_ENTITY_NAME = 'child_entity_name';

    /**
     * @var RelationConfigInterfaceFactory
     */
    private $relationConfigFactory;

    /**
     * @var RelationValidationInterfaceFactory
     */
    private $validationFactory;

    /**
     * @var RelationActionInterfaceFactory
     */
    private $actionFactory;

    /**
     * @var ConfigClassInterfaceFactory
     */
    private $configClassFactory;

    /**
     * @var ArgumentsPrepare
     */
    private $argumentsPrepare;

    /**
     * @var InterpreterInterface
     */
    private $argumentInterpreter;

    /**
     * @var PreselectedInterfaceFactory
     */
    private $preselectedFactory;

    /**
     * @var MetadataSearcher
     */
    private $metadataSearcher;

    public function __construct(
        RelationConfigInterfaceFactory $relationConfigFactory,
        RelationValidationInterfaceFactory $validationFactory,
        RelationActionInterfaceFactory $actionFactory,
        ConfigClassInterfaceFactory $configClassFactory,
        ArgumentsPrepare $argumentsPrepare,
        InterpreterInterface $argumentInterpreter,
        PreselectedInterfaceFactory $preselectedFactory,
        MetadataSearcher $metadataSearcher
    ) {
        $this->relationConfigFactory = $relationConfigFactory;
        $this->validationFactory = $validationFactory;
        $this->actionFactory = $actionFactory;
        $this->configClassFactory = $configClassFactory;
        $this->argumentsPrepare = $argumentsPrepare;
        $this->argumentInterpreter = $argumentInterpreter;
        $this->preselectedFactory = $preselectedFactory;
        $this->metadataSearcher = $metadataSearcher;
    }

    /**
     * @param array $xmlRelationsConfig
     * @return RelationConfigInterface[]
     */
    public function execute(array $xmlRelationsConfig): array
    {
        $relations = [];
        foreach ($xmlRelationsConfig as $relationConfigData) {
            if (!empty($relationConfigData['arguments'])) {
                $arguments = [];
                foreach ($relationConfigData['arguments'] as $key => $argumentData) {
                    $arguments[$key] = $this->argumentInterpreter->evaluate($argumentData);
                }
                $relationConfigData['arguments'] = $arguments;
                $this->parseArguments($relationConfigData);

                if (isset($relationConfigData['validation'])) {
                    $relationConfigData['validation'] = $this->getValidation(
                        $relationConfigData['validation']
                    );
                }
                if (isset($relationConfigData['action'])) {
                    $relationConfigData['action'] = $this->getAction(
                        $relationConfigData['action']
                    );
                }
                if (isset($relationConfigData['required'])) {
                    $relationConfigData['preselected'] = $this->getPreselected(
                        $relationConfigData['required']
                    );
                    unset($relationConfigData['required']);
                }
            }

            /** @var RelationConfigInterface $relationConfig */
            $relationConfig = $this->relationConfigFactory->create(['data' => $relationConfigData]);
            $relations[$relationConfig->getSubEntityFieldName()] = $relationConfig;
        }

        return $relations;
    }

    /**
     * @param array $relationConfigData
     */
    private function parseArguments(array &$relationConfigData): void
    {
        $arguments = &$relationConfigData['arguments'];
        if (!isset($arguments[self::PARENT_FIELD_NAME]) && !isset($arguments[self::PARENT_ENTITY_NAME])) {
            throw new \LogicException('Parent field or entity is not specified');
        }
        if (!isset($arguments[self::CHILD_FIELD_NAME]) && !isset($arguments[self::CHILD_ENTITY_NAME])) {
            throw new \LogicException('Child field or entity is not specified');
        }

        if (isset($arguments[self::PARENT_ENTITY_NAME])) {
            $metadata = $this->metadataSearcher->searchMetadata(
                $arguments[self::PARENT_ENTITY_NAME]
            );
            $relationConfigData['parent_field_name'] = $metadata->getLinkField();
            unset($arguments[self::PARENT_ENTITY_NAME]);
        } else {
            $relationConfigData['parent_field_name'] = $arguments[self::PARENT_FIELD_NAME];
            unset($arguments[self::PARENT_FIELD_NAME]);
        }

        if (isset($arguments[self::CHILD_ENTITY_NAME])) {
            $metadata = $this->metadataSearcher->searchMetadata(
                $arguments[self::CHILD_ENTITY_NAME]
            );
            $relationConfigData['child_field_name'] = $metadata->getLinkField();
            unset($arguments[self::CHILD_ENTITY_NAME]);
        } else {
            $relationConfigData['child_field_name'] = $arguments[self::CHILD_FIELD_NAME];
            unset($arguments[self::CHILD_FIELD_NAME]);
        }
    }

    /**
     * Get validation config instance
     *
     * @param array $validationConfig
     * @return RelationValidationInterface
     */
    private function getValidation(array $validationConfig): RelationValidationInterface
    {
        /** @var RelationValidationInterface $validation */
        $validation = $this->validationFactory->create();
        $class = $this->configClassFactory->create([
            'baseType' => RelationValidatorInterface::class,
            'name' => $validationConfig['class']
        ]);

        $validation->setConfigClass($class);

        if (isset($validationConfig['includeBehaviors'])) {
            $validation->setIncludeBehaviors($validationConfig['includeBehaviors']);
        } elseif (isset($validationConfig['excludeBehaviors'])) {
            $validation->setExcludeBehaviors($validationConfig['excludeBehaviors']);
        }

        return $validation;
    }

    /**
     * Get action config instance
     *
     * @param array $actionConfig
     * @return RelationActionInterface
     */
    private function getAction(array $actionConfig): RelationActionInterface
    {
        /** @var RelationActionInterface $action */
        $action = $this->actionFactory->create();
        $class = $this->configClassFactory->create([
            'baseType' => RelationModifierInterface::class,
            'name' => $actionConfig['class'],
            'arguments' => $this->argumentsPrepare->execute($actionConfig['arguments'] ?? [])
        ]);
        $action->setConfigClass($class);

        return $action;
    }

    /**
     * @param array $preselectedConfig
     * @return PreselectedInterface
     */
    private function getPreselected(array $preselectedConfig): PreselectedInterface
    {
        /** @var PreselectedInterface $preselected */
        $preselected = $this->preselectedFactory->create();
        $preselected->setIsRequired($preselectedConfig['isRequired']);

        if (isset($preselectedConfig['behaviors']['excludeBehaviors'])) {
            $preselected->setExcludeBehaviors($preselectedConfig['behaviors']['excludeBehaviors']);
        } elseif (isset($preselectedConfig['behaviors']['includeBehaviors'])) {
            $preselected->setIncludeBehaviors($preselectedConfig['behaviors']['includeBehaviors']);
        }

        return $preselected;
    }
}
