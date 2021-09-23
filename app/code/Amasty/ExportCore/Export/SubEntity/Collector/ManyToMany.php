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
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection;
use Magento\Framework\EntityManager\MetadataPool;

class ManyToMany implements SubEntityCollectorInterface
{
    const CONNECT_TABLE_NAME = 'connect_table_name';
    const PARENT_FIELD_NAME = 'parent_field_name';
    const CONNECT_PARENT_FIELD_NAME = 'connect_parent_field_name';
    const CHILD_FIELD_NAME = 'child_field_name';
    const CONNECT_CHILD_FIELD_NAME = 'connect_child_field_name';
    const PARENT_ENTITY_NAME = 'parent_entity_name';
    const CHILD_ENTITY_NAME = 'child_entity_name';

    /** @var string */
    protected $connectTableName;

    /** @var string */
    protected $parentFieldName;

    /** @var string */
    protected $childFieldName;

    /** @var string */
    protected $connectParentFieldName;

    /** @var string */
    protected $connectChildFieldName;

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
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    public function __construct(
        EntityConfigProvider $entityConfigProvider,
        CollectionFactory $collectionFactory,
        PrepareCollection $prepareCollection,
        ResourceConnection $resourceConnection,
        RelationInterface $config,
        MetadataPool $metadataPool
    ) {
        $arguments = $config->getArguments();
        if (!isset($arguments[self::CONNECT_PARENT_FIELD_NAME])) {
            throw new \LogicException(
                'Connect parent field name is not specified in relation ' . $config->getSubEntityFieldName()
            );
        }
        if (!isset($arguments[self::CONNECT_CHILD_FIELD_NAME])) {
            throw new \LogicException(
                'Connect child field name is not specified in relation ' . $config->getSubEntityFieldName()
            );
        }
        if (!isset($arguments[self::PARENT_FIELD_NAME]) && !isset($arguments[self::PARENT_ENTITY_NAME])) {
            throw new \LogicException(
                'Parent field or entity is not specified in relation ' . $config->getSubEntityFieldName()
            );
        }
        if (!isset($arguments[self::CHILD_FIELD_NAME]) && !isset($arguments[self::CHILD_ENTITY_NAME])) {
            throw new \LogicException(
                'Child field or entity is not specified in relation ' . $config->getSubEntityFieldName()
            );
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
        $this->connectTableName = $arguments[self::CONNECT_TABLE_NAME];
        $this->connectParentFieldName = $arguments[self::CONNECT_PARENT_FIELD_NAME];
        $this->connectChildFieldName = $arguments[self::CONNECT_CHILD_FIELD_NAME];
        $this->collectionFactory = $collectionFactory;
        $this->config = $config;
        $this->prepareCollection = $prepareCollection;
        $this->entityConfigProvider = $entityConfigProvider;
        $this->resourceConnection = $resourceConnection;
    }

    public function collect(array &$parentData, FieldsConfigInterface $fieldsConfig): SubEntityCollectorInterface
    {
        $collection = $this->collectionFactory->create(
            $this->entityConfigProvider->get($this->config->getChildEntityCode())
        );
        $this->prepareCollection->execute($collection, $this->config->getChildEntityCode(), $fieldsConfig);
        $ids = $this->getChildIds(array_unique(array_column($parentData, $this->parentFieldName)));
        $collection->addFieldToFilter(
            $this->childFieldName,
            ['in' => $ids]
        );
        $collection->addFieldToSelect($this->childFieldName);
        $subEntities = [];
        foreach ($this->fetchData($collection) as $row) {
            $id = $row[$this->childFieldName];
            foreach ($ids as $parentId => $childIds) {
                if (in_array($id, $childIds)) {
                    if (!isset($subEntities[$parentId])) {
                        $subEntities[$parentId] = [];
                    }

                    $subEntities[$parentId][] = $row;
                }
            }
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
        return $collection->getData();
    }

    protected function getChildIds(array $ids): array
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select();
        $select->from($this->resourceConnection->getTableName($this->connectTableName))
            ->columns([$this->connectParentFieldName, $this->connectChildFieldName])
            ->where($connection->quoteIdentifier($this->connectParentFieldName) . ' in (?)', $ids);

        $result = [];
        foreach ($connection->fetchAll($select) as $row) {
            if (!isset($result[$row[$this->connectParentFieldName]])) {
                $result[$row[$this->connectParentFieldName]] = [];
            }
            $result[$row[$this->connectParentFieldName]][] = $row[$this->connectChildFieldName];
        }

        return $result;
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
