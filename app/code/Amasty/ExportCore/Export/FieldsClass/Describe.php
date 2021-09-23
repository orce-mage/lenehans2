<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\FieldsClass;

use Amasty\ExportCore\Api\Config\Entity\Field\FieldInterface;
use Amasty\ExportCore\Api\Config\Entity\Field\FieldInterfaceFactory;
use Amasty\ExportCore\Api\Config\Entity\Field\FilterInterface as FilterInstanceInterface;
use Amasty\ExportCore\Api\Config\Entity\Field\FilterInterfaceFactory;
use Amasty\ExportCore\Api\Config\Entity\FieldsConfigInterface;
use Amasty\ExportCore\Api\Filter\FilterConfigInterface;
use Amasty\ExportCore\Api\Filter\FilterInterface;
use Amasty\ExportCore\Api\Filter\FilterMetaInterface;
use Amasty\ExportCore\Export\Config\EntityConfig;
use Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterfaceFactory;
use Amasty\ImportExportCore\Config\ConfigClass\Factory;
use Magento\Framework\App\ResourceConnection\SourceProviderInterface;
use Magento\Framework\ObjectManagerInterface;

class Describe extends AbstractFieldsClass
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var FieldInterfaceFactory
     */
    private $fieldConfigFactory;

    /**
     * @var FilterInterfaceFactory
     */
    private $filterFactory;

    /**
     * @var FilterConfigInterface
     */
    private $filterConfig;

    /**
     * @var ConfigClassInterfaceFactory
     */
    private $configClassFactory;

    /**
     * @var Factory
     */
    private $factory;

    public function __construct(
        ObjectManagerInterface $objectManager,
        FieldInterfaceFactory $fieldConfigFactory,
        FilterInterfaceFactory $filterFactory,
        FilterConfigInterface $filterConfig,
        Factory $factory,
        ConfigClassInterfaceFactory $configClassFactory,
        array $config
    ) {
        $this->objectManager = $objectManager;
        $this->fieldConfigFactory = $fieldConfigFactory;
        $this->filterFactory = $filterFactory;
        $this->filterConfig = $filterConfig;
        $this->configClassFactory = $configClassFactory;
        $this->factory = $factory;
        $this->config = $config;
    }

    public function execute(FieldsConfigInterface $existingConfig, EntityConfig $entityConfig): FieldsConfigInterface
    {
        $columnInfo = $this->describe($entityConfig);

        $newFieldCallback = function (\ArrayIterator $fieldDetailsIterator) {
            /** @var FieldInterface $field */
            $field = $this->fieldConfigFactory->create();
            $field->setName($fieldDetailsIterator->key());
            $field->setFilter(
                $this->getFilterConfig($this->getFilterType($fieldDetailsIterator->current()))
            );

            return $field;
        };

        return $this->mergeFields(
            $existingConfig,
            new \ArrayIterator($columnInfo),
            $newFieldCallback
        );
    }

    /**
     * Get filter type based on column field details
     *
     * @param array $fieldDetails
     * @return string
     */
    protected function getFilterType(array $fieldDetails): string
    {
        switch (strtolower($fieldDetails['DATA_TYPE'])) {
            case 'date':
            case 'datetime':
            case 'timestamp':
                return \Amasty\ExportCore\Export\Filter\Type\Date\Filter::TYPE_ID;
            default:
                return \Amasty\ExportCore\Export\Filter\Type\Text\Filter::TYPE_ID;
        }
    }

    /**
     * Get filter config of specified filter type
     *
     * @param string $filterType
     * @return FilterInstanceInterface
     */
    protected function getFilterConfig(string $filterType): FilterInstanceInterface
    {
        $filterConfig = $this->filterConfig->get($filterType);

        $arguments = [];
        $filterClass = $this->configClassFactory->create([
            'baseType'  => FilterInterface::class,
            'name'      => $filterConfig['filterClass'],
            'arguments' => []
        ]);
        $metaClass = $this->configClassFactory->create([
            'baseType'  => FilterMetaInterface::class,
            'name'      => $filterConfig['metaClass'],
            'arguments' => $arguments
        ]);
        $filter = $this->filterFactory->create();
        $filter->setType($filterType);

        $filter->setMetaClass($metaClass);
        $filter->setFilterClass($filterClass);

        return $filter;
    }

    /**
     * Get the table columns descriptions
     *
     * @param EntityConfig $entityConfig
     * @return array
     */
    protected function describe(EntityConfig $entityConfig): array
    {
        $collection = $this->factory->createObject($entityConfig->getCollectionFactory())->create();
        if (!is_subclass_of($collection, SourceProviderInterface::class)) {
            throw new \LogicException(sprintf(
                '"Describe" fields class can work only with collections that implement %s',
                SourceProviderInterface::class
            ));
        }

        return $collection->getResource()->getConnection()->describeTable($collection->getMainTable());
    }
}
