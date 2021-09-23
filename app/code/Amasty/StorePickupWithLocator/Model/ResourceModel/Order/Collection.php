<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Model\ResourceModel\Order;

use Amasty\StorePickupWithLocator\Api\Data\OrderInterface;
use Amasty\StorePickupWithLocator\Model\Order;
use Amasty\StorePickupWithLocator\Model\ResourceModel\Order as OrderResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection for Order
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = OrderInterface::ID;

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(Order::class, OrderResource::class);
    }
}
