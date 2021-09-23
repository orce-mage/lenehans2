<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Api\Config\Profile;

interface FieldInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const FIELD_TYPE = 'field';
    const VIRTUAL_TYPE = 'virtual';
    const STATIC_TYPE = 'static';

    /**
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * @param string $name
     *
     * @return \Amasty\ExportCore\Api\Config\Profile\FieldInterface
     */
    public function setName(string $name): FieldInterface;

    /**
     * @return string|null
     */
    public function getMap(): ?string;

    /**
     * @param string $map
     *
     * @return \Amasty\ExportCore\Api\Config\Profile\FieldInterface
     */
    public function setMap(string $map): FieldInterface;

    /**
     * @return string|null
     */
    public function getType(): ?string;

    /**
     * @param string $type
     *
     * @return \Amasty\ExportCore\Api\Config\Profile\FieldInterface
     */
    public function setType(string $type): FieldInterface;

    /**
     * @return int|null
     */
    public function getSortOrder(): ?int;

    /**
     * @param int|null $sortOrder
     *
     * @return \Amasty\ExportCore\Api\Config\Profile\FieldInterface
     */
    public function setSortOrder(?int $sortOrder): FieldInterface;

    /**
     * @return \Amasty\ExportCore\Api\Config\Profile\ModifierInterface[]
     */
    public function getModifiers(): array;

    /**
     * @param \Amasty\ExportCore\Api\Config\Profile\ModifierInterface[] $modifiers
     *
     * @return \Amasty\ExportCore\Api\Config\Profile\FieldInterface
     */
    public function setModifiers(?array $modifiers): FieldInterface;

    /**
     * @return \Amasty\ExportCore\Api\Config\Profile\FieldExtensionInterface
     */
    public function getExtensionAttributes(): \Amasty\ExportCore\Api\Config\Profile\FieldExtensionInterface;

    /**
     * @param \Amasty\ExportCore\Api\Config\Profile\FieldExtensionInterface $extensionAttributes
     *
     * @return \Amasty\ExportCore\Api\Config\Profile\FieldInterface
     */
    public function setExtensionAttributes(
        \Amasty\ExportCore\Api\Config\Profile\FieldExtensionInterface $extensionAttributes
    ): FieldInterface;
}
