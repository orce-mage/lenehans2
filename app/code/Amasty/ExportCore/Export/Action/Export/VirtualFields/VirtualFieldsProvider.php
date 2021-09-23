<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\Action\Export\VirtualFields;

use Amasty\ExportCore\Api\Config\EntityConfigInterface;
use Amasty\ExportCore\Api\Config\Profile\FieldInterface;
use Amasty\ExportCore\Api\Config\Profile\FieldsConfigInterface;
use Amasty\ExportCore\Api\ExportProcessInterface;
use Amasty\ExportCore\Api\FieldModifier\FieldModifierInterface;
use Amasty\ExportCore\Api\VirtualField\GeneratorInterface;
use Amasty\ExportCore\Export\Config\EntityConfigProvider;
use Amasty\ExportCore\Export\Config\RelationConfigProvider;
use Amasty\ImportExportCore\Config\ConfigClass\Factory as ConfigClassFactory;

class VirtualFieldsProvider
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

    public function __construct(
        ConfigClassFactory $configClassFactory,
        EntityConfigProvider $entityConfigProvider,
        RelationConfigProvider $relationConfigProvider
    ) {
        $this->configClassFactory = $configClassFactory;
        $this->entityConfigProvider = $entityConfigProvider;
        $this->relationConfigProvider = $relationConfigProvider;
    }

    /**
     * @param ExportProcessInterface $exportProcess
     *
     * @return FieldModifierInterface[][]
     */
    public function prepareVirtualFields(ExportProcessInterface $exportProcess): array
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
                if ($field->getType() === FieldInterface::VIRTUAL_TYPE
                    && $generator = $this->getFieldGenerator($field, $entityConfig)
                ) {
                    $result[$field->getName()] = $generator;
                }
            }
        }

        if (!empty($fieldsConfig->getSubEntitiesFieldsConfig()) && !empty($relations)) {
            foreach ($relations as $relation) {
                foreach ($fieldsConfig->getSubEntitiesFieldsConfig() as $subEntityFieldsConfig) {
                    if ($relation->getSubEntityFieldName() == $subEntityFieldsConfig->getName()) {
                        $subEntityFieldsActions =  $this->processFields(
                            $this->entityConfigProvider->get($relation->getChildEntityCode()),
                            $subEntityFieldsConfig,
                            $relation->getRelations()
                        );
                        if (!empty($subEntityFieldsActions)) {
                            $result[self::SUBENTITIES_KEY][$subEntityFieldsConfig->getName()] = $subEntityFieldsActions;
                        }
                        break;
                    }
                }
            }
        }

        return $result;
    }

    protected function getFieldGenerator(
        FieldInterface $field,
        EntityConfigInterface $entityConfig
    ): ?GeneratorInterface {
        foreach ($entityConfig->getFieldsConfig()->getVirtualFields() as $fieldConfig) {
            if ($fieldConfig->getName() === $field->getName()) {
                return $this->configClassFactory->createObject(
                    $fieldConfig->getGenerator()
                );
            }
        }

        return null;
    }
}
