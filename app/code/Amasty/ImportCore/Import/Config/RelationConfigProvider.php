<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Import\Config;

use Amasty\ImportCore\Api\Config\Relation\RelationConfigInterface;
use Magento\Framework\Event\ManagerInterface;

class RelationConfigProvider
{
    /**
     * @var array
     */
    private $relationsConfig = [];

    /**
     * @var array
     */
    private $relationsConfigByParentChild = [];

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
     * @return RelationConfigInterface[]
     */
    public function get(string $entityCode): array
    {
        if (!isset($this->relationsConfig[$entityCode])) {
            if ($this->preparedRelations === null) {
                $this->preparedRelations = $this->getRelationsConfig();
            }
            $entityRelations = $this->prepareEntityRelations($entityCode);

            // Extension point
            $this->eventManager->dispatch(
                'amimport_relations_prepared',
                [
                    'entity_code' => $entityCode,
                    'relations' => $entityRelations
                ]
            );

            $this->relationsConfig[$entityCode] = $entityRelations;
        }

        return $this->relationsConfig[$entityCode];
    }

    /**
     * Get relation config using parent entity code and child entity code
     *
     * @param string $parentEntityCode
     * @param string $childEntityCode
     * @return RelationConfigInterface|null
     */
    public function getExact(string $parentEntityCode, string $childEntityCode): ?RelationConfigInterface
    {
        if (!isset($this->relationsConfigByParentChild[$parentEntityCode])) {
            $this->relationsConfigByParentChild[$parentEntityCode] = [];

            $relationConfigs = $this->get($parentEntityCode);
            foreach ($relationConfigs as $relationConfig) {
                $this->relationsConfigByParentChild[$parentEntityCode]
                [$relationConfig->getChildEntityCode()] = $relationConfig;
            }
        }

        return $this->relationsConfigByParentChild[$parentEntityCode][$childEntityCode] ?? null;
    }

    protected function getRelationsConfig(): array
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
            /** @var RelationConfigInterface $relation */
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
        /** @var RelationConfigInterface $relation */
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
     * @param RelationConfigInterface $relationConfig
     * @param RelationConfigInterface[] $relations
     * @param array $skipPath
     */
    protected function processRelations(
        RelationConfigInterface $relationConfig,
        array $relations,
        array $skipPath
    ) {
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
