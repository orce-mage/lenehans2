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
use Amasty\ImportCore\Import\Behavior\EavEntityManager as EavEntityManagerBehavior;
use Magento\Framework\EntityManager\Operation\AttributeInterface;

class EavEntityManager extends EavEntityManagerBehavior implements BehaviorInterface
{
    public function execute(array &$data, ?string $customIdentifier = null): BehaviorResultInterface
    {
        /** @var BehaviorResultInterface $result */
        $result = $this->resultFactory->create();

        /** @var AttributeInterface[] $actions */
        $actions = $this->attributePool->getActions($this->entityType, 'update');
        foreach ($this->prepareAttributeValues($data) as $row) {
            $this->applyActions($actions, $row);
        }

        return $result;
    }
}
