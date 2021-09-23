<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Api\Config;

use Amasty\ImportCore\Api\Config\Profile\EntitiesConfigInterface;

interface ProfileConfigInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return string
     */
    public function getStrategy();

    /**
     * @param string $strategy
     * @return $this
     */
    public function setStrategy($strategy);

    /**
     * @return \Amasty\ImportCore\Api\Config\Profile\EntitiesConfigInterface
     */
    public function getEntitiesConfig();

    /**
     * Set entities config
     *
     * @param EntitiesConfigInterface $entitiesConfig
     * @return $this
     */
    public function setEntitiesConfig(EntitiesConfigInterface $entitiesConfig);

    /**
     * @return string
     */
    public function getEntityCode();

    /**
     * @param string $entityCode
     *
     * @return void
     */
    public function setEntityCode($entityCode);

    /**
     * @return string
     */
    public function getBehavior();

    /**
     *
     * @param $behavior
     * @return void
     */
    public function setBehavior($behavior);

    /**
     * @param string $identifier
     * @return $this
     */
    public function setEntityIdentifier(string $identifier);

    /**
     * @return string|null
     */
    public function getEntityIdentifier(): ?string;

    /**
     * @return bool
     */
    public function isUseMultiProcess();

    /**
     * @param $isUseMultiProcess
     * @return $this
     */
    public function setIsUseMultiProcess($isUseMultiProcess);

    /**
     * @return int
     */
    public function getMaxJobs();

    /**
     * @param int $maxJobs
     * @return $this
     */
    public function setMaxJobs($maxJobs);

    /**
     * @return string
     */
    public function getFileResolverType();

    /**
     * @param string $type
     * @return $this
     */
    public function setFileResolverType($type);

    /**
     * @return string
     */
    public function getSourceType();

    /**
     * @param string $type
     * @return $this
     */
    public function setSourceType($type);

    /**
     * @return int
     */
    public function getBatchSize();

    /**
     * @param int $batchSize
     * @return $this
     */
    public function setBatchSize($batchSize);

    /**
     * @return int
     */
    public function getOverflowBatchSize();

    /**
     * @param int $batchSize
     * @return $this
     */
    public function setOverflowBatchSize($batchSize);

    /**
     * @return string|null
     */
    public function getModuleType(): ?string;

    /**
     * @param string|null $moduleType
     * @return $this
     */
    public function setModuleType(?string $moduleType);

    /**
     * @return string
     */
    public function getValidationStrategy();

    /**
     * @param string|null $validationStrategy
     * @return $this
     */
    public function setValidationStrategy(?string $validationStrategy);

    /**
     * @return string
     */
    public function getAllowErrorsCount();

    /**
     * @param string|null $allowErrorsCount
     * @return $this
     */
    public function setAllowErrorsCount(?string $allowErrorsCount);

    /**
     * @return string
     */
    public function getImagesFileDirectory();

    /**
     * @param string|null $imageFileDirectory
     * @return $this
     */
    public function setImagesFileDirectory(?string $imageFileDirectory);

    /**
     * Extension point for customizations to set extension attributes of ProfileConfig class
     *
     * @return void
     */
    public function initialize();

    /**
     * @return \Amasty\ImportCore\Api\Config\ProfileConfigExtensionInterface
     */
    public function getExtensionAttributes();

    /**
     * @param \Amasty\ImportCore\Api\Config\ProfileConfigExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(
        \Amasty\ImportCore\Api\Config\ProfileConfigExtensionInterface $extensionAttributes
    );
}
