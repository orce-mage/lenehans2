<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Behavior\AddUpdate;

use Amasty\ImportCore\Api\Behavior\BehaviorObserverInterface;
use Amasty\ImportCore\Api\Behavior\BehaviorResultInterface;
use Amasty\ImportCore\Api\BehaviorInterface;
use Amasty\ImportCore\Import\Behavior\Table as TableBehavior;

class Table extends TableBehavior implements BehaviorInterface
{
    public function execute(array &$data, ?string $customIdentifier = null): BehaviorResultInterface
    {
        $result = $this->resultFactory->create();
        $preparedData = $this->prepareData($data);

        if (!$this->hasDataToInsert($preparedData)) {
            return $result;
        }

        if ($customIdentifier) {
            $this->updateDataIdFields($preparedData, $customIdentifier);
        }
        $this->serializeArrays($preparedData);
        $uniqueIds = $this->getUniqueIds($preparedData);
        $existingIds = $this->getExistingIds($uniqueIds);
        $connection = $this->getConnection();
        $connection->beginTransaction();
        try {
            $this->dispatchBehaviorEvent(
                BehaviorObserverInterface::BEFORE_APPLY,
                $preparedData
            );

            $connection->insertOnDuplicate($this->getTable(), $preparedData);

            foreach ($uniqueIds as $index => $id) { //todo: check with 1 update 1 new
                $data[$index][$this->getIdField()] = $id;
            }
            $result->setUpdatedIds(array_intersect($uniqueIds, $existingIds));
            $result->setNewIds(array_diff($uniqueIds, $existingIds));

            $this->dispatchBehaviorEvent(
                BehaviorObserverInterface::AFTER_APPLY,
                $data
            );

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        return $result;
    }

    protected function getExistingIds(array $filledIds)
    {
        $select = $this->resourceConnection->getConnection()->select()
            ->from($this->getTable(), [$this->getIdField()])
            ->where($this->getIdField() . ' IN (?)', $filledIds);

        return $this->resourceConnection->getConnection()->fetchCol($select);
    }
}
