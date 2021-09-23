<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Api\Config\Profile;

use Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface;

interface FieldFilterInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return string|null
     */
    public function getField(): ?string;

    /**
     * @param string|null $field
     *
     * @return \Amasty\ExportCore\Api\Config\Profile\FieldFilterInterface
     */
    public function setField(?string $field): FieldFilterInterface;

    /**
     * @return string|null
     */
    public function getCondition(): ?string;

    /**
     * @param string|null $condition
     *
     * @return \Amasty\ExportCore\Api\Config\Profile\FieldFilterInterface
     */
    public function setCondition(?string $condition): FieldFilterInterface;

    /**
     * @return string|null
     */
    public function getType(): ?string;

    /**
     * @param string|null $filterType
     *
     * @return \Amasty\ExportCore\Api\Config\Profile\FieldFilterInterface
     */
    public function setType(?string $filterType): FieldFilterInterface;

    /**
     * @return \Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface|null
     */
    public function getFilterClass(): ?ConfigClassInterface;

    /**
     * @param \Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface|null $filterType
     *
     * @return \Amasty\ExportCore\Api\Config\Profile\FieldFilterInterface
     */
    public function setFilterClass(?ConfigClassInterface $filterClass): FieldFilterInterface;

    /**
     * @return \Amasty\ExportCore\Api\Config\Profile\FieldFilterExtensionInterface
     */
    public function getExtensionAttributes(): \Amasty\ExportCore\Api\Config\Profile\FieldFilterExtensionInterface;

    /**
     * @param \Amasty\ExportCore\Api\Config\Profile\FieldFilterExtensionInterface $extensionAttributes
     *
     * @return \Amasty\ExportCore\Api\Config\Profile\FieldFilterInterface
     */
    public function setExtensionAttributes(
        \Amasty\ExportCore\Api\Config\Profile\FieldFilterExtensionInterface $extensionAttributes
    ): FieldFilterInterface;
}
