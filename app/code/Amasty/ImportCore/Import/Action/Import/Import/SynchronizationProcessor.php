<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Import\Action\Import\Import;

use Amasty\ImportCore\Api\Config\Entity\Field\Configuration\SyncFieldInterface;
use Amasty\ImportCore\Import\Config\EntityConfigProvider;
use Amasty\ImportCore\Import\Source\SourceDataStructure;
use Magento\Framework\Stdlib\ArrayManager;

class SynchronizationProcessor
{
    /**
     * @var EntityConfigProvider
     */
    private $entityConfigProvider;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var BehaviorProvider
     */
    private $behaviorProvider;

    /**
     * @var array
     */
    private $syncIdsMapping = [];

    public function __construct(
        EntityConfigProvider $entityConfigProvider,
        ArrayManager $arrayManager,
        BehaviorProvider $behaviorProvider
    ) {
        $this->entityConfigProvider = $entityConfigProvider;
        $this->arrayManager = $arrayManager;
        $this->behaviorProvider = $behaviorProvider;
    }

    public function setOrigIds(string $entityCode, array $data): void
    {
        if (!($synchronizationData = $this->getSynchronizationData($entityCode))) {
            return;
        }

        $this->syncIdsMapping = [];//reset, one row at a time
        foreach ($synchronizationData as $syncFields) {
            /** @var SyncFieldInterface $syncField */
            foreach ($syncFields as $syncField) {
                foreach ($data as $row) {
                    if (isset($row[$syncField->getSynchronizationFieldName()])) {
                        $this->syncIdsMapping[$syncField->getEntityName()]
                        [$syncField->getFieldName()][] = $row[$syncField->getSynchronizationFieldName()];
                    }
                }
            }
        }
    }

    public function processSynchronizations(array &$row, string $synchronizationEntity, array $behaviorResults)
    {
        if (!($synchronizationData = $this->getSynchronizationData($synchronizationEntity))) {
            return;
        }

        foreach ($synchronizationData as $synchronization) {
            /** @var SyncFieldInterface $synchronizationField */
            foreach ($synchronization as $synchronizationField) {
                $entityName = $synchronizationField->getEntityName();
                $entityToUpdate = $this->getEntityToUpdate(
                    $row,
                    $entityName,
                    isset($behaviorResults[$entityName])
                );
                if (!$entityToUpdate) {
                    continue;
                }

                $this->updateIdsMapping($row, $synchronizationField, $synchronizationEntity);
                $entityUpdated = false;
                $fieldName = $synchronizationField->getFieldName();
                $idsMap = $this->syncIdsMapping[$synchronizationField->getEntityName()][$fieldName] ?? [];
                foreach ($entityToUpdate as &$rowToUpdate) {
                    if (isset($rowToUpdate[$fieldName], $idsMap[$rowToUpdate[$fieldName]])) {
                        $entityUpdated = true;
                        $rowToUpdate[$fieldName] = $idsMap[$rowToUpdate[$fieldName]];
                    }
                }

                if ($entityUpdated) {
                    if (isset($behaviorResults[$entityName])) {
                        $behaviorForUpdate = $this->behaviorProvider->getBehavior(
                            $entityName,
                            'update_direct',
                            true
                        );
                        $behaviorForUpdate->execute($entityToUpdate);
                    }
                    $this->setUpdatedEntity(
                        $row,
                        $entityToUpdate,
                        $entityName,
                        isset($behaviorResults[$entityName])
                    );
                }
            }
        }
    }

    public function adjustSubEntityOrigId(
        string $entityCode,
        array &$row,
        string $fieldName,
        $id
    ): void {
        if (!($synchronizationData = $this->getSynchronizationData($entityCode))) {
            return;
        }

        foreach ($synchronizationData as $syncFields) {
            /** @var SyncFieldInterface $syncField */
            foreach ($syncFields as $syncField) {
                if ($syncField->getSynchronizationFieldName() != $fieldName) {
                    continue;
                }

                $entityName = $syncField->getEntityName();
                $entities = $this->getEntityToUpdate($row, $entityName, false);
                if (!$entities) {
                    continue;
                }

                $entitiesUpdated = false;
                $fieldNameToUpdate = $syncField->getFieldName();
                foreach ($entities as &$entity) {
                    if (isset($entity[$fieldNameToUpdate])) {
                        $entity[$fieldNameToUpdate] = $id;
                        $entitiesUpdated = true;
                    }
                }
                if ($entitiesUpdated) {
                    $this->setUpdatedEntity(
                        $row,
                        $entities,
                        $entityName,
                        false
                    );
                }
            }
        }
    }

    private function updateIdsMapping(
        array $row,
        SyncFieldInterface $synchronizationField,
        string $synchronizationEntity
    ): void {
        $newIds = $updatedMapping = [];

        foreach ($row[SourceDataStructure::SUB_ENTITIES_DATA_KEY][$synchronizationEntity] as $subRow) {
            $newIds[] = $subRow[$synchronizationField->getSynchronizationFieldName()];
        }
        if (empty($newIds)) {
            return;
        }

        $syncIds = &$this->syncIdsMapping[$synchronizationField->getEntityName()]
            [$synchronizationField->getFieldName()];
        foreach ($newIds as $id) {
            $updatedMapping[current($syncIds)] = $id;
            next($syncIds);
        }
        $syncIds = $updatedMapping;
    }

    private function getSynchronizationData(string $entityCode): array
    {
        $fieldsConfig = $this->entityConfigProvider->get($entityCode)->getFieldsConfig();
        $synchronizationData = [];

        foreach ($fieldsConfig->getFields() as $field) {
            if (!($synchronization = $field->getSynchronization())) {
                continue;
            }
            foreach ($synchronization as $synchronizationField) {
                $synchronizationField->setSynchronizationFieldName($field->getName());
            }
            $synchronizationData[] = $synchronization;
        }

        return $synchronizationData;
    }

    private function getEntityToUpdate(array $row, string $entityName, bool $isMainEntity): ?array
    {
        $rowToUpdatePath = $this->arrayManager->findPath($entityName, $row);

        if ($rowToUpdatePath) {
            return $this->arrayManager->get($rowToUpdatePath, $row);
        } elseif ($isMainEntity) { //main entity
            return [$row];
        }

        return null;
    }

    private function setUpdatedEntity(array &$row, array $updatedEntity, string $entityName, bool $isMainEntity): void
    {
        $rowToUpdatePath = $this->arrayManager->findPath($entityName, $row);

        if ($rowToUpdatePath) {
            $row = $this->arrayManager->merge($rowToUpdatePath, $row, $updatedEntity);
        } elseif ($isMainEntity) { //main entity
            $row = array_merge($row, $updatedEntity[0]);
        }
    }
}
