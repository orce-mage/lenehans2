<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Config\EntitySource;

use Amasty\ExportCore\Api\CollectionModifierInterface;
use Amasty\ExportCore\Export\Config\EntityConfigFactory;
use Amasty\ExportCore\SchemaReader\Config;
use Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterfaceFactory;
use Amasty\ImportExportCore\Config\ConfigClass\Factory as ObjectFactory;
use Amasty\ImportExportCore\Config\Xml\ArgumentsPrepare;

class Xml implements EntitySourceInterface
{
    /**
     * @var Config
     */
    private $entitiesConfigCache;

    /**
     * @var EntityConfigFactory
     */
    private $entityConfigFactory;

    /**
     * @var Xml\FieldsConfigPrepare
     */
    private $fieldsConfigPrepare;

    /**
     * @var ConfigClassInterfaceFactory
     */
    private $configClassFactory;

    /**
     * @var ArgumentsPrepare
     */
    private $argumentsPrepare;

    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    public function __construct(
        Config $entitiesConfigCache,
        EntityConfigFactory $entityConfigFactory,
        ConfigClassInterfaceFactory $configClassFactory,
        ObjectFactory $objectFactory,
        ArgumentsPrepare $argumentsPrepare,
        Xml\FieldsConfigPrepare $fieldsConfigPrepare
    ) {
        $this->entitiesConfigCache = $entitiesConfigCache;
        $this->entityConfigFactory = $entityConfigFactory;
        $this->fieldsConfigPrepare = $fieldsConfigPrepare;
        $this->configClassFactory = $configClassFactory;
        $this->argumentsPrepare = $argumentsPrepare;
        $this->objectFactory = $objectFactory;
    }

    public function get()
    {
        $result = [];
        foreach ($this->entitiesConfigCache->get() as $entityCode => $entityConfig) {
            if (!empty($entityConfig['enabledChecker'])) {
                $enabledChecker = $this->configClassFactory->create([
                    'name'      => $entityConfig['enabledChecker']['class'],
                    'arguments' => $this->argumentsPrepare->execute($entityConfig['enabledChecker']['arguments'] ?? [])
                ]);
                if (!$this->objectFactory->createObject($enabledChecker)->isEnabled()) {
                    continue;
                }
            }

            $entity = $this->entityConfigFactory->create();
            $entity->setEntityCode($entityCode);
            $entity->setName($entityConfig['name']);
            $entity->setGroup($entityConfig['group'] ?? null);
            $entity->setDescription($entityConfig['description'] ?? null);
            $entity->setHiddenInLists(!empty($entityConfig['isHidden']));
            $collectionFactory = $this->configClassFactory->create([
                'name'      => $entityConfig['collectionFactory']['class'],
                'arguments' => $this->argumentsPrepare->execute(
                    $entityConfig['collectionFactory']['arguments'] ?? []
                )
            ]);
            $entity->setCollectionFactory($collectionFactory);
            if (!empty($entityConfig['collectionModifier'])) {
                $collectionModifier = $this->configClassFactory->create([
                    'baseType'  => CollectionModifierInterface::class,
                    'name'      => $entityConfig['collectionModifier']['class'],
                    'arguments' => $this->argumentsPrepare->execute(
                        $entityConfig['collectionModifier']['arguments'] ?? []
                    )
                ]);
                $entity->setCollectionModifier($collectionModifier);
            }
            $entity->setFieldsConfigResolveClosure(
                function () use ($entityConfig, $entity) {
                    return $this->fieldsConfigPrepare->execute($entityConfig['fieldsConfig'], $entity);
                }
            );

            $result[] =  $entity;
        }

        return $result;
    }
}
