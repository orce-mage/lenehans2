<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Behavior;

use Amasty\Base\Model\Serializer;
use Amasty\ImportCore\Api\Behavior\BehaviorObserverInterface;
use Amasty\ImportCore\Api\Behavior\BehaviorResultInterfaceFactory;
use Amasty\ImportCore\Import\Utils\DuplicateFieldChecker;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\ObjectManagerInterface;

abstract class Table
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var BehaviorResultInterfaceFactory
     */
    protected $resultFactory;

    /**
     * @var DuplicateFieldChecker
     */
    protected $duplicateFieldChecker;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var string
     */
    protected $idField;

    /**
     * @var BehaviorObserverInterface[][]
     */
    private $observersByEventType = [];

    public function __construct(
        ObjectManagerInterface $objectManager,
        ResourceConnection $resourceConnection,
        Serializer $serializer,
        BehaviorResultInterfaceFactory $behaviorResultFactory,
        DuplicateFieldChecker $duplicateFieldChecker,
        array $config
    ) {
        $this->tableName = $config['tableName'];
        $this->objectManager = $objectManager;
        $this->resourceConnection = $resourceConnection;
        $this->serializer = $serializer;
        $this->resultFactory = $behaviorResultFactory;
        $this->duplicateFieldChecker = $duplicateFieldChecker;
        $this->config = $config;
    }

    /**
     * Get entity connection
     *
     * @return AdapterInterface
     */
    protected function getConnection()
    {
        return $this->resourceConnection->getConnection();
    }

    protected function getTable()
    {
        return $this->resourceConnection->getTableName($this->tableName);
    }

    protected function getUniqueIds(array &$data)
    {
        return array_filter(array_unique(array_column($data, $this->getIdField())));
    }

    protected function getMaxId(): int
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable(), 'MAX(' . $this->getIdField() . ')')
            ->limit(1);

        return (int)$this->getConnection()->fetchOne($select);
    }

    protected function getNewIds(int $minId): array
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable(), $this->getIdField())
            ->where($this->getIdField() . ' > ' . $minId);

        return $this->getConnection()->fetchCol($select);
    }

    /**
     * @return bool|string
     */
    protected function getIdField()
    {
        if ($this->idField === null) {
            $this->idField = $this->getConnection()->getAutoIncrementField(
                $this->getTable()
            ) ?: $this->config['idField'] ?? null;
        }

        return $this->idField;
    }

    protected function serializeArrays(array &$preparedData): void
    {
        foreach ($preparedData as &$row) {
            foreach ($row as $key => $column) {
                if (is_array($column)) {
                    $row[$key] = $this->serializer->serialize($column);
                }
            }
        }
    }

    protected function updateDataIdFields(array &$data, string $customIdentifier): void
    {
        $connection = $this->getConnection();
        $idFieldName = $this->getIdField();
        $tableName = $this->getTable();

        $newEntityIdsSelect = $connection->select()
            ->from($tableName, [$customIdentifier, $idFieldName])
            ->where($customIdentifier . ' IN (?)', array_column($data, $customIdentifier));
        $newEntityIds = $connection->fetchPairs($newEntityIdsSelect);

        $existingEntityIdsSelect = $connection->select()
            ->from($tableName, [$customIdentifier, $idFieldName])
            ->where($idFieldName . ' IN (?)', array_column($data, $idFieldName));
        $existingEntityIds = $connection->fetchPairs($existingEntityIdsSelect);

        foreach ($data as &$row) {
            $currentEntityId = $row[$idFieldName] ?? 0;
            if (in_array($currentEntityId, $existingEntityIds)
                && ($newEntityIds[$currentEntityId] ?? 0) != ($existingEntityIds[$currentEntityId] ?? 0)
            ) {
                unset($row[$idFieldName]);
            }
            if (!empty($row[$customIdentifier]) && $newEntityId = $newEntityIds[$row[$customIdentifier]] ?? null) {
                $row[$idFieldName] = $newEntityId;
            }
            if ($existingId = $this->duplicateFieldChecker->getDuplicateRowId($tableName, $row)) {
                $row[$idFieldName] = $existingId;
            }
        }
    }

    protected function prepareData(array $data)
    {
        if (empty($data)) {
            return $data;
        }

        $columns = $this->getConnection()->describeTable($this->getTable());
        $columnsToUnset = current($data) ? array_keys(current($data)) : [];
        foreach ($columns as $column => $value) {
            if (false !== $key = array_search($column, $columnsToUnset)) {
                unset($columnsToUnset[$key]);
            }
        }

        if (!empty($columnsToUnset)) {
            $data = $this->unsetColumns($data, $columnsToUnset);
        }

        return $data;
    }

    protected function unsetColumns(array $data, array $columns): array
    {
        foreach ($data as &$row) {
            foreach ($columns as $column) {
                unset($row[$column]);
            }
        }

        return $data;
    }

    protected function hasDataToInsert(array $data): bool
    {
        return count(current($data) ?: []) >= 1;
    }

    /**
     * Dispatch behavior event
     *
     * @param string $eventType
     * @param array $data
     * @return void
     */
    protected function dispatchBehaviorEvent(string $eventType, array &$data): void
    {
        foreach ($this->getEventObservers($eventType) as $observer) {
            $observer->execute($data);
        }
    }

    /**
     * Get behavior observers for specified event type
     *
     * @param string $eventType
     * @return BehaviorObserverInterface[]
     */
    private function getEventObservers(string $eventType): array
    {
        if (!isset($this->observersByEventType[$eventType])) {
            $this->observersByEventType[$eventType] = [];

            $entityType = $this->config['entityType'] ?? null;
            $observers = $this->config['events'][$eventType] ?? [];
            foreach ($observers as $observerConfig) {
                /** @var BehaviorObserverInterface $observerInstance */
                $observerInstance =
                    $this->objectManager->create($observerConfig['class'], ['config' => ['entityType' => $entityType]]);
                if (!$observerInstance instanceof BehaviorObserverInterface) {
                    throw new \LogicException($observerConfig['class'] . ' is not a valid behavior observer class');
                }

                $this->observersByEventType[$eventType][] = $observerInstance;
            }
        }

        return $this->observersByEventType[$eventType];
    }
}
