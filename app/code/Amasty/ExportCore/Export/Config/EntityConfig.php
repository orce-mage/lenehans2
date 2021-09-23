<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\Config;

use Amasty\ExportCore\Api\Config\Entity\FieldsConfigInterface;
use Amasty\ExportCore\Api\Config\EntityConfigExtensionInterfaceFactory;
use Amasty\ExportCore\Api\Config\EntityConfigInterface;
use Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface;
use Magento\Framework\DataObject;

class EntityConfig extends DataObject implements EntityConfigInterface
{
    const ENTITY_CODE = 'entity_code';
    const NAME = 'name';
    const GROUP = 'group';
    const DESCRIPTION = 'description';
    const COLLECTION_FACTORY = 'collection_factory';
    const COLLECTION_MODIFIER = 'collection_modifier';
    const IS_HIDDEN_IN_LISTS = 'is_hidden_in_lists';
    const FIELDS_CONFIG = 'fields_config';
    const FIELDS_CONFIG_RESOLVE = 'fields_config_resolve';

    /**
     * @var EntityConfigExtensionInterfaceFactory
     */
    private $extensionAttributesFactory;

    public function __construct(
        EntityConfigExtensionInterfaceFactory $extensionAttributesFactory,
        array $data = []
    ) {
        parent::__construct($data);
        $this->extensionAttributesFactory = $extensionAttributesFactory;
    }

    public function getEntityCode(): ?string
    {
        return $this->getData(self::ENTITY_CODE);
    }

    public function setEntityCode(?string $entityCode): EntityConfigInterface
    {
        $this->setData(self::ENTITY_CODE, $entityCode);

        return $this;
    }

    public function getName(): ?string
    {
        return $this->getData(self::NAME);
    }

    public function setName(?string $name): EntityConfigInterface
    {
        $this->setData(self::NAME, $name);

        return $this;
    }

    public function getGroup(): ?string
    {
        return $this->getData(self::GROUP);
    }

    public function setGroup(?string $group): EntityConfigInterface
    {
        $this->setData(self::GROUP, $group);

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->getData(self::DESCRIPTION);
    }

    public function setDescription(?string $description): EntityConfigInterface
    {
        $this->setData(self::DESCRIPTION, $description);

        return $this;
    }

    public function getCollectionFactory(): ?ConfigClassInterface
    {
        return $this->getData(self::COLLECTION_FACTORY);
    }

    public function setCollectionFactory(?ConfigClassInterface $collectionFactory): EntityConfigInterface
    {
        $this->setData(self::COLLECTION_FACTORY, $collectionFactory);

        return $this;
    }

    public function getCollectionModifier(): ?ConfigClassInterface
    {
        return $this->getData(self::COLLECTION_MODIFIER);
    }

    public function setCollectionModifier(?ConfigClassInterface $collectionModifier): EntityConfigInterface
    {
        $this->setData(self::COLLECTION_MODIFIER, $collectionModifier);

        return $this;
    }

    public function isHiddenInLists(): ?bool
    {
        return $this->getData(self::IS_HIDDEN_IN_LISTS);
    }

    public function setHiddenInLists(?bool $isHiddenInLists): EntityConfigInterface
    {
        $this->setData(self::IS_HIDDEN_IN_LISTS, $isHiddenInLists);

        return $this;
    }

    public function getFieldsConfig(): ?FieldsConfigInterface
    {
        if (!$this->hasData(self::FIELDS_CONFIG) && $this->hasData(self::FIELDS_CONFIG_RESOLVE)) {
            $this->setFieldsConfig($this->getData(self::FIELDS_CONFIG_RESOLVE)());
        }

        return $this->getData(self::FIELDS_CONFIG);
    }

    public function setFieldsConfig(?FieldsConfigInterface $fieldsConfig): EntityConfigInterface
    {
        $this->setData(self::FIELDS_CONFIG, $fieldsConfig);

        return $this;
    }

    public function setFieldsConfigResolveClosure(\Closure $fieldsConfigClosure): void
    {
        $this->setData(self::FIELDS_CONFIG_RESOLVE, $fieldsConfigClosure);
    }

    public function getExtensionAttributes(): \Amasty\ExportCore\Api\Config\EntityConfigExtensionInterface
    {
        if (!$this->hasData(self::EXTENSION_ATTRIBUTES_KEY)) {
            $this->setExtensionAttributes($this->extensionAttributesFactory->create());
        }

        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    public function setExtensionAttributes(
        \Amasty\ExportCore\Api\Config\EntityConfigExtensionInterface $extensionAttributes
    ): EntityConfigInterface {
        $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);

        return $this;
    }
}
