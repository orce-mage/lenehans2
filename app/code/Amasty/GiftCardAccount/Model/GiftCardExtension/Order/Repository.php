<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardExtension\Order;

use Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface;
use Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterfaceFactory;
use Amasty\GiftCardAccount\Api\GiftCardOrderRepositoryInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class Repository implements GiftCardOrderRepositoryInterface
{
    /**
     * @var GiftCardOrderInterfaceFactory
     */
    private $orderFactory;

    /**
     * @var ResourceModel\Order
     */
    private $resource;

    /**
     * @var ResourceModel\CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var array
     */
    private $orders;

    public function __construct(
        GiftCardOrderInterfaceFactory $orderFactory,
        ResourceModel\Order $resource,
        ResourceModel\CollectionFactory $orderCollectionFactory
    ) {
        $this->orderFactory = $orderFactory;
        $this->resource = $resource;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    public function getById(int $entityId): GiftCardOrderInterface
    {
        if (!isset($this->orders[$entityId])) {
            /** @var GiftCardOrderInterface $order */
            $order = $this->orderFactory->create();
            $this->resource->load($order, $entityId);

            if (!$order->getEntityId()) {
                throw new NoSuchEntityException(__('Gift Card Order with specified ID "%1" not found.', $entityId));
            }
            $this->orders[$entityId] = $order;
        }

        return $this->orders[$entityId];
    }

    public function getByOrderId(int $orderId): GiftCardOrderInterface
    {
        $collection = $this->orderCollectionFactory->create()
            ->addFieldToFilter(GiftCardOrderInterface::ORDER_ID, $orderId);
        $order = $collection->getFirstItem();

        if (!$order->getId()) {
            throw new NoSuchEntityException(__('Gift Card Order not found.'));
        }

        return $this->getById((int)$order->getEntityId());
    }

    public function save(GiftCardOrderInterface $order): GiftCardOrderInterface
    {
        try {
            $this->resource->save($order);
        } catch (\Exception $e) {
            if ($order->getEntityId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save Gift Card Order with ID %1. Error: %2',
                        [$order->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new Gift Card Order. Error: %1', $e->getMessage()));
        }

        return $order;
    }

    public function delete(GiftCardOrderInterface $order): bool
    {
        try {
            $this->resource->delete($order);
            unset($this->orders[$order->getEntityId()]);
        } catch (\Exception $e) {
            if ($order->getEntityId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove Gift Card Order with ID %1. Error: %2',
                        [$order->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove Gift Card Order. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @return GiftCardOrderInterface
     */
    public function getEmptyOrderModel(): GiftCardOrderInterface
    {
        return $this->orderFactory->create();
    }
}
