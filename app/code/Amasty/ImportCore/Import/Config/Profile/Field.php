<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Import\Config\Profile;

use Amasty\ImportCore\Api\Config\Profile\FieldExtensionInterface;
use Amasty\ImportCore\Api\Config\Profile\FieldExtensionInterfaceFactory;
use Amasty\ImportCore\Api\Config\Profile\FieldInterface;
use Magento\Framework\DataObject;

class Field extends DataObject implements FieldInterface
{
    const NAME = 'name';
    const MAP = 'map';
    const LABEL = 'label';
    const VALUE = 'value';

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

    public function getLabel(): ?string
    {
        return $this->getData(self::LABEL);
    }

    public function setLabel(?string $label): FieldInterface
    {
        $this->setData(self::LABEL, $label);

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->getData(self::VALUE);
    }

    public function setValue(?string $value): FieldInterface
    {
        $this->setData(self::VALUE, $value);

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

    public function getExtensionAttributes(): FieldExtensionInterface
    {
        if (null === $this->getData(self::EXTENSION_ATTRIBUTES_KEY)) {
            $this->setExtensionAttributes($this->extensionFactory->create());
        }

        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    public function setExtensionAttributes(
        FieldExtensionInterface $extensionAttributes
    ): FieldInterface {
        $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);

        return $this;
    }
}
