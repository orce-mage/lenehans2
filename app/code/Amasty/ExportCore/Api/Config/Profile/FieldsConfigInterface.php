<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Api\Config\Profile;

interface FieldsConfigInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * @param string $name
     *
     * @return \Amasty\ExportCore\Api\Config\Profile\FieldsConfigInterface
     */
    public function setName(string $name): FieldsConfigInterface;

    /**
     * @return string|null
     */
    public function getMap(): ?string;

    /**
     * @param string $map
     *
     * @return \Amasty\ExportCore\Api\Config\Profile\FieldsConfigInterface
     */
    public function setMap(string $map): FieldsConfigInterface;

    /**
     * @return \Amasty\ExportCore\Api\Config\Profile\FieldInterface[]
     */
    public function getFields(): ?array;

    /**
     * @param \Amasty\ExportCore\Api\Config\Profile\FieldInterface[] $fields
     *
     * @return \Amasty\ExportCore\Api\Config\Profile\FieldsConfigInterface
     */
    public function setFields(?array $fields): FieldsConfigInterface;

    /**
     * @return \Amasty\ExportCore\Api\Config\Profile\FieldFilterInterface[]|null
     */
    public function getFilters(): ?array;

    /**
     * @param \Amasty\ExportCore\Api\Config\Profile\FieldFilterInterface[]|null $filters
     *
     * @return \Amasty\ExportCore\Api\Config\Profile\FieldsConfigInterface
     */
    public function setFilters(?array $filters): FieldsConfigInterface;

    /**
     * @return bool|null
     */
    public function isExcludeRowIfNoResultsFound(): ?bool;

    /**
     * @param bool|null $isExcludeRowIfNoResultsFound
     *
     * @return \Amasty\ExportCore\Api\Config\Profile\FieldsConfigInterface
     */
    public function setIsExcludeRowIfNoResultsFound(?bool $isExcludeRowIfNoResultsFound): FieldsConfigInterface;

    /**
     * @return \Amasty\ExportCore\Api\Config\Profile\FieldsConfigInterface[]
     */
    public function getSubEntitiesFieldsConfig(): ?array;

    /**
     * @param \Amasty\ExportCore\Api\Config\Profile\FieldsConfigInterface[] $subentitesFieldsConfig
     *
     * @return \Amasty\ExportCore\Api\Config\Profile\FieldsConfigInterface
     */
    public function setSubEntitiesFieldsConfig(?array $subentitesFieldsConfig): FieldsConfigInterface;

    /**
     * @return \Amasty\ExportCore\Api\Config\Profile\FieldsConfigExtensionInterface
     */
    public function getExtensionAttributes(): \Amasty\ExportCore\Api\Config\Profile\FieldsConfigExtensionInterface;

    /**
     * @param \Amasty\ExportCore\Api\Config\Profile\FieldsConfigExtensionInterface $extensionAttributes
     *
     * @return \Amasty\ExportCore\Api\Config\Profile\FieldsConfigInterface
     */
    public function setExtensionAttributes(
        \Amasty\ExportCore\Api\Config\Profile\FieldsConfigExtensionInterface $extensionAttributes
    ): FieldsConfigInterface;
}
