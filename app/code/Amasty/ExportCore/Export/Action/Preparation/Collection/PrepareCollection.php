<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Action\Preparation\Collection;

use Amasty\ExportCore\Api\Config\Profile\FieldsConfigInterface;
use Amasty\ExportCore\Export\Config\EntityConfigProvider;
use Amasty\ExportCore\Export\Config\RelationConfigProvider;
use Amasty\ExportCore\Export\Filter\FilterProvider;
use Amasty\ExportCore\Export\SubEntity\CollectorFactory;
use Amasty\ImportExportCore\Config\ConfigClass\Factory as ConfigClassFactory;
use Magento\Framework\Data\Collection;

class PrepareCollection
{
    /**
     * @var EntityConfigProvider
     */
    private $entityConfigProvider;

    /**
     * @var ConfigClassFactory
     */
    private $configClassFactory;

    /**
     * @var FilterProvider
     */
    private $filterProvider;

    /**
     * @var CollectorFactory
     */
    private $collectorFactory;

    /**
     * @var RelationConfigProvider
     */
    private $relationConfigProvider;

    public function __construct(
        EntityConfigProvider $entityConfigProvider,
        ConfigClassFactory $configClassFactory,
        RelationConfigProvider $relationConfigProvider,
        CollectorFactory $collectorFactory,
        FilterProvider $filterProvider
    ) {
        $this->entityConfigProvider = $entityConfigProvider;
        $this->configClassFactory = $configClassFactory;
        $this->filterProvider = $filterProvider;
        $this->collectorFactory = $collectorFactory;
        $this->relationConfigProvider = $relationConfigProvider;
    }

    public function execute(Collection $collection, string $entityCode, FieldsConfigInterface $fieldsConfig)
    {
        if ($collection instanceof \Magento\Framework\App\ResourceConnection\SourceProviderInterface) {
            $this->addFieldsToSelect($collection, $entityCode, $fieldsConfig->getFields());
            $this->addSubentityRequiredFields($collection, $entityCode);
        }
        $this->applyFilters($collection, $entityCode, $fieldsConfig->getFilters());
    }

    public function addFieldsToSelect(Collection $collection, string $entityCode, ?array $fields): PrepareCollection
    {
        $fieldsToSelect = [];
        if (!empty($fields)) {
            $entityFields = $this->entityConfigProvider->get($entityCode)->getFieldsConfig()->getFields();
            $entityFieldNames = [];
            foreach ($entityFields as $entityField) {
                $entityFieldNames[] = $entityField->getName();
            }
            foreach ($fields as $field) {
                if (in_array($field->getName(), $entityFieldNames)) {
                    $fieldsToSelect[] = $field->getName();
                }
            }
        }

        if (!empty($fieldsToSelect)) {
            $collection->addFieldToSelect($fieldsToSelect);
        }

        return $this;
    }

    public function applyFilters(Collection $collection, string $entityCode, ?array $filters): PrepareCollection
    {
        if (empty($filters)) {
            return $this;
        }
        $entityFields = $this->entityConfigProvider->get($entityCode)->getFieldsConfig()->getFields();

        $entityFieldsFilter = [];
        foreach ($entityFields as $field) {
            $entityFieldsFilter[$field->getName()] = $field->getFilter();
        }
        foreach ($filters as $filter) {
            if (!empty($filter->getType())) {
                $this->filterProvider->getFilter($filter->getType())
                    ->apply($collection, $filter);
            } elseif (!empty($filter->getFilterClass())) {
                $this->configClassFactory->createObject($filter->getFilterClass())->apply($collection, $filter);
            } elseif (!empty($entityFieldsFilter[$filter->getField()])) {
                $this->configClassFactory->createObject(
                    $entityFieldsFilter[$filter->getField()]->getFilterClass()
                )->apply($collection, $filter);
            }
        }

        return $this;
    }

    protected function addSubentityRequiredFields(Collection $collection, string $entityCode): PrepareCollection
    {
        $relations = $this->relationConfigProvider->get($entityCode);
        if (!empty($relations)) {
            foreach ($relations as $relationConfig) {
                foreach ($this->collectorFactory->create($relationConfig)->getParentRequiredFields() as $field) {
                    $collection->addFieldToSelect($field);
                }
            }
        }

        return $this;
    }
}
