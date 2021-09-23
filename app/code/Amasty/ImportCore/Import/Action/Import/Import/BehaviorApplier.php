<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Import\Action\Import\Import;

use Amasty\ImportCore\Api\Behavior\BehaviorResultInterface;
use Amasty\ImportCore\Api\Config\Profile\EntitiesConfigInterface;
use Amasty\ImportCore\Api\Config\ProfileConfigInterface;
use Amasty\ImportCore\Import\Config\RelationConfigProvider;
use Amasty\ImportCore\Import\Source\SourceDataStructure;

class BehaviorApplier
{
    const SYNC_BEHAVIORS_TO_SKIP = [
        'delete',
        'delete_direct'
    ];

    /**
     * @var BehaviorProvider
     */
    private $behaviorProvider;

    /**
     * @var RelationConfigProvider
     */
    private $relationConfigProvider;

    /**
     * @var SynchronizationProcessor
     */
    private $synchronizationProcessor;

    /**
     * @var array
     */
    private $behaviorResults = [];

    public function __construct(
        BehaviorProvider $behaviorProvider,
        RelationConfigProvider $relationConfigProvider,
        SynchronizationProcessor $synchronizationProcessor
    ) {
        $this->behaviorProvider = $behaviorProvider;
        $this->relationConfigProvider = $relationConfigProvider;
        $this->synchronizationProcessor = $synchronizationProcessor;
    }

    public function apply(array &$data, ProfileConfigInterface $profileConfig): array
    {
        $this->behaviorResults = [];
        $profileEntitiesConfig = $profileConfig->getEntitiesConfig();

        $behavior = $this->behaviorProvider->getBehavior(
            $profileEntitiesConfig->getEntityCode(),
            $profileEntitiesConfig->getBehavior()
        );
        $result = $behavior->execute($data, $profileConfig->getEntityIdentifier());
        $this->behaviorResults[$profileEntitiesConfig->getEntityCode()] = $result;

        $this->executeSubEntities($profileEntitiesConfig, $data, $result->getAffectedIds());
        $this->processBehaviorResults($profileEntitiesConfig);

        return $this->behaviorResults;
    }

    private function executeSubEntities(
        EntitiesConfigInterface $entitiesConfig,
        array &$data,
        array $parentIds
    ): void {
        foreach ($data as $index => &$row) {
            if (!isset($row[SourceDataStructure::SUB_ENTITIES_DATA_KEY])) {
                continue;
            }
            $rowSubEntities = &$row[SourceDataStructure::SUB_ENTITIES_DATA_KEY];

            foreach ($entitiesConfig->getSubEntitiesConfig() as $subEntityConfig) {
                if (!isset($rowSubEntities[$subEntityConfig->getEntityCode()])) {
                    continue;
                }
                $parentEntityCode = $entitiesConfig->getEntityCode();
                $childEntityCode = $subEntityConfig->getEntityCode();

                $this->updateRelationFields(
                    $parentEntityCode,
                    $childEntityCode,
                    $rowSubEntities,
                    $parentIds,
                    $index
                );
                $this->synchronizationProcessor->setOrigIds($childEntityCode, $rowSubEntities[$childEntityCode]);

                $behavior = $this->behaviorProvider->getBehavior(
                    $childEntityCode,
                    $subEntityConfig->getBehavior(),
                    true
                );
                $subEntityResult = $behavior->execute($rowSubEntities[$childEntityCode]);

                if (isset($this->behaviorResults[$childEntityCode])) {
                    $subEntityResult->merge($this->behaviorResults[$childEntityCode]);
                }
                $this->behaviorResults[$childEntityCode] = $subEntityResult;

                if (!in_array($entitiesConfig->getBehavior(), self::SYNC_BEHAVIORS_TO_SKIP)) {
                    $this->synchronizationProcessor->processSynchronizations(
                        $row,
                        $childEntityCode,
                        $this->behaviorResults
                    );
                }

                if (!empty($subEntityConfig->getSubEntitiesConfig())) {
                    $this->executeSubEntities(
                        $subEntityConfig,
                        $rowSubEntities[$childEntityCode],
                        $subEntityResult->getAffectedIds()
                    );
                }
            }
        }
    }

    private function updateRelationFields(
        string $parentEntityCode,
        string $childEntityCode,
        array &$rowSubEntities,
        array $parentIds,
        int $index
    ): void {
        $relationConfig = $this->relationConfigProvider->getExact(
            $parentEntityCode,
            $childEntityCode
        );
        // TODO: replace isSkipRelationFieldsUpdate() method to behavior's getAffectedIds() with autoincrement field
        if ($relationConfig && !$relationConfig->isSkipRelationFieldsUpdate()) {
            $childFieldName = $relationConfig->getChildFieldName();
            if ($childFieldName) {
                foreach ($rowSubEntities[$childEntityCode] as &$entityRow) {
                    if (isset($parentIds[$index])) {
                        $entityRow[$childFieldName] = $parentIds[$index];
                        $this->synchronizationProcessor->adjustSubEntityOrigId(
                            $childEntityCode,
                            $entityRow,
                            $childFieldName,
                            $parentIds[$index]
                        );
                    }
                }
            }
        }
    }

    private function processBehaviorResults(EntitiesConfigInterface $entitiesConfig): void
    {
        $mainEntityCode = $entitiesConfig->getEntityCode();
        if (count($this->behaviorResults[$mainEntityCode]->getAffectedIds()) > 0) {
            return;
        }

        $mainEntityResult = $this->behaviorResults[$mainEntityCode];
        foreach ($entitiesConfig->getSubEntitiesConfig() as $subEntityConfig) {
            $this->processSubEntitiesResult($mainEntityResult, $subEntityConfig);
        }
    }

    private function processSubEntitiesResult(
        BehaviorResultInterface $mainEntityResult,
        EntitiesConfigInterface $entitiesConfig
    ): void {
        $subEntityCode = $entitiesConfig->getEntityCode();

        if (!isset($this->behaviorResults[$subEntityCode])) {
            foreach ($entitiesConfig->getSubEntitiesConfig() as $subEntityConfig) {
                $this->processSubEntitiesResult($mainEntityResult, $subEntityConfig);
            }
            return;
        }

        $subEntityResult = $this->behaviorResults[$subEntityCode];
        if (count($subEntityResult->getAffectedIds()) > 0) {
            $mainEntityResult->setNewIds(
                array_merge($mainEntityResult->getNewIds(), $subEntityResult->getNewIds())
            );
            $mainEntityResult->setUpdatedIds(
                array_merge($mainEntityResult->getUpdatedIds(), $subEntityResult->getUpdatedIds())
            );
            $mainEntityResult->setDeletedIds(
                array_merge($mainEntityResult->getDeletedIds(), $subEntityResult->getDeletedIds())
            );

            return;
        }

        foreach ($entitiesConfig->getSubEntitiesConfig() as $subEntityConfig) {
            $this->processSubEntitiesResult($mainEntityResult, $subEntityConfig);
        }
    }
}
