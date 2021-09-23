<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\Action\Export\DataHandling;

use Amasty\ExportCore\Api\Config\EntityConfigInterface;
use Amasty\ExportCore\Api\Config\Profile\FieldInterface;
use Amasty\ExportCore\Api\Config\Profile\FieldsConfigInterface;
use Amasty\ExportCore\Api\ExportProcessInterface;
use Amasty\ExportCore\Api\FieldModifier\FieldModifierInterface;
use Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterfaceFactory;
use Amasty\ExportCore\Export\Config\EntityConfigProvider;
use Amasty\ExportCore\Export\Config\RelationConfigProvider;
use Amasty\ImportExportCore\Config\ConfigClass\Factory as ConfigClassFactory;
use Magento\Framework\ObjectManagerInterface;

class DataHandlerProvider
{
    const SUBENTITIES_KEY = 'sub';

    /**
     * @var ConfigClassFactory
     */
    private $configClassFactory;

    /**
     * @var EntityConfigProvider
     */
    private $entityConfigProvider;

    /**
     * @var RelationConfigProvider
     */
    private $relationConfigProvider;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ConfigClassInterfaceFactory
     */
    private $configClassInterfaceFactory;

    public function __construct(
        ConfigClassFactory $configClassFactory,
        EntityConfigProvider $entityConfigProvider,
        RelationConfigProvider $relationConfigProvider,
        ObjectManagerInterface $objectManager,
        ConfigClassInterfaceFactory $configClassInterfaceFactory
    ) {
        $this->configClassFactory = $configClassFactory;
        $this->entityConfigProvider = $entityConfigProvider;
        $this->relationConfigProvider = $relationConfigProvider;
        $this->objectManager = $objectManager;
        $this->configClassInterfaceFactory = $configClassInterfaceFactory;
    }

    /**
     * @param ExportProcessInterface $exportProcess
     *
     * @return FieldModifierInterface[][]
     */
    public function prepareModifiers(ExportProcessInterface $exportProcess): array
    {
        $relations = $this->relationConfigProvider->get($exportProcess->getProfileConfig()->getEntityCode());
        return $this->processFields(
            $exportProcess->getEntityConfig(),
            $exportProcess->getProfileConfig()->getFieldsConfig(),
            $relations
        );
    }

    protected function processFields(
        EntityConfigInterface $entityConfig,
        FieldsConfigInterface $fieldsConfig,
        ?array $relations
    ): array {
        $result = [];
        if (!empty($fieldsConfig->getFields())) {
            foreach ($fieldsConfig->getFields() as $field) {
                if ($field->getType() === FieldInterface::FIELD_TYPE
                    && ($modifiers = $this->getFieldModifiers($field))
                ) {
                    $result[$field->getName()] = $modifiers;
                }
            }
        }

        if (!empty($fieldsConfig->getSubEntitiesFieldsConfig()) && !empty($relations)) {
            foreach ($relations as $relation) {
                foreach ($fieldsConfig->getSubEntitiesFieldsConfig() as $subEntityFieldsConfig) {
                    if ($relation->getSubEntityFieldName() == $subEntityFieldsConfig->getName()) {
                        $subEntityFieldsModifiers =  $this->processFields(
                            $this->entityConfigProvider->get($relation->getChildEntityCode()),
                            $subEntityFieldsConfig,
                            $relation->getRelations()
                        );
                        if (!empty($subEntityFieldsModifiers)) {
                            $result[self::SUBENTITIES_KEY][$subEntityFieldsConfig->getName()] =
                                $subEntityFieldsModifiers;
                        }
                        break;
                    }
                }
            }
        }

        return $result;
    }

    protected function getFieldModifiers(FieldInterface $field): array
    {
        $modifiers = [];
        foreach ($field->getModifiers() as $fieldModifier) {
            $configClass = $this->configClassInterfaceFactory->create(
                [
                    'name' => $fieldModifier->getModifierClass(),
                    'baseType' => FieldModifierInterface::class,
                    'arguments' => $fieldModifier->getArguments() ?? []
                ]
            );
            $modifier = $this->configClassFactory->createObject($configClass);
            $modifiers[] = $modifier;
        }

        return $modifiers;
    }
}
