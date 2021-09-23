<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Api\Config\Entity\Field;

use Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface;

interface FilterInterface
{
    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param string $type
     *
     * @return \Amasty\ExportCore\Api\Config\Entity\Field\FilterInterface
     */
    public function setType(string $type): FilterInterface;

    /**
     * @return \Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface|null
     */
    public function getMetaClass(): ?ConfigClassInterface;

    /**
     * @param \Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface $metaClass
     *
     * @return \Amasty\ExportCore\Api\Config\Entity\Field\FilterInterface
     */
    public function setMetaClass(ConfigClassInterface $metaClass): FilterInterface;

    /**
     * @return \Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface|null
     */
    public function getFilterClass(): ?ConfigClassInterface;

    /**
     * @param \Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface $filterClass
     *
     * @return \Amasty\ExportCore\Api\Config\Entity\Field\FilterInterface
     */
    public function setFilterClass(ConfigClassInterface $filterClass): FilterInterface;
}
