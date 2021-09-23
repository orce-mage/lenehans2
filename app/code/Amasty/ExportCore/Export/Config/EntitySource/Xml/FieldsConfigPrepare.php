<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Config\EntitySource\Xml;

use Amasty\ExportCore\Api\CollectionModifierInterface;
use Amasty\ExportCore\Api\Config\Entity\Field\ActionInterfaceFactory;
use Amasty\ExportCore\Api\Config\Entity\Field\FieldInterfaceFactory;
use Amasty\ExportCore\Api\Config\Entity\Field\FilterInterfaceFactory;
use Amasty\ExportCore\Api\Config\Entity\Field\VirtualFieldInterfaceFactory;
use Amasty\ExportCore\Api\Config\Entity\FieldsConfigInterface;
use Amasty\ExportCore\Api\Config\Entity\FieldsConfigInterfaceFactory;
use Amasty\ExportCore\Api\FieldModifier\FieldModifierInterface;
use Amasty\ExportCore\Api\Filter\FilterConfigInterface;
use Amasty\ExportCore\Api\Filter\FilterInterface;
use Amasty\ExportCore\Api\Filter\FilterMetaInterface;
use Amasty\ExportCore\Api\VirtualField\GeneratorInterface;
use Amasty\ExportCore\Export\Config\EntityConfig;
use Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterfaceFactory;
use Amasty\ImportExportCore\Config\ConfigClass\Factory as ObjectFactory;
use Amasty\ImportExportCore\Config\Xml\ArgumentsPrepare;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;

class FieldsConfigPrepare
{
    /**
     * @var FieldsConfigInterfaceFactory
     */
    private $fieldsConfigFactory;

    /**
     * @var FieldInterfaceFactory
     */
    private $fieldFactory;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ActionInterfaceFactory
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
     * @var VirtualFieldInterfaceFactory
     */
    private $virtualFieldFactory;

    /**
     * @var FilterConfigInterface
     */
    private $filterConfig;

    /**
     * @var FilterInterfaceFactory
     */
    private $filterFactory;

    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    public function __construct(
        FieldsConfigInterfaceFactory $fieldsConfigFactory,
        FieldInterfaceFactory $fieldFactory,
        VirtualFieldInterfaceFactory $virtualFieldFactory,
        ActionInterfaceFactory $actionFactory,
        FilterConfigInterface $filterConfig,
        ConfigClassInterfaceFactory $configClassFactory,
        ArgumentsPrepare $argumentsPrepare,
        FilterInterfaceFactory $filterFactory,
        ObjectManagerInterface $objectManager,
        ObjectFactory $objectFactory
    ) {
        $this->fieldsConfigFactory = $fieldsConfigFactory;
        $this->fieldFactory = $fieldFactory;
        $this->objectManager = $objectManager;
        $this->actionFactory = $actionFactory;
        $this->configClassFactory = $configClassFactory;
        $this->argumentsPrepare = $argumentsPrepare;
        $this->virtualFieldFactory = $virtualFieldFactory;
        $this->filterConfig = $filterConfig;
        $this->filterFactory = $filterFactory;
        $this->objectFactory = $objectFactory;
    }

    public function execute(
        array $xmlFieldsConfig,
        EntityConfig $entityConfig
    ): FieldsConfigInterface {
        $fieldsConfig = $this->fieldsConfigFactory->create();
        if (!empty($xmlFieldsConfig['rowActionClass'])) {
            $fieldsConfig->setRowActionClass($xmlFieldsConfig['rowActionClass']);
        }
        $fieldsConfig->setFields($this->getFields($xmlFieldsConfig['fields'] ?? []));
        $fieldsConfig->setVirtualFields($this->getVirtualFields($xmlFieldsConfig['virtualFields'] ?? []));

        if ($fieldsConfigClassName = $xmlFieldsConfig['fieldsClass']['class'] ?? null) {
            $fieldsConfigClass = $this->configClassFactory->create(
                [
                    'name' => $fieldsConfigClassName,
                    'arguments' => $this->argumentsPrepare->execute($xmlFieldsConfig['fieldsClass']['arguments'] ?? [])
                ]
            );
            $fieldsConfig = $this->objectFactory->createObject($fieldsConfigClass)
                ->execute($fieldsConfig, $entityConfig);
            if (!is_subclass_of($fieldsConfig, FieldsConfigInterface::class)) {
                throw new LocalizedException(
                    __('Fields Class should return "%1" interface result', FieldsConfigInterface::class)
                );
            }

            return $fieldsConfig;
        }

        return $fieldsConfig;
    }

