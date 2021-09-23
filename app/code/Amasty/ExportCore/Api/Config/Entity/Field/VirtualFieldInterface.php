<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Api\Config\Entity\Field;

use Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface;

interface VirtualFieldInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     *
     * @return \Amasty\ExportCore\Api\Config\Entity\Field\VirtualFieldInterface
     */
    public function setName(?string $name): VirtualFieldInterface;

    /**
     * @return string
     */
    public function getLabel(): ?string;

    /**
     * @param string $label
     *
     * @return \Amasty\ExportCore\Api\Config\Entity\Field\VirtualFieldInterface
     */
    public function setLabel(?string $label): VirtualFieldInterface;

    /**
     * @return \Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface|null
     */
    public function getGenerator(): ?ConfigClassInterface;

    /**
     * @param \Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface $generatorClass
     *
     * @return \Amasty\ExportCore\Api\Config\Entity\Field\VirtualFieldInterface
     */
    public function setGenerator(ConfigClassInterface $generatorClass): VirtualFieldInterface;

    /**
     * @return \Amasty\ExportCore\Api\Config\Entity\Field\VirtualFieldExtensionInterface
     */
    public function getExtensionAttributes()
        : ?\Amasty\ExportCore\Api\Config\Entity\Field\VirtualFieldExtensionInterface;

    /**
     * @param \Amasty\ExportCore\Api\Config\Entity\Field\VirtualFieldExtensionInterface $extensionAttributes
     *
     * @return \Amasty\ExportCore\Api\Config\Entity\Field\VirtualFieldInterface
     */
    public function setExtensionAttributes(
        \Amasty\ExportCore\Api\Config\Entity\Field\VirtualFieldExtensionInterface $extensionAttributes
    ): VirtualFieldInterface;
}
