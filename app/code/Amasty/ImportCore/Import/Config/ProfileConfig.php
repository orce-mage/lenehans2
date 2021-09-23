<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Config;

use Amasty\ImportCore\Api\Config\Profile\EntitiesConfigInterface;
use Amasty\ImportCore\Api\Config\ProfileConfigExtensionInterfaceFactory;
use Amasty\ImportCore\Api\Config\ProfileConfigInterface;
use Magento\Framework\DataObject;

class ProfileConfig extends DataObject implements ProfileConfigInterface
{
    const STRATEGY = 'strategy';
    const ENTITIES_CONFIG = 'entities_config';
    const ENTITY_CODE = 'entity_code';
    const BEHAVIOR = 'behavior';
    const ENTITY_IDENTIFIER = 'entity_identifier';
    const USE_MULTIPROCESS = 'use_multiprocess';
    const MAX_JOBS = 'max_jobs';
    const FILE_RESOLVER_TYPE = 'file_resolver_type';
    const SOURCE_TYPE = 'source_type';
    const BATCH_SIZE = 'batch_size';
    const OVERFLOW_BATCH_SIZE = 'overflow_batch_size';
    const MODULE_TYPE = 'module_type';
    const VALIDATION_STRATEGY = 'validation_strategy';
    const ALLOW_ERRORS_COUNT = 'allow_errors_count';
    const IMAGES_FILE_DIRECTORY = 'images_file_directory';
    const EXTENSION_ATTRIBUTES = 'extension_attributes';
    /**
     * @var ProfileConfigExtensionInterfaceFactory
     */
    private $extensionAttributesFactory;

    public function __construct(
        ProfileConfigExtensionInterfaceFactory $extensionAttributesFactory,
        array $data = []
    ) {
        parent::__construct($data);
        $this->extensionAttributesFactory = $extensionAttributesFactory;
    }

    //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedFunction
    public function initialize()
    {
    }

    public function getStrategy()
    {
        return $this->getData(self::STRATEGY);
    }

    public function setStrategy($strategy)
    {
        return $this->setData(self::STRATEGY, $strategy);
    }

    public function getEntitiesConfig()
    {
        return $this->getData(self::ENTITIES_CONFIG);
    }

    public function setEntitiesConfig(EntitiesConfigInterface $entitiesConfig)
    {
        return $this->setData(self::ENTITIES_CONFIG, $entitiesConfig);
    }

    public function getEntityCode()
    {
        return $this->getData(self::ENTITY_CODE);
    }

    public function setEntityCode($entityCode)
    {
        return $this->setData(self::ENTITY_CODE, $entityCode);
    }

    public function getBehavior()
    {
        return $this->getData(self::BEHAVIOR);
    }

    public function setBehavior($behavior)
    {
        $this->setData(self::BEHAVIOR, $behavior);
    }

    public function setEntityIdentifier(string $identifier)
    {
        return $this->setData(self::ENTITY_IDENTIFIER, $identifier);
    }

    public function getEntityIdentifier(): ?string
    {
        return $this->getData(self::ENTITY_IDENTIFIER);
    }

    public function isUseMultiProcess()
    {
        return $this->getData(self::USE_MULTIPROCESS) ?? false;
    }

    public function setIsUseMultiProcess($isUseMultiProcess)
    {
        return $this->setData(self::USE_MULTIPROCESS, $isUseMultiProcess);
    }

    public function getMaxJobs()
    {
        return $this->getData(self::MAX_JOBS) ?? 1;
    }

    public function setMaxJobs($maxJobs)
    {
        return $this->setData(self::MAX_JOBS, $maxJobs);
    }

    public function getFileResolverType()
    {
        return $this->getData(self::FILE_RESOLVER_TYPE);
    }

    public function setFileResolverType($type)
    {
        return $this->setData(self::FILE_RESOLVER_TYPE, $type);
    }

    public function getSourceType()
    {
        return $this->getData(self::SOURCE_TYPE);
    }

    public function setSourceType($type)
    {
        return $this->setData(self::SOURCE_TYPE, $type);
    }

    public function getBatchSize()
    {
        return $this->getData(self::BATCH_SIZE);
    }

    public function setBatchSize($batchSize)
    {
        return $this->setData(self::BATCH_SIZE, $batchSize);
    }

    public function getOverflowBatchSize()
    {
        return $this->getData(self::OVERFLOW_BATCH_SIZE);
    }

    public function setOverflowBatchSize($batchSize)
    {
        return $this->setData(self::OVERFLOW_BATCH_SIZE, $batchSize);
    }

    public function getModuleType(): ?string
    {
        return $this->getData(self::MODULE_TYPE);
    }

    public function setModuleType(?string $moduleType)
    {
        return $this->setData(self::MODULE_TYPE, $moduleType);
    }

    public function getValidationStrategy(): ?string
    {
        return $this->getData(self::VALIDATION_STRATEGY);
    }

    public function setValidationStrategy(?string $validationStrategy)
    {
        return $this->setData(self::VALIDATION_STRATEGY, $validationStrategy);
    }

    public function getAllowErrorsCount(): ?string
    {
        return $this->getData(self::ALLOW_ERRORS_COUNT);
    }

    public function setAllowErrorsCount(?string $allowErrorsCount)
    {
        return $this->setData(self::ALLOW_ERRORS_COUNT, $allowErrorsCount);
    }

    public function getImagesFileDirectory(): ?string
    {
        return $this->getData(self::IMAGES_FILE_DIRECTORY);
    }

    public function setImagesFileDirectory(?string $imageFileDirectory)
    {
        return $this->setData(self::IMAGES_FILE_DIRECTORY, $imageFileDirectory);
    }

    public function getExtensionAttributes()
    {
        if (null === $this->getData(self::EXTENSION_ATTRIBUTES)) {
            $this->setExtensionAttributes($this->extensionAttributesFactory->create());
        }

        return $this->getData(self::EXTENSION_ATTRIBUTES);
    }

    public function setExtensionAttributes(
        \Amasty\ImportCore\Api\Config\ProfileConfigExtensionInterface $extensionAttributes
    ) {
        return $this->setData(self::EXTENSION_ATTRIBUTES, $extensionAttributes);
    }
}
