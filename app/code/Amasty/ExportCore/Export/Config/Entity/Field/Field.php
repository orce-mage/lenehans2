<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Config\Entity\Field;

use Amasty\ExportCore\Api\Config\Entity\Field\FieldExtensionInterfaceFactory;
use Amasty\ExportCore\Api\Config\Entity\Field\FieldInterface;
use Magento\Framework\DataObject;

class Field extends DataObject implements FieldInterface
{
    const NAME = 'name';
    const LABEL = 'label';
    const MAP = 'map';
    const ACTIONS = 'actions';
    const FILTER = 'filter';
    const REMOVE = 'remove';

    /**
     * @var FieldExtensionInterfaceFactory
     */
    private $extensionAttributesFactory;

    public function __construct(
        FieldExtensionInterfaceFactory $extensionAttributesFactory,
        array $data = []
    ) {
        parent::__construct($data);
        $this->extensionAttributesFactory = $extensionAttributesFactory;
    }

    public function getName(): string
    {
        return $this->getData(self::NAME);
    }

    public function setName(?string $name): FieldInterface
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

    public function getMap(): ?string
    {
        return $this->getData(self::MAP);
    }

    public function setMap(?string $map): FieldInterface
    {
        $this->setData(self::MAP, $map);

        return $this;
    }

    public function getActions(): ?array
    {
        return $this->getData(self::ACTIONS);
    }

    public function setActions(?array $actions): FieldInterface
    {
        $this->setData(self::ACTIONS, $actions);

        return $this;
    }

    public function getFilter()
    {
        return $this->getData(self::FILTER);
    }

    public function setFilter($filter): FieldInterface
    {
        $this->setData(self::FILTER, $filter);

        return $this;
    }

    public function setRemove($remove)
    {
        $this->setData(self::REMOVE, $remove);
    }

    public function getRemove()
    {
        return $this->getData(self::REMOVE) ?: false;
    }

    public function getExtensionAttributes(): ?\Amasty\ExportCore\Api\Config\Entity\Field\FieldExtensionInterface
    {
        if (!$this->hasData(self::EXTENSION_ATTRIBUTES_KEY)) {
            $this->setExtensionAttributes($this->extensionAttributesFactory->create());
        }

        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    public function setExtensionAttributes(
        \Amasty\ExportCore\Api\Config\Entity\Field\FieldExtensionInterface $extensionAttributes
    ): FieldInterface {
        $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);

        return $this;
    }
}
