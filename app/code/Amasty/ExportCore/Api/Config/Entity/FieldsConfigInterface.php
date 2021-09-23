<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Api\Config\Entity;

interface FieldsConfigInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return \Amasty\ExportCore\Api\Config\Entity\Field\FieldInterface[]
     */
    public function getFields(): ?array;

    /**
     * @param \Amasty\ExportCore\Api\Config\Entity\Field\FieldInterface[] $fields
     *
     * @return \Amasty\ExportCore\Api\Config\Entity\FieldsConfigInterface
     */
    public function setFields(?array $fields): FieldsConfigInterface;

    /**
     * @return \Amasty\ExportCore\Api\Config\Entity\Field\VirtualFieldInterface[]
     */
    public function getVirtualFields(): ?array;

    /**
     * @param \Amasty\ExportCore\Api\Config\Entity\Field\VirtualFieldInterface[] $virtualFields
     *
     * @return \Amasty\ExportCore\Api\Config\Entity\FieldsConfigInterface
     */
    public function setVirtualFields(?array $virtualFields): FieldsConfigInterface;

    /**
     * @return string
     */
    public function getRowActionClass(): ?string;

    /**
     * @param string $class
     *
     * @return \Amasty\ExportCore\Api\Config\Entity\FieldsConfigInterface
     */
    public function setRowActionClass(?string $class): FieldsConfigInterface;

    /**
     * @return \Amasty\ExportCore\Api\Config\Entity\FieldsConfigExtensionInterface
     */
    public function getExtensionAttributes(): \Amasty\ExportCore\Api\Config\Entity\FieldsConfigExtensionInterface;

    /**
     * @param \Amasty\ExportCore\Api\Config\Entity\FieldsConfigExtensionInterface $extensionAttributes
     *
     * @return \Amasty\ExportCore\Api\Config\Entity\FieldsConfigInterface
     */
    public function setExtensionAttributes(
        \Amasty\ExportCore\Api\Config\Entity\FieldsConfigExtensionInterface $extensionAttributes
    ): FieldsConfigInterface;
}
