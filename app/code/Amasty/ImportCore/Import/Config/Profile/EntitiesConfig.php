<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Config\Profile;

use Amasty\ImportCore\Api\Config\Profile\EntitiesConfigExtensionInterface;
use Amasty\ImportCore\Api\Config\Profile\EntitiesConfigExtensionInterfaceFactory;
use Amasty\ImportCore\Api\Config\Profile\EntitiesConfigInterface;
use Magento\Framework\DataObject;

class EntitiesConfig extends DataObject implements EntitiesConfigInterface
{
    const ENTITY_CODE = 'entity_code';
    const BEHAVIOR = 'behavior';
    const MAP = 'map';
    const FIELDS = 'fields';
    const SUB_ENTITIES_CONFIG = 'sub_entities_config';
    const IS_ROOT = 'is_root';

    /**
     * @var EntitiesConfigExtensionInterfaceFactory
     */
    private $extensionFactory;

    public function __construct(
        EntitiesConfigExtensionInterfaceFactory $extensionFactory,
        array $data = []
    ) {
        parent::__construct($data);
        $this->extensionFactory = $extensionFactory;
    }

    public function getEntityCode(): string
    {
        return $this->getData(self::ENTITY_CODE);
    }

    public function setEntityCode(string $entityCode): EntitiesConfigInterface
    {
        return $this->setData(self::ENTITY_CODE, $entityCode);
    }

    public function getBehavior(): string
    {
        return $this->getData(self::BEHAVIOR);
    }

    public function setBehavior(string $behavior): EntitiesConfigInterface
    {
        return $this->setData(self::BEHAVIOR, $behavior);
    }

    public function getMap(): ?string
    {
        return $this->getData(self::MAP);
    }

    public function setMap(string $map): EntitiesConfigInterface
    {
        return $this->setData(self::MAP, $map);
    }

    public function getFields(): array
    {
        return $this->getData(self::FIELDS) ?? [];
    }

    public function setFields(array $fields): EntitiesConfigInterface
    {
        return $this->setData(self::FIELDS, $fields);
    }

    public function getSubEntitiesConfig(): array
    {
        return $this->getData(self::SUB_ENTITIES_CONFIG) ?? [];
    }

    public function setSubEntitiesConfig(array $subEntitiesConfig): EntitiesConfigInterface
    {
        return $this->setData(self::SUB_ENTITIES_CONFIG, $subEntitiesConfig);
    }

    public function getIsRoot(): bool
    {
        return $this->getData(self::IS_ROOT) ?: false;
    }

    public function setIsRoot(bool $isRoot): EntitiesConfigInterface
    {
        return $this->setData(self::IS_ROOT, $isRoot);
    }

    public function getExtensionAttributes(): EntitiesConfigExtensionInterface
    {
        if (null === $this->getData(self::EXTENSION_ATTRIBUTES_KEY)) {
            $this->setExtensionAttributes($this->extensionFactory->create());
        }

        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    public function setExtensionAttributes(
        EntitiesConfigExtensionInterface $extensionAttributes
    ): EntitiesConfigInterface {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
