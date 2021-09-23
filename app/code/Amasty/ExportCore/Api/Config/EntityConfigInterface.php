<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Api\Config;

use Amasty\ExportCore\Api\Config\Entity\FieldsConfigInterface;
use Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface;

interface EntityConfigInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return string
     */
    public function getEntityCode(): ?string;

    /**
     * @param string $entityCode
     *
     * @return \Amasty\ExportCore\Api\Config\EntityConfigInterface
     */
    public function setEntityCode(?string $entityCode): EntityConfigInterface;

    /**
     * @return string
     */
    public function getName(): ?string;

    /**
     * @param string $name
     *
     * @return \Amasty\ExportCore\Api\Config\EntityConfigInterface
     */
    public function setName(?string $name): EntityConfigInterface;

    /**
     * @return string
     */
    public function getGroup(): ?string;

    /**
     * @param string $group
     *
     * @return \Amasty\ExportCore\Api\Config\EntityConfigInterface
     */
    public function setGroup(?string $group): EntityConfigInterface;

    /**
     * @return string
     */
    public function getDescription(): ?string;

    /**
     * @param string $description
     *
     * @return \Amasty\ExportCore\Api\Config\EntityConfigInterface
     */
    public function setDescription(?string $description): EntityConfigInterface;

    /**
     * @return \Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface
     */
    public function getCollectionFactory(): ?ConfigClassInterface;

    /**
     * @param \Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface $collectionFactory
     *
     * @return \Amasty\ExportCore\Api\Config\EntityConfigInterface
     */
    public function setCollectionFactory(?ConfigClassInterface $collectionFactory): EntityConfigInterface;

    /**
     * @return \Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface
     */
    public function getCollectionModifier(): ?ConfigClassInterface;

    /**
     * @param \Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface $collectionModifier
     *
     * @return \Amasty\ExportCore\Api\Config\EntityConfigInterface
     */
    public function setCollectionModifier(?ConfigClassInterface $collectionModifier): EntityConfigInterface;

    /**
     * @return bool
     */
    public function isHiddenInLists(): ?bool;

    /**
     * @param bool $isHiddenInLists
     *
     * @return \Amasty\ExportCore\Api\Config\EntityConfigInterface
     */
    public function setHiddenInLists(?bool $isHiddenInLists): EntityConfigInterface;

    /**
     * @return \Amasty\ExportCore\Api\Config\Entity\FieldsConfigInterface
     */
    public function getFieldsConfig(): ?FieldsConfigInterface;

    /**
     * @param \Amasty\ExportCore\Api\Config\Entity\FieldsConfigInterface $fieldsConfig
     *
     * @return \Amasty\ExportCore\Api\Config\EntityConfigInterface
     */
    public function setFieldsConfig(?FieldsConfigInterface $fieldsConfig): EntityConfigInterface;

    /**
     * @return \Amasty\ExportCore\Api\Config\EntityConfigExtensionInterface
     */
    public function getExtensionAttributes(): \Amasty\ExportCore\Api\Config\EntityConfigExtensionInterface;

    /**
     * @param \Amasty\ExportCore\Api\Config\EntityConfigExtensionInterface $extensionAttributes
     *
     * @return \Amasty\ExportCore\Api\Config\EntityConfigInterface
     */
    public function setExtensionAttributes(
        \Amasty\ExportCore\Api\Config\EntityConfigExtensionInterface $extensionAttributes
    ): EntityConfigInterface;
}
