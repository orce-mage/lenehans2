<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Api\Config\Profile;

interface FieldInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const FIELD_TYPE = 'field';

    /**
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * @param string $name
     *
     * @return \Amasty\ImportCore\Api\Config\Profile\FieldInterface
     */
    public function setName(string $name): FieldInterface;

    /**
     * @return string|null
     */
    public function getLabel(): ?string;

    /**
     * @param string $label
     *
     * @return \Amasty\ImportCore\Api\Config\Profile\FieldInterface
     */
    public function setLabel(?string $label): FieldInterface;

    /**
     * @return string|null
     */
    public function getValue(): ?string;

    /**
     * @param string|null $value
     * @return FieldInterface
     */
    public function setValue(?string $value): FieldInterface;

    /**
     * @return string|null
     */
    public function getMap(): ?string;

    /**
     * @param string $map
     *
     * @return \Amasty\ImportCore\Api\Config\Profile\FieldInterface
     */
    public function setMap(string $map): FieldInterface;

    /**
     * @return \Amasty\ImportCore\Api\Config\Profile\FieldExtensionInterface
     */
    public function getExtensionAttributes(): \Amasty\ImportCore\Api\Config\Profile\FieldExtensionInterface;

    /**
     * @param \Amasty\ImportCore\Api\Config\Profile\FieldExtensionInterface $extensionAttributes
     *
     * @return \Amasty\ImportCore\Api\Config\Profile\FieldInterface
     */
    public function setExtensionAttributes(
        \Amasty\ImportCore\Api\Config\Profile\FieldExtensionInterface $extensionAttributes
    ): FieldInterface;
}
