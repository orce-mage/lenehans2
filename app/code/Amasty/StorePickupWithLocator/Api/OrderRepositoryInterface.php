<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Api;

use Amasty\StorePickupWithLocator\Api\Data\OrderInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;

interface OrderRepositoryInterface
{
    /**
     * @param OrderInterface $orderModel
     * @return OrderInterface
     * @throws CouldNotSaveException
     */
    public function save(OrderInterface $orderModel);

    /**
     * @param int $itemId
     * @return OrderInterface
     * @throws NoSuchEntityException
     */
    public function get($itemId);

    /**
     * @param OrderInterface $orderModel
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(OrderInterface $orderModel);

    /**
     * @param int $itemId
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteById($itemId);

    /**
     * @param int $orderId
     * @return OrderInterface
     */
    public function getByOrderId($orderId);
}
