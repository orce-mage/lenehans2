<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Api\Config;

use Amasty\ExportCore\Api\Config\Profile\FieldsConfigInterface;

interface ProfileConfigInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return string
     */
    public function getStrategy(): ?string;

    /**
     * @param string $strategy
     *
     * @return \Amasty\ExportCore\Api\Config\ProfileConfigInterface
     */
    public function setStrategy(?string $strategy): ProfileConfigInterface;

    /**
     * @return string
     */
    public function getEntityCode(): string;

    /**
     * @param string $entityCode
     *
     * @return \Amasty\ExportCore\Api\Config\ProfileConfigInterface
     */
    public function setEntityCode(string $entityCode): ProfileConfigInterface;

    /**
     * @return string
     */
    public function getFilename(): ?string;

    /**
     * @param string $filename
     *
     * @return \Amasty\ExportCore\Api\Config\ProfileConfigInterface
     */
    public function setFilename(?string $filename): ProfileConfigInterface;

    /**
     * @return bool
     */
    public function isUseMultiProcess(): ?bool;

    /**
     * @param $isUseMultiProcess
     *
     * @return \Amasty\ExportCore\Api\Config\ProfileConfigInterface
     */
    public function setIsUseMultiProcess(?bool $isUseMultiProcess): ProfileConfigInterface;

    /**
     * @return int
     */
    public function getMaxJobs(): ?int;

    /**
     * @param int $maxJobs
     *
     * @return \Amasty\ExportCore\Api\Config\ProfileConfigInterface
     */
    public function setMaxJobs(?int $maxJobs): ProfileConfigInterface;

    /**
     * @return int
     */
    public function getBatchSize(): ?int;

    /**
     * @param int $batchSize
     *
     * @return \Amasty\ExportCore\Api\Config\ProfileConfigInterface
     */
    public function setBatchSize(?int $batchSize): ProfileConfigInterface;

    /**
     * @return string
     */
    public function getTemplateType(): ?string;

    /**
     * @param string $templateType
     *
     * @return \Amasty\ExportCore\Api\Config\ProfileConfigInterface
     */
    public function setTemplateType(?string $templateType): ProfileConfigInterface;

    /**
     * @return string[]
     */
    public function getFileDestinationTypes(): ?array;

    /**
     * @param string[] $fileDestinationTypes
     *
     * @return \Amasty\ExportCore\Api\Config\ProfileConfigInterface
     */
    public function setFileDestinationTypes(array $fileDestinationTypes): ProfileConfigInterface;

    /**
     * @return string[]|null
     */
    public function getPostProcessors(): ?array;

    /**
     * @param string[] $postProcessors
     *
     * @return \Amasty\ExportCore\Api\Config\ProfileConfigInterface
     */
    public function setPostProcessors(array $postProcessors): ProfileConfigInterface;

    /**
     * @return \Amasty\ExportCore\Api\Config\Profile\FieldsConfigInterface
     */
    public function getFieldsConfig(): ?FieldsConfigInterface;

    /**
     * @param \Amasty\ExportCore\Api\Config\Profile\FieldsConfigInterface $fieldsConfig
     *
     * @return \Amasty\ExportCore\Api\Config\ProfileConfigInterface
     */
    public function setFieldsConfig(?FieldsConfigInterface $fieldsConfig): ProfileConfigInterface;

    /**
     * @return string|null
     */
    public function getModuleType(): ?string;

    /**
     * @param string|null $moduleType
     *
     * @return \Amasty\ExportCore\Api\Config\ProfileConfigInterface
     */
    public function setModuleType(?string $moduleType): ProfileConfigInterface;

    /**
     * Extension point for customizations to set extension attributes of ProfileConfig class
     *
     * @return \Amasty\ExportCore\Api\Config\ProfileConfigInterface
     */
    public function initialize(): ProfileConfigInterface;

    /**
     * @return \Amasty\ExportCore\Api\Config\ProfileConfigExtensionInterface
     */
    public function getExtensionAttributes(): \Amasty\ExportCore\Api\Config\ProfileConfigExtensionInterface;

    /**
     * @param \Amasty\ExportCore\Api\Config\ProfileConfigExtensionInterface $extensionAttributes
     *
     * @return \Amasty\ExportCore\Api\Config\ProfileConfigInterface
     */
    public function setExtensionAttributes(
        \Amasty\ExportCore\Api\Config\ProfileConfigExtensionInterface $extensionAttributes
    ): ProfileConfigInterface;
}
