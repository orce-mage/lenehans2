<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Config\Entity;

use Amasty\ExportCore\Api\Config\Entity\FieldsConfigExtensionInterfaceFactory;
use Amasty\ExportCore\Api\Config\Entity\FieldsConfigInterface;
use Magento\Framework\DataObject;

class FieldsConfig extends DataObject implements FieldsConfigInterface
{
    const FIELDS = 'fields';
    const VIRTUAL_FIELDS = 'virtual_fields';
    const ROW_ACTION_CLASS = 'row_action_class';

    /**
     * @var FieldsConfigExtensionInterfaceFactory
     */
    private $extensionAttributesFactory;

    public function __construct(
        FieldsConfigExtensionInterfaceFactory $extensionAttributesFactory,
        array $data = []
    ) {
        parent::__construct($data);
        $this->extensionAttributesFactory = $extensionAttributesFactory;
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

    public function getVirtualFields(): ?array
    {
        return $this->getData(self::VIRTUAL_FIELDS);
    }

    public function setVirtualFields(?array $virtualFields): FieldsConfigInterface
    {
        $this->setData(self::VIRTUAL_FIELDS, $virtualFields);

        return $this;
    }

    public function getRowActionClass(): ?string
    {
        return $this->getData(self::ROW_ACTION_CLASS);
    }

    public function setRowActionClass(?string $class): FieldsConfigInterface
    {
        $this->setData(self::ROW_ACTION_CLASS, $class);

        return $this;
    }

    public function getExtensionAttributes(): \Amasty\ExportCore\Api\Config\Entity\FieldsConfigExtensionInterface
    {
        if (!$this->hasData(self::EXTENSION_ATTRIBUTES_KEY)) {
            $this->setExtensionAttributes($this->extensionAttributesFactory->create());
        }

        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    public function setExtensionAttributes(
        \Amasty\ExportCore\Api\Config\Entity\FieldsConfigExtensionInterface $extensionAttributes
    ): FieldsConfigInterface {
        $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);

        return $this;
    }
}
