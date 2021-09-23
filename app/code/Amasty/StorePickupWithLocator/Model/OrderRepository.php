<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Model;

use Amasty\StorePickupWithLocator\Api\Data\OrderInterface;
use Amasty\StorePickupWithLocator\Api\OrderRepositoryInterface;
use Amasty\StorePickupWithLocator\Model\ResourceModel\Order as OrderResource;
use Amasty\StorePickupWithLocator\Model\OrderFactory;
use Magento\Framework\Config\Dom\ValidationException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class OrderRepository for Action with Date Time
 */
class OrderRepository implements OrderRepositoryInterface
{
    /**
     * @var OrderResource
     */
    private $orderResource;

    /**
     * @var OrderFactory
     */
    private $orderModelFactory;

    public function __construct(
        OrderResource $orderResource,
        OrderFactory $orderModelFactory
    ) {
        $this->orderResource = $orderResource;
        $this->orderModelFactory = $orderModelFactory;
    }

    /**
     * @inheritDoc
     */
    public function save(OrderInterface $orderModel)
    {
        try {
            $this->orderResource->save($orderModel);
        } catch (ValidationException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Unable to save model %1', $orderModel->getId()));
        }

        return $orderModel;
    }

    /**
     * @param array $data
     * @todo Refactor method if it needed, change param to object, change data transfer
     */
    public function setAndSaveOrderData($data)
    {
        $orderModel = $this->orderModelFactory->create();
        $orderModel->setData(
            [
                OrderInterface::ORDER_ID => $data['order_id'],
                OrderInterface::STORE_ID => $data['location_id'],
                OrderInterface::DATE => $data['date'] ?? '',
                OrderInterface::TIME_FROM => $data['time_from'] ?? '',
                OrderInterface::TIME_TO => $data['time_to'] ?? '',
                OrderInterface::IS_CURBSIDE_PICKUP => $data['is_curbside_pickup'] ?? 0,
                OrderInterface::CURBSIDE_PICKUP_COMMENT => $data['curbside_pickup_comment'] ?? '',
            ]
        );
        $this->orderResource->save($orderModel);
    }

    /**
     * @inheritDoc
     */
    public function get($entityId)
    {
        /** @var Order $orderModel */
        $orderModel = $this->orderModelFactory->create();
        $this->orderResource->load($orderModel, $entityId);

        if (!$orderModel->getId()) {
            throw new NoSuchEntityException(__('Entity with specified ID "%1" not found.', $entityId));
        }

        return $orderModel;
    }

    /**
     * @inheritDoc
     */
    public function delete(OrderInterface $orderModel)
    {
        try {
            $this->orderResource->delete($orderModel);
        } catch (ValidationException $e) {
            throw new CouldNotDeleteException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Unable to remove entity with ID%', $orderModel->getId()));
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($entityId)
    {
        $orderModel = $this->get($entityId);
        $this->delete($orderModel);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getByOrderId($orderId)
    {
        /** @var Order $orderModel */
        $orderModel = $this->orderModelFactory->create();
        $this->orderResource->load($orderModel, $orderId, OrderInterface::ORDER_ID);

        return $orderModel;
    }
}
