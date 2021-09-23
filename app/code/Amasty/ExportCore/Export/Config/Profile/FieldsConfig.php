<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Config\Profile;

use Amasty\ExportCore\Api\Config\Profile\FieldsConfigExtensionInterface;
use Amasty\ExportCore\Api\Config\Profile\FieldsConfigExtensionInterfaceFactory;
use Amasty\ExportCore\Api\Config\Profile\FieldsConfigInterface;
use Magento\Framework\DataObject;

class FieldsConfig extends DataObject implements \Amasty\ExportCore\Api\Config\Profile\FieldsConfigInterface
{
    const NAME = 'name';
    const MAP = 'map';
    const FIELDS = 'fields';
    const FILTERS = 'filters';
    const IS_EXCLUDE_ROW_IF_NO_RESULTS_FOUND = 'is_exclude_row_if_no_results_found';
    const SUBENTITIES_FIELDS_CONFIG = 'subentities_fields_config';

    /**
     * @var FieldsConfigExtensionInterfaceFactory
     */
    private $extensionFactory;

    public function __construct(
        FieldsConfigExtensionInterfaceFactory $extensionFactory,
        array $data = []
    ) {
        parent::__construct($data);
        $this->extensionFactory = $extensionFactory;
    }

    public function getName(): ?string
    {
        return $this->getData(self::NAME);
    }

    public function setName(string $name): FieldsConfigInterface
    {
        $this->setData(self::NAME, $name);

        return $this;
    }

    public function getMap(): ?string
    {
        return $this->getData(self::MAP);
    }

    public function setMap(string $map): FieldsConfigInterface
    {
        $this->setData(self::MAP, $map);

        return $this;
    }

    public function getFields(): ?array
    {
        return $this->getData(self::FIELDS);
    }

    public function setFields(?array $fields): FieldsConfigInterface
    {
        $this->setData(self::FIELDS, $fields);

        return $this;
    }

    public function getFilters(): ?array
    {
        return $this->getData(self::FILTERS);
    }

    public function setFilters(?array $filters): FieldsConfigInterface
    {
        $this->setData(self::FILTERS, $filters);

        return $this;
    }

    public function isExcludeRowIfNoResultsFound(): ?bool
    {
        return $this->getData(self::IS_EXCLUDE_ROW_IF_NO_RESULTS_FOUND);
    }

    public function setIsExcludeRowIfNoResultsFound(?bool $isExcludeRowIfNoResultsFound): FieldsConfigInterface
    {
        $this->setData(self::IS_EXCLUDE_ROW_IF_NO_RESULTS_FOUND, $isExcludeRowIfNoResultsFound);

        return $this;
    }

    public function getSubEntitiesFieldsConfig(): ?array
    {
        return $this->getData(self::SUBENTITIES_FIELDS_CONFIG);
    }

    public function setSubEntitiesFieldsConfig(?array $subentitesFieldsConfig): FieldsConfigInterface
    {
        $this->setData(self::SUBENTITIES_FIELDS_CONFIG, $subentitesFieldsConfig);

        return $this;
    }

    public function getExtensionAttributes(): FieldsConfigExtensionInterface
    {
        if (null === $this->getData(self::EXTENSION_ATTRIBUTES_KEY)) {
            $this->setExtensionAttributes($this->extensionFactory->create());
        }

        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    public function setExtensionAttributes(
        FieldsConfigExtensionInterface $extensionAttributes
    ): FieldsConfigInterface {
        $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);

        return $this;
    }
}
