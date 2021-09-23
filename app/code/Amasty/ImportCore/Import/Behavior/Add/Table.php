<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Behavior\Add;

use Amasty\ImportCore\Api\Behavior\BehaviorObserverInterface;
use Amasty\ImportCore\Api\Behavior\BehaviorResultInterface;
use Amasty\ImportCore\Api\BehaviorInterface;
use Amasty\ImportCore\Import\Behavior\Table as TableBehavior;

class Table extends TableBehavior implements BehaviorInterface
{
    public function execute(array &$data, ?string $customIdentifier = null): BehaviorResultInterface
    {
        $result = $this->resultFactory->create();
        $preparedData = $this->unsetColumns(
            $this->prepareData($data),
            [$this->getIdField()]
        );

        if (!$this->hasDataToInsert($preparedData)) {
            return $result;
        }

        $this->serializeArrays($preparedData);

        $connection = $this->getConnection();
        $connection->beginTransaction();
        try {
            $this->dispatchBehaviorEvent(
                BehaviorObserverInterface::BEFORE_APPLY,
                $preparedData
            );

            $affectedRows = $connection->insertMultiple($this->getTable(), $preparedData);
            $insertId = $connection->lastInsertId();
            if ($affectedRows > 0) {
                $range = range($insertId, $insertId + $affectedRows - 1);
                foreach ($range as $index => $id) {
                    $data[$index][$this->getIdField()] = $id;
                }
                $result->setNewIds($range);
            }

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
}
