<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Config\Entity\Field;

use Amasty\ExportCore\Api\Config\Entity\Field\FilterInterface;
use Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface;

class Filter implements FilterInterface
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var ConfigClassInterface
     */
    private $metaClass;

    /**
     * @var ConfigClassInterface
     */
    private $filterClass;

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): FilterInterface
    {
        $this->type = $type;

        return $this;
    }

    public function getMetaClass(): ?ConfigClassInterface
    {
        return $this->metaClass;
    }

    public function setMetaClass(ConfigClassInterface $metaClass): FilterInterface
    {
        $this->metaClass = $metaClass;

        return $this;
    }

    public function getFilterClass(): ?ConfigClassInterface
    {
        return $this->filterClass;
    }

    public function setFilterClass(ConfigClassInterface $filterClass): FilterInterface
    {
        $this->filterClass = $filterClass;

        return $this;
    }
}