    public function getFields(array $fieldsConfig): array
    {
        $fields = [];
        foreach ($fieldsConfig as $name => $fieldConfig) {
            $field = $this->fieldFactory->create();
            $field->setName($name);
            if (!empty($fieldConfig['map'])) {
                $field->setMap($fieldConfig['map']);
            }
            if (!empty($fieldConfig['label'])) {
                $field->setLabel($fieldConfig['label']);
            }
            if (!empty($fieldConfig['actions'])) {
                $actions = [];
                foreach ($fieldConfig['actions'] as $actionConfig) {
                    $action = $this->actionFactory->create();
                    $class = $this->configClassFactory->create([
                        'baseType'  => FieldModifierInterface::class,
                        'name'      => $actionConfig['class'],
                        'arguments' => $this->argumentsPrepare->execute($actionConfig['arguments'] ?? [])
                    ]);

                    $action->setConfigClass($class);
                    $actions[] = $action;
                }
                if (!empty($actions)) {
                    $field->setActions($actions);
                }
            }
            if (!empty($fieldConfig['filter']['type'])) {
                $filterConfig = $this->filterConfig->get($fieldConfig['filter']['type']);

                $arguments = [];
                if ($filterConfig['code'] === \Amasty\ExportCore\Export\Filter\Type\Select\Filter::TYPE_ID) {
                    if (!empty($fieldConfig['filter']['type']['options'])) {
                        $arguments['options'] = $fieldConfig['filter']['type']['options'];
                    } elseif (!empty($fieldConfig['filter']['options']['class'])) {
                        $arguments['class'] = $fieldConfig['filter']['options']['class'];
                    }
                    $arguments = $this->argumentsPrepare->execute($arguments);
                }
                $filterClass = $this->configClassFactory->create([
                    'baseType'  => FilterInterface::class,
                    'name'      => $filterConfig['filterClass'],
                    'arguments' => []
                ]);
                $metaClass = $this->configClassFactory->create([
                    'baseType'  => FilterMetaInterface::class,
                    'name'      => $filterConfig['metaClass'],
                    'arguments' => $arguments
                ]);
                $filter = $this->filterFactory->create();
                $filter->setType($filterConfig['code']);

                $filter->setMetaClass($metaClass);
                $filter->setFilterClass($filterClass);

                $field->setFilter($filter);
            } elseif (!empty($fieldConfig['filterClass'])) {
                $filterClass = $this->configClassFactory->create([
                    'baseType'  => FilterInterface::class,
                    'name'      => $fieldConfig['filterClass']['class']['class'],
                    'arguments' => $this->argumentsPrepare->execute(
                        $fieldConfig['filterClass']['class']['arguments'] ?? []
                    )
                ]);
                $metaClass = $this->configClassFactory->create([
                    'baseType'  => FilterMetaInterface::class,
                    'name'      => $fieldConfig['filterClass']['metaClass']['class'],
                    'arguments' => $this->argumentsPrepare->execute(
                        $fieldConfig['filterClass']['metaClass']['arguments'] ?? []
                    )
                ]);

                $filter = $this->filterFactory->create();
                $filter->setType($fieldConfig['filterClass']['type']);
                $filter->setMetaClass($metaClass);
                $filter->setFilterClass($filterClass);

                $field->setFilter($filter);
            }
            if (!empty($fieldConfig['remove'])) {
                $field->setRemove($fieldConfig['remove']);
            }

            $fields[] = $field;
        }

        return $fields;
    }

    public function getVirtualFields(array $virtualFieldsConfig): array
    {
        $virtualFields = [];
        foreach ($virtualFieldsConfig as $name => $virtualFieldConfig) {
            $virtualField = $this->virtualFieldFactory->create();
            $virtualField->setName($name);
            if (!empty($virtualFieldConfig['label'])) {
                $virtualField->setLabel($virtualFieldConfig['label']);
            }
            if (!empty($virtualFieldConfig['generator'])) {
                $class = $this->configClassFactory->create([
                    'baseType'  => GeneratorInterface::class,
                    'name'      => $virtualFieldConfig['generator']['class'],
                    'arguments' => $this->argumentsPrepare->execute($virtualFieldConfig['generator']['arguments']) ?? []
                ]);
                $virtualField->setGenerator($class);
            }
            if (!empty($virtualFieldConfig['collectionModifier'])) {
                $class = $this->configClassFactory->create([
                    'baseType'  => CollectionModifierInterface::class,
                    'name'      => $virtualFieldConfig['collectionModifier']['class'],
                    'arguments' => $this->argumentsPrepare->execute(
                        $virtualFieldConfig['collectionModifier']['arguments'] ?? []
                    )
                ]);
                $virtualField->setCollectionModifier($class);
            }

            $virtualFields[] = $virtualField;
        }

        return $virtualFields;
    }
}
