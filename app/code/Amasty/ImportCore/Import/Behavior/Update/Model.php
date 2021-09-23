<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Behavior\Update;

use Amasty\ImportCore\Api\Behavior\BehaviorResultInterface;
use Amasty\ImportCore\Api\BehaviorInterface;
use Amasty\ImportCore\Import\Behavior\Model as ModelBehavior;

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
        $ids = [];
        foreach ($data as $row) {
            if (empty($row[$idFieldName])) {
                continue;
            }
            $model = $this->load((int)$row[$idFieldName]);

            if (!$model) {
                $model = $this->createWithInsertResourceModel($row);
            }

            if (!$model) {
                continue;
            }
            $model->setData($row);
            $this->save($model);
            $ids[] = $model->getId();
        }
        $result->setUpdatedIds($ids);

        return $result;
    }
}
