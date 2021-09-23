<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Import\SampleData;

use Amasty\ImportCore\Api\Config\Relation\RelationConfigInterface;
use Amasty\ImportCore\Api\Source\SourceConfigInterface;
use Amasty\ImportCore\Api\Source\SourceGeneratorInterface;
use Amasty\ImportCore\Import\Config\EntityConfigProvider;
use Amasty\ImportCore\Import\Config\RelationConfigProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;

class FileContent
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var SourceConfigInterface
     */
    private $sourceConfig;

    /**
     * @var EntityConfigProvider
     */
    private $entityConfigProvider;

    /**
     * @var RelationConfigProvider
     */
    private $relationConfigProvider;

    public function __construct(
        ObjectManagerInterface $objectManager,
        SourceConfigInterface $sourceConfig,
        EntityConfigProvider $entityConfigProvider,
        RelationConfigProvider $relationConfigProvider
    ) {
        $this->objectManager = $objectManager;
        $this->sourceConfig = $sourceConfig;
        $this->entityConfigProvider = $entityConfigProvider;
        $this->relationConfigProvider = $relationConfigProvider;
    }

    public function get(string $entityCode, string $sourceType): array
    {
        $source = $this->sourceConfig->get($sourceType);
        $entity = $this->entityConfigProvider->get($entityCode);
        $relations = $this->relationConfigProvider->get($entityCode);

        if (empty($source['sampleFileGenerator'])) {
            throw new LocalizedException(__('Source doesn\'t have sample file generator.'));
        }

        if (empty($entity->getFieldsConfig()->getFields())) {
            throw new LocalizedException(__('Entity Fields Config is empty.'));
        }

        if (empty($entity->getFieldsConfig()->getSampleData())) {
            throw new LocalizedException(__('Entity doesn\'t have sample data.'));
        }

        $data = $this->prepareSampleData($entity->getFieldsConfig()->getSampleData());

        if (empty($data)) {
            throw new LocalizedException(__('Entity doesn\'t have sample data.'));
        }

        if (!empty($relations)) {
            foreach ($data as &$parentData) {
                $this->getSubEntitySampleData($relations, $parentData);
            }
        }

        /** @var SourceGeneratorInterface $sampleFileGenerator */
        $sampleFileGenerator = $this->objectManager->create($source['sampleFileGenerator']);
        $filename = $entityCode . '.' . $sampleFileGenerator->getExtension();
        $content = $sampleFileGenerator->generate($data);

        return [$filename, $content];
    }

    private function getSubEntitySampleData(array $relations, array &$data, string $parentKey = '')
    {
        /** @var RelationConfigInterface $relation */
        foreach ($relations as $relation) {
            $entity = $this->entityConfigProvider->get($relation->getChildEntityCode());
            $preparedSampleData = $this->prepareSampleData($entity->getFieldsConfig()->getSampleData() ?? []);
            if (!empty($data[$parentKey])) {
                foreach ($data[$parentKey] as &$parentData) {
                    $this->addSubEntitySampleData($relation, $parentData, $preparedSampleData);
                }
            } else {
                $this->addSubEntitySampleData($relation, $data, $preparedSampleData);
            }
            if ($childRelations = $relation->getRelations()) {
                $this->getSubEntitySampleData($childRelations, $data, $relation->getSubEntityFieldName());
            }
        }
    }

    private function prepareSampleData(array $sampleData): array
    {
        $result = [];
        foreach ($sampleData as $row) {
            $preparedRow = [];
            foreach ($row->getValues() as $value) {
                $preparedRow[$value->getField()] = $value->getValue();
            }

            $result[] = $preparedRow;
        }

        return $result;
    }

    private function addSubEntitySampleData(
        RelationConfigInterface $relation,
        array &$parentData,
        array $childData
    ): void {
        $parentFieldName = $relation->getParentFieldName();
        $childFieldName = $relation->getChildFieldName();

        if (isset($parentData[$parentFieldName])) {
            $parentLinkValue = $parentData[$parentFieldName];

            /**
             * @param array $row
             * @return bool
             */
            $filterCallback = function (array $row) use ($parentLinkValue, $childFieldName) {
                return isset($row[$childFieldName])
                    && $row[$childFieldName] == $parentLinkValue;
            };

            $childSampleRows = array_filter($childData, $filterCallback);
            if (!empty($childSampleRows)) {
                $parentData[$relation->getSubEntityFieldName()] = $childSampleRows;
            }
        }
    }
}
