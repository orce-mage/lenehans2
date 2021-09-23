<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Config\Profile;

use Amasty\ExportCore\Api\Config\Profile\FieldExtensionInterfaceFactory;
use Amasty\ExportCore\Api\Config\Profile\FieldInterface;
use Magento\Framework\DataObject;

class Field extends DataObject implements FieldInterface
{
    const NAME = 'name';
    const MAP = 'map';
    const TYPE = 'type';
    const MODIFIERS = 'modifiers';
    const SORT_ORDER = 'sort_order';

    /**
     * @var FieldExtensionInterfaceFactory
     */
    private $extensionFactory;

    public function __construct(
        FieldExtensionInterfaceFactory $extensionFactory,
        array $data = []
    ) {
        parent::__construct($data);
        $this->extensionFactory = $extensionFactory;
    }

    public function getName(): ?string
    {
        return $this->getData(self::NAME);
    }

    public function setName(string $name): FieldInterface
    {
        $this->setData(self::NAME, $name);

        return $this;
    }

    public function getMap(): ?string
    {
        return $this->getData(self::MAP);
    }

    public function setMap(string $map): FieldInterface
    {
        $this->setData(self::MAP, $map);

        return $this;
    }

    public function getType(): ?string
    {
        return $this->getData(self::TYPE);
    }

    public function setType(string $type): FieldInterface
    {
        $this->setData(self::TYPE, $type);

        return $this;
    }

    public function getSortOrder(): ?int
    {
        return $this->getData(self::SORT_ORDER);
    }

    public function setSortOrder(?int $sortOrder): FieldInterface
    {
        $this->setData(self::SORT_ORDER, $sortOrder);

        return $this;
    }

    public function getModifiers(): array
    {
        return $this->getData(self::MODIFIERS) ?? [];
    }

    public function setModifiers(?array $modifiers): FieldInterface
    {
        $this->setData(self::MODIFIERS, $modifiers);

        return $this;
    }

    public function getExtensionAttributes(): \Amasty\ExportCore\Api\Config\Profile\FieldExtensionInterface
    {
        if (null === $this->getData(self::EXTENSION_ATTRIBUTES_KEY)) {
            $this->setExtensionAttributes($this->extensionFactory->create());
        }

        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    public function setExtensionAttributes(
        \Amasty\ExportCore\Api\Config\Profile\FieldExtensionInterface $extensionAttributes
    ): FieldInterface {
        $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);

        return $this;
    }
}
