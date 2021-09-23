<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */

declare(strict_types=1);

namespace Amasty\ExportCore\Export\Filter;

use Amasty\ImportExportCore\Api\Config\ConfigClass\ArgumentInterface;
use Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterface;
use Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterfaceFactory;
use Amasty\ExportCore\Api\Filter\FilterConfigInterface;
use Amasty\ExportCore\Api\Filter\FilterInterface;
use Amasty\ExportCore\Api\Filter\FilterMetaInterface;
use Amasty\ExportCore\Api\Config\Entity\Field\FilterInterface as FieldFilterInterface;
use Amasty\ExportCore\Api\Config\Entity\Field\FilterInterfaceFactory;

class FilterConfigBuilder
{
    /**
     * @var FilterConfigInterface
     */
    private $filterConfig;

    /**
     * @var FilterInterfaceFactory
     */
    private $filterFactory;

    /**
     * @var ConfigClassInterfaceFactory
     */
    private $configClassFactory;

    /**
     * @var string
     */
    private $filterType;

    /**
     * @var ArgumentInterface[]
     */
    private $metaArguments = [];

    public function __construct(
        FilterConfigInterface $filterConfig,
        FilterInterfaceFactory $filterFactory,
        ConfigClassInterfaceFactory $configClassFactory
    ) {
        $this->filterConfig = $filterConfig;
        $this->filterFactory = $filterFactory;
        $this->configClassFactory = $configClassFactory;
    }

    /**
     * Set filter type
     *
     * @param string $filterType
     * @return $this
     */
    public function setFilterType($filterType)
    {
        $this->filterType = $filterType;
        return $this;
    }

    /**
     * Get meta arguments
     *
     * @param ArgumentInterface[] $metaArguments
     * @return $this
     */
    public function setMetaArguments($metaArguments)
    {
        $this->metaArguments = $metaArguments;
        return $this;
    }

    /**
     * Build filter config instance
     *
     * @return FieldFilterInterface|null
     */
    public function build()
    {
        $result = $this->isValidForBuild()
            ? $this->performBuild()
            : null;
        $this->resetState();

        return $result;
    }

    /**
     * Performs build
     *
     * @return FieldFilterInterface
     */
    private function performBuild()
    {
        $filterConfig = $this->filterConfig->get($this->filterType);

        /** @var ConfigClassInterface $filterClass */
        $filterClass = $this->configClassFactory->create(
            [
                'baseType' => FilterInterface::class,
                'name' => $filterConfig['filterClass'],
                'arguments' => []
            ]
        );
        /** @var ConfigClassInterface $metaClass */
        $metaClass = $this->configClassFactory->create(
            [
                'baseType' => FilterMetaInterface::class,
                'name' => $filterConfig['metaClass'],
                'arguments' => $this->metaArguments
            ]
        );

        $filter = $this->filterFactory->create();
        $filter->setType($this->filterType)
            ->setMetaClass($metaClass)
            ->setFilterClass($filterClass);
        return $filter;
    }

    /**
     * Checks if the builder state valid for build
     *
     * @return bool
     */
    private function isValidForBuild()
    {
        return $this->filterType !== null;
    }

    /**
     * Reset builder state
     *
     * @return void
     */
    private function resetState()
    {
        $this->filterType = null;
        $this->metaArguments = [];
    }
}
