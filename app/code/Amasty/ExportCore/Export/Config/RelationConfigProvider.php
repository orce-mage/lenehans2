<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\Config;

use Amasty\ExportCore\Api\Config\Relation\RelationInterface;
use Magento\Framework\Event\ManagerInterface;

class RelationConfigProvider
{
    /**
     * @var array
     */
    private $relationsConfig = [];

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var RelationSource\RelationSourceInterface[]
     */
    private $relationSources;

    /**
     * @var array
     */
    private $preparedRelations;

    /**
     * @var EntityConfigProvider
     */
    private $entityConfigProvider;

    public function __construct(
        ManagerInterface $eventManager,
        EntityConfigProvider $entityConfigProvider,
        array $relationSources
    ) {
        $this->eventManager = $eventManager;
        $this->relationSources = $relationSources;
        $this->entityConfigProvider = $entityConfigProvider;
    }

    /**
     * @param string $entityCode
     *
     * @return RelationInterface[]
     */
    public function get(string $entityCode): array
    {
        if (!isset($this->relationsConfig[$entityCode])) {
            if ($this->preparedRelations === null) {
                $this->preparedRelations = $this->getRelationsConfig();
            }
            $entityRelations = $this->prepareEntityRelations($entityCode);

            //extension point
            $this->eventManager->dispatch(
                'amexport_relations_prepared',
                ['entity_code' => $entityCode, 'relations' => $entityRelations]
            );

            $this->relationsConfig[$entityCode] = $entityRelations;
        }

        return $this->relationsConfig[$entityCode];
    }

    protected function getRelationsConfig()
    {
        $result = [];
        foreach ($this->relationSources as $relationSource) {
            $result[] = $relationSource->get();
        }
        $result = empty($result) ? [] : array_merge_recursive(...$result);

        $preparedRelations = [];
        foreach ($result as $entityCode => $relationConfig) {
            try {
                $this->entityConfigProvider->get($entityCode);
            } catch (\LogicException $e) {
                continue;
            }
            $preparedRelationConfig = [];
            /** @var RelationInterface $relation */
            foreach ($relationConfig as $relation) {
                try {
                    $this->entityConfigProvider->get($relation->getChildEntityCode());
                } catch (\LogicException $e) {
                    continue;
                }
                $preparedRelationConfig[$relation->getSubEntityFieldName()] = $relation;
            }
            if (!empty($preparedRelationConfig)) {
                $preparedRelations[$entityCode] = $preparedRelationConfig;
            }
        }

        return $preparedRelations;
    }

    protected function prepareEntityRelations(string $entityCode): array
    {
        if (empty($this->preparedRelations[$entityCode])) {
            return [];
        }

        $relations = [];
        /** @var RelationInterface $relation */
        foreach ($this->preparedRelations[$entityCode] as $relation) {
            $outputRelation = clone $relation;
            if (!empty($this->preparedRelations[$relation->getChildEntityCode()])) {
                $this->processRelations(
                    $outputRelation,
                    $this->preparedRelations[$relation->getChildEntityCode()],
                    [$entityCode, $relation->getChildEntityCode()]
                );
            }
            $relations[] = $outputRelation;
        }

        return $relations;
    }

    /**
     * @param RelationInterface $relationConfig
     * @param RelationInterface[] $relations
     * @param array $skipPath
     */
    protected function processRelations(RelationInterface $relationConfig, array $relations, array $skipPath)
    {
        $levelRelations = [];
        foreach ($relations as $relation) {
            $outputRelation = clone $relation;
            if (in_array($outputRelation->getChildEntityCode(), $skipPath)) {
                continue;
            }

            if (!empty($this->preparedRelations[$relation->getChildEntityCode()])) {
                $this->processRelations(
                    $outputRelation,
                    $this->preparedRelations[$relation->getChildEntityCode()],
                    //phpcs:ignore
                    array_merge($skipPath, [$relation->getChildEntityCode()])
                );
            }

            $levelRelations[] = $outputRelation;
        }

        $relationConfig->setRelations($levelRelations);
    }
}
