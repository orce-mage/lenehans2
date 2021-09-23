<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Behavior\Add;

use Amasty\ImportCore\Api\Behavior\BehaviorResultInterface;
use Amasty\ImportCore\Api\BehaviorInterface;
use Amasty\ImportCore\Import\Behavior\Model as ModelBehavior;
use Magento\Framework\Model\AbstractModel;

class Model extends ModelBehavior implements BehaviorInterface
{
    public function execute(array &$data, ?string $customIdentifier = null): BehaviorResultInterface
    {
        $result = $this->resultFactory->create();
        $ids = [];
        foreach ($data as &$row) {
            /** @var AbstractModel $model */
            $model = $this->modelFactory->create();
            $model->setData($row);
            $model->unsetData($model->getIdFieldName());
            $this->save($model);
            $ids[] = $model->getId();
            $row[$this->getIdFieldName()] = $model->getId();
        }
        $result->setNewIds($ids);

        return $result;
    }
}
