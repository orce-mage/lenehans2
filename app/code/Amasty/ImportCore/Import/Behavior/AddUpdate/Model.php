<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Behavior\AddUpdate;

use Amasty\ImportCore\Api\Behavior\BehaviorResultInterface;
use Amasty\ImportCore\Api\BehaviorInterface;
use Amasty\ImportCore\Import\Behavior\Model as ModelBehavior;
use Magento\Framework\Model\AbstractModel;

class Model extends ModelBehavior implements BehaviorInterface
{
    public function execute(array &$data, ?string $customIdentifier = null): BehaviorResultInterface
    {
        $preparedData = $data;
        if ($customIdentifier) {
            $this->updateDataIdFields($preparedData, $customIdentifier);
        }

        $result = $this->resultFactory->create();
        $idFieldName = $this->getIdFieldName();
        $newIds = $updatedIds = $allIds = [];
        foreach ($preparedData as $row) {
            if (!empty($row[$idFieldName])) {
                $model = $this->load((int)$row[$idFieldName]);
                if ((!isset($model) || !$model)) {
                    $model = $this->createWithInsertResourceModel($row);
                    if ($model) {
                        $idField = $model->getData($model->getIdFieldName());
                        $newIds[] = $idField;
                        $allIds[] = $idField;
                    }
                } else {
                    $idField = $model->getData($model->getIdFieldName());
                    $updatedIds[] = $idField;
                    $allIds[] = $idField;
                }
            }

            if (!isset($model) || !$model) {
                /** @var AbstractModel $model */
                $model = $this->modelFactory->create();
                $model->setData($row);
                $model->unsetData($model->getIdFieldName());
                $this->save($model);
                $idField = $model->getData($model->getIdFieldName());
                $newIds[] = $idField;
                $allIds[] = $idField;
            } else {
                $model->setData($row);
                $this->save($model);
            }
        }
        foreach ($allIds as $index => $id) {
            $data[$index][$this->getIdFieldName()] = $id;
        }
        $result->setNewIds($newIds);
        $result->setUpdatedIds($updatedIds);

        return $result;
    }
}
