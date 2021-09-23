<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Behavior;

use Amasty\ImportCore\Api\Behavior\BehaviorResultInterfaceFactory;
use Amasty\ImportCore\Import\Utils\DuplicateFieldChecker;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\ObjectManagerInterface;

abstract class Model
{
    protected $modelFactory = null;
    protected $repository = null;
    protected $idFieldName = null;

    protected $deleteCallback = null;
    protected $saveCallback = null;
    protected $loadCallback = null;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var null|\Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    private $insertResourceModel;

    /**
     * @var BehaviorResultInterfaceFactory
     */
    protected $resultFactory;

    /**
     * @var DuplicateFieldChecker
     */
    private $duplicateFieldChecker;

    public function __construct(
        ObjectManagerInterface $objectManager,
        BehaviorResultInterfaceFactory $behaviorResultFactory,
        DuplicateFieldChecker $duplicateFieldChecker,
        array $config
    ) {
        $this->config = $config;
        $this->modelFactory = $objectManager->get($config['modelFactory']);
        if (!method_exists($this->modelFactory, 'create')) {
            throw new \LogicException($config['modelFactory'] . ' is not a valid factory class');
        }

        if (!empty($config['insertResourceModel'])) {
            $this->insertResourceModel = $objectManager->create($config['insertResourceModel']);
        }

        if (!empty($config['repository']['class'])
            && $repositoryClass = $config['repository']['class']
        ) {
            $this->repository = $objectManager->get($repositoryClass);
        }
        $this->resultFactory = $behaviorResultFactory;
        $this->duplicateFieldChecker = $duplicateFieldChecker;
    }

    public function getIdFieldName(): string
    {
        if ($this->idFieldName === null) {
            /** @var AbstractModel $modelPrototype */
            $modelPrototype = $this->modelFactory->create();
            $this->idFieldName = $modelPrototype->getIdFieldName();
        }

        return $this->idFieldName;
    }

    private function initDelete()
    {
        if ($this->repository) {
            $deleteMethod = $this->config['repository']['deleteMethod'];
            if (method_exists($this->repository, $deleteMethod)) {
                $this->deleteCallback = [$this->repository, $deleteMethod];
            } else {
                throw new \LogicException(
                    'Repository "' . get_class($this->repository) . '" has no method "' . $deleteMethod . '"'
                );
            }
        } else {
            $this->deleteCallback = function (AbstractModel $model) {
                return $model->delete();
            };
        }
    }

    public function delete(AbstractModel $model)
    {
        if (!$this->deleteCallback) {
            $this->initDelete();
        }
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return call_user_func($this->deleteCallback, $model);
    }

    private function initLoad()
    {
        if ($this->repository) {
            $loadMethod = $this->config['repository']['loadMethod'];
            if (method_exists($this->repository, $loadMethod)) {
                $this->loadCallback = [$this->repository, $loadMethod];
            } else {
                throw new \LogicException(
                    'Repository "' . get_class($this->repository) . '" has no method "' . $loadMethod . '"'
                );
            }
        } else {
            $this->loadCallback = function (int $id) {
                /** @var AbstractModel $model */
                $model = $this->modelFactory->create();

                return $model->load($id);
            };
        }
    }

    /**
     * @param int $id
     * @return AbstractModel|null Always returns null when entity is not found
     */
    public function load(int $id)
    {
        if (!$this->loadCallback) {
            $this->initLoad();
        }

        try {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $model = call_user_func($this->loadCallback, $id);
            if (!$model->getId()) {
                return null;
            }

            return $model;
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    private function initSave()
    {
        if ($this->repository) {
            $saveMethod = $this->config['repository']['saveMethod'];
            if (method_exists($this->repository, $saveMethod)) {
                $this->saveCallback = [$this->repository, $saveMethod];
            } else {
                throw new \LogicException(
                    'Repository "' . get_class($this->repository) . '" has no method "' . $saveMethod . '"'
                );
            }
        } else {
            $this->saveCallback = function (AbstractModel $model) {
                return $model->save();
            };
        }
    }

    public function save(AbstractModel $model)
    {
        if (!$this->saveCallback) {
            $this->initSave();
        }
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return call_user_func($this->saveCallback, $model);
    }

    public function createWithInsertResourceModel(array $row)
    {
        if (!$this->insertResourceModel) {
            return null;
        }
        /** @var AbstractModel $model */
        $insertModel = $this->modelFactory->create();
        $insertModel->setId($row[$this->getIdFieldName()]);
        $this->insertResourceModel->save($insertModel);

        return $this->load((int)$row[$this->getIdFieldName()]);
    }

    protected function updateDataIdFields(array &$data, string $customIdentifier): void
    {
        /** @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource */
        $resource = $this->modelFactory->create()->getResource();
        $connection = $resource->getConnection();
        $mainTable = $resource->getMainTable();
        $idFieldName = $this->getIdFieldName();

        $newEntityIdsSelect = $connection->select()
            ->from($mainTable, [$customIdentifier, $idFieldName])
            ->where($customIdentifier . ' IN (?)', array_column($data, $customIdentifier));
        $newEntityIds = $connection->fetchPairs($newEntityIdsSelect);

        $existingEntityIdsSelect = $connection->select()
            ->from($mainTable, [$customIdentifier, $idFieldName])
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
            if ($existingId = $this->duplicateFieldChecker->getDuplicateRowId($mainTable, $row)) {
                $row[$idFieldName] = $existingId;
            }
        }
    }
}
