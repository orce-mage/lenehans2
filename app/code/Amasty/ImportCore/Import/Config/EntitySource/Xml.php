<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Import\Config\EntitySource;

use Amasty\ImportCore\Api\BehaviorInterface;
use Amasty\ImportCore\Api\Config\Entity\BehaviorInterfaceFactory;
use Amasty\ImportCore\Api\Config\Entity\FileUploaderConfigInterfaceFactory;
use Amasty\ImportCore\Api\Config\Entity\IndexerConfigInterfaceFactory;
use Amasty\ImportCore\Import\Config\EntityConfigFactory;
use Amasty\ImportCore\SchemaReader\Config;
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
     * @var BehaviorInterfaceFactory
     */
    private $behaviorFactory;

    /**
     * @var ConfigClassInterfaceFactory
     */
    private $configClassFactory;

    /**
     * @var Xml\FieldsConfigPrepare
     */
    private $fieldsConfigPrepare;

    /**
     * @var ArgumentsPrepare
     */
    private $argumentsPrepare;

    /**
     * @var IndexerConfigInterfaceFactory
     */
    private $indexerConfigFactory;

    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    /**
     * @var FileUploaderConfigInterfaceFactory
     */
    private $fileUploaderConfigFactory;

    public function __construct(
        Config $entitiesConfigCache,
        EntityConfigFactory $entityConfigFactory,
        ConfigClassInterfaceFactory $configClassFactory,
        BehaviorInterfaceFactory $behaviorFactory,
        ArgumentsPrepare $argumentsPrepare,
        Xml\FieldsConfigPrepare $fieldsConfigPrepare,
        IndexerConfigInterfaceFactory $indexerConfigFactory,
        ObjectFactory $objectFactory,
        FileUploaderConfigInterfaceFactory $fileUploaderConfigFactory
    ) {
        $this->entitiesConfigCache = $entitiesConfigCache;
        $this->entityConfigFactory = $entityConfigFactory;
        $this->behaviorFactory = $behaviorFactory;
        $this->configClassFactory = $configClassFactory;
        $this->fieldsConfigPrepare = $fieldsConfigPrepare;
        $this->argumentsPrepare = $argumentsPrepare;
        $this->indexerConfigFactory = $indexerConfigFactory;
        $this->objectFactory = $objectFactory;
        $this->fileUploaderConfigFactory = $fileUploaderConfigFactory;
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

            $indexerMethods = [];
            $entity = $this->entityConfigFactory->create();
            $entity->setEntityCode($entityCode);
            $entity->setName($entityConfig['name']);
            $entity->setGroup($entityConfig['group'] ?? null);
            $entity->setDescription($entityConfig['description'] ?? null);
            $entity->setHiddenInLists(!empty($entityConfig['isHidden']));
            if (!empty($entityConfig['behaviors'])) {
                $behaviors = [];
                foreach ($entityConfig['behaviors'] as $code => $behaviorConfig) {
                    $behavior = $this->behaviorFactory->create();
                    $behavior->setName($behaviorConfig['name']);
                    $behavior->setCode($code);
                    if ($indexerMethod = $behaviorConfig['indexerMethod'] ?? null) {
                        $indexerMethods[$code] = $indexerMethod;
                    }

                    if (isset($behaviorConfig['executeOnCodes'])) {
                        $behavior->setExecuteOnCodes($behaviorConfig['executeOnCodes']);
                    }

                    $class = $this->configClassFactory->create([
                        'baseType'  => BehaviorInterface::class,
                        'name'      => $behaviorConfig['class'],
                        'arguments' => $this->argumentsPrepare->execute($behaviorConfig['arguments'])
                    ]);
                    $behavior->setConfigClass($class);

                    $behaviors[] = $behavior;
                }
                $entity->setBehaviors($behaviors);
            }

            if (!empty($entityConfig['indexer'])) {
                $indexerConfig = $this->indexerConfigFactory->create();
                $indexerConfig->setData($entityConfig['indexer'])
                    ->setIndexerMethods($indexerMethods);
                $entity->setIndexerConfig($indexerConfig);
            }
            if (!empty($entityConfig['fileUploader'])) {
                $fileUploaderConfig = $this->fileUploaderConfigFactory->create();
                $fileUploaderConfig->setData($entityConfig['fileUploader']);
                $entity->setFileUploaderConfig($fileUploaderConfig);
            }

            $entity->setFieldsConfig($this->fieldsConfigPrepare->execute($entityConfig['fieldsConfig']));

            $result[] =  $entity;
        }

        return $result;
    }
}
