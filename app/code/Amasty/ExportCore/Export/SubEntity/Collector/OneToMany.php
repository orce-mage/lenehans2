<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\SubEntity\Collector;

use Amasty\ExportCore\Api\Config\Entity\SubEntityCollectorInterface;
use Amasty\ExportCore\Api\Config\Profile\FieldsConfigInterface;
use Amasty\ExportCore\Api\Config\Relation\RelationInterface;
use Amasty\ExportCore\Export\Action\Preparation\Collection\Factory as CollectionFactory;
use Amasty\ExportCore\Export\Action\Preparation\Collection\PrepareCollection;
use Amasty\ExportCore\Export\Config\EntityConfigProvider;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Data\Collection;
use Magento\Framework\EntityManager\MetadataPool;

class OneToMany implements SubEntityCollectorInterface
{
    const PARENT_FIELD_NAME = 'parent_field_name';
    const CHILD_FIELD_NAME = 'child_field_name';
    const PARENT_ENTITY_NAME = 'parent_entity_name';
    const CHILD_ENTITY_NAME = 'child_entity_name';

    /** @var string */
    protected $parentFieldName;

    /** @var string */
    protected $childFieldName;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var RelationInterface
     */
    private $config;

    /**
     * @var PrepareCollection
     */
    private $prepareCollection;

    /**
     * @var EntityConfigProvider
     */
    private $entityConfigProvider;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    public function __construct(
        EntityConfigProvider $entityConfigProvider,
        CollectionFactory $collectionFactory,
        PrepareCollection $prepareCollection,
        RelationInterface $config,
        MetadataPool $metadataPool
    ) {
        $arguments = $config->getArguments();
        if (!isset($arguments[self::PARENT_FIELD_NAME]) && !isset($arguments[self::PARENT_ENTITY_NAME])) {
            throw new \LogicException('Parent field or entity is not specified');
        }
        if (!isset($arguments[self::CHILD_FIELD_NAME]) && !isset($arguments[self::CHILD_ENTITY_NAME])) {
            throw new \LogicException('Child field or entity is not specified');
        }

        $this->metadataPool = $metadataPool;
        if (isset($arguments[self::PARENT_ENTITY_NAME])) {
            $this->parentFieldName = $this->getLinkField($arguments[self::PARENT_ENTITY_NAME]);
        } else {
            $this->parentFieldName = $arguments[self::PARENT_FIELD_NAME];
        }
        if (isset($arguments[self::CHILD_ENTITY_NAME])) {
            $this->childFieldName = $this->getLinkField($arguments[self::CHILD_ENTITY_NAME]);
        } else {
            $this->childFieldName = $arguments[self::CHILD_FIELD_NAME];
        }
        $this->collectionFactory = $collectionFactory;
        $this->config = $config;
        $this->prepareCollection = $prepareCollection;
        $this->entityConfigProvider = $entityConfigProvider;
    }

    public function collect(array &$parentData, FieldsConfigInterface $fieldsConfig): SubEntityCollectorInterface
    {
        $collection = $this->collectionFactory->create(
            $this->entityConfigProvider->get($this->config->getChildEntityCode())
        );
        $this->prepareCollection->execute($collection, $this->config->getChildEntityCode(), $fieldsConfig);

        $cumulativeIds = array_unique(array_column($parentData, $this->parentFieldName));
        $collection->addFieldToFilter($this->childFieldName, ['in' => $cumulativeIds]);
        $collection->addFieldToSelect($this->childFieldName);
        $subEntities = [];
        foreach ($this->fetchData($collection) as $row) {
            $id = $row[$this->childFieldName];
            if (!isset($subEntities[$id])) {
                $subEntities[$id] = [];
            }

            $subEntities[$id][] = $row;
        }

        foreach ($parentData as $key => &$parentRow) {
            $id = $parentRow[$this->parentFieldName] ?? null;
            if (isset($subEntities[$id])) {
                $parentRow[$this->config->getSubEntityFieldName()] = $subEntities[$id];
            } elseif ($fieldsConfig->isExcludeRowIfNoResultsFound()) {
                unset($parentData[$key]);
            } else {
                $parentRow[$this->config->getSubEntityFieldName()] = [];
            }
        }

        return $this;
    }

    protected function fetchData(Collection $collection)
    {
        if ($collection instanceof AbstractCollection) {
            $data = $this->getDataWithEav($collection);
        } else {
            $data = $collection->getData();
        }

        return $data;
    }

    protected function getDataWithEav(Collection $collection)
    {
        $data = [];
        foreach ($collection->getItems() as $item) {
            $data[] = $item->getData();
        }

        return $data;
    }

    public function getParentRequiredFields(): array
    {
        return [$this->parentFieldName];
    }

    public function getLinkField($entityType): string
    {
        return $this->metadataPool->getMetadata($entityType)->getLinkField();
    }
}
