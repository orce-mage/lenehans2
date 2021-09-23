<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Behavior\Update;

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

        $idField = $this->getIdField();
        $filledIds = $this->getUniqueIds($preparedData);

        $existingIds = $this->getExistingIds($idField, $filledIds);
        $this->serializeArrays($preparedData);

        $preparedData = array_filter(
            $preparedData,
            function ($row) use ($existingIds, $idField) {
                return !empty($row[$idField]) && in_array($row[$idField], $existingIds);
            }
        );

        if (!$this->hasDataToInsert($preparedData)) {
            return $result;
        }

        $connection = $this->getConnection();
        $connection->beginTransaction();
        try {
            $this->dispatchBehaviorEvent(
                BehaviorObserverInterface::BEFORE_APPLY,
                $preparedData
            );
            $connection->insertOnDuplicate($this->getTable(), $preparedData);
            $this->dispatchBehaviorEvent(
                BehaviorObserverInterface::AFTER_APPLY,
                $preparedData
            );

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        $result->setUpdatedIds($existingIds);

        return $result;
    }

    protected function getExistingIds(string $idField, $filledIds)
    {
        $select = $this->resourceConnection->getConnection()->select()
            ->from($this->getTable(), $idField)
            ->where($idField . ' IN (?)', $filledIds);

        return $this->resourceConnection->getConnection()->fetchCol($select);
    }
}
