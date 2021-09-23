<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Behavior\Delete;

use Amasty\ImportCore\Api\Behavior\BehaviorObserverInterface;
use Amasty\ImportCore\Api\Behavior\BehaviorResultInterface;
use Amasty\ImportCore\Api\BehaviorInterface;
use Amasty\ImportCore\Import\Behavior\Table as TableBehavior;

class Table extends TableBehavior implements BehaviorInterface
{
    public function execute(array &$data, ?string $customIdentifier = null): BehaviorResultInterface
    {
        $result = $this->resultFactory->create();

        $ids = $this->getIdsForDelete($data, $customIdentifier);
        if ($ids) {
            $connection = $this->getConnection();
            $connection->beginTransaction();
            try {
                $this->dispatchBehaviorEvent(BehaviorObserverInterface::BEFORE_APPLY, $ids);
                $connection->delete(
                    $this->getTable(),
                    $connection->quoteInto($this->getIdField() . ' IN (?)', $ids)
                );
                $this->dispatchBehaviorEvent(BehaviorObserverInterface::AFTER_APPLY, $ids);

                $connection->commit();
            } catch (\Exception $e) {
                $connection->rollBack();
                throw $e;
            }
        }
        $result->setDeletedIds($ids);

        return $result;
    }

    private function getIdsForDelete(array &$data, ?string $customIdentifier = null): array
    {
        $preparedData = $data;
        if ($customIdentifier) {
            $this->updateDataIdFields($preparedData, $customIdentifier);
        }

        $uniqueIds = $this->getUniqueIds($preparedData);
        $connection = $this->resourceConnection->getConnection();
        $idFieldName = $this->getIdField();

        $select = $connection->select()
            ->from($this->getTable(), [$idFieldName])
            ->where($idFieldName . ' IN (?)', $uniqueIds);

        return $connection->fetchCol($select);
    }
}
