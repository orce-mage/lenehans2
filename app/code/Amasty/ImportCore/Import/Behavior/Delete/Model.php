<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Behavior\Delete;

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
        $ids = [];
        foreach ($data as $row) {
            if (empty($row[$idFieldName])) {
                continue;
            }
            $model = $this->modelFactory->create();
            $model->load((int)$row[$idFieldName]);

            if (!$model->getId()) {
                continue;
            }
            $model->setData($row);
            $this->delete($model);
            $ids[] = $model->getId();
        }
        $result->setDeletedIds($ids);

        return $result;
    }
}
