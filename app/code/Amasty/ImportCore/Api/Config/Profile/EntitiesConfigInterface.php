<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Api\Config\Profile;

interface EntitiesConfigInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get entity code
     *
     * @return string
     */
    public function getEntityCode(): string;

    /**
     * Set entity code
     *
     * @param string $entityCode
     * @return $this
     */
    public function setEntityCode(string $entityCode): EntitiesConfigInterface;

    /**
     * Get behavior code
     *
     * @return string
     */
    public function getBehavior(): string;

    /**
     * Set behavior code
     *
     * @param string $behavior
     * @return EntitiesConfigInterface
     */
    public function setBehavior(string $behavior): EntitiesConfigInterface;

    /**
     * Get map
     *
     * @return string|null
     */
    public function getMap(): ?string;

    /**
     * Set map
     *
     * @param string $map
     * @return $this
     */
    public function setMap(string $map): EntitiesConfigInterface;

    /**
     * Get entity fields
     *
     * @return \Amasty\ImportCore\Api\Config\Profile\FieldInterface[]
     */
    public function getFields(): array;

    /**
     * @param \Amasty\ImportCore\Api\Config\Profile\FieldInterface[] $fields
     * @return EntitiesConfigInterface
     */
    public function setFields(array $fields): EntitiesConfigInterface;

    /**
     * Get sub-entities config
     *
     * @return \Amasty\ImportCore\Api\Config\Profile\EntitiesConfigInterface[]
     */
    public function getSubEntitiesConfig(): array;

    /**
     * Set sub-entities config
     *
     * @param \Amasty\ImportCore\Api\Config\Profile\EntitiesConfigInterface[] $subEntitiesConfig
     * @return \Amasty\ImportCore\Api\Config\Profile\EntitiesConfigInterface
     */
    public function setSubEntitiesConfig(array $subEntitiesConfig): EntitiesConfigInterface;

    /**
     * Get root entity flag
     *
     * @return bool
     */
    public function getIsRoot(): bool;

    /**
     * Set root entity flag
     *
     * @param bool $isRoot
     * @return EntitiesConfigInterface
     */
    public function setIsRoot(bool $isRoot): EntitiesConfigInterface;

    /**
     * Get existing extension attributes object or create a new one
     *
     * @return \Amasty\ImportCore\Api\Config\Profile\EntitiesConfigExtensionInterface
     */
    public function getExtensionAttributes(): \Amasty\ImportCore\Api\Config\Profile\EntitiesConfigExtensionInterface;

    /**
     * Set an extension attributes object
     *
     * @param \Amasty\ImportCore\Api\Config\Profile\EntitiesConfigExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Amasty\ImportCore\Api\Config\Profile\EntitiesConfigExtensionInterface $extensionAttributes
    ): EntitiesConfigInterface;
}
