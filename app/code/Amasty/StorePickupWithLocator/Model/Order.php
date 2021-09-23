<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Model;

use Amasty\StorePickupWithLocator\Api\Data\OrderInterface;
use Amasty\StorePickupWithLocator\Model\ResourceModel\Order as OrderResourceModel;
use Magento\Framework\Model\AbstractModel;

class Order extends AbstractModel implements OrderInterface
{
    protected function _construct()
    {
        $this->_init(OrderResourceModel::class);
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @param int $entityId
     * @return OrderInterface|Order
     */
    public function setId($entityId)
    {
        return $this->setData(self::ID, $entityId);
    }

    /**
     * @return int|null
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @return int|null
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * @return string|null
     */
    public function getDate()
    {
        return $this->getData(self::DATE);
    }

    /**
     * @param string $date
     * @return $this
     */
    public function setDate($date)
    {
        return $this->setData(self::DATE, $date);
    }

    /**
     * @return int|null
     */
    public function getTimeFrom()
    {
        return $this->getData(self::TIME_FROM);
    }

    /**
     * @param int $timeFrom
     * @return $this
     */
    public function setTimeFrom($timeFrom)
    {
        return $this->setData(self::TIME_FROM, $timeFrom);
    }

    /**
     * @return int|null
     */
    public function getTimeTo()
    {
        return $this->getData(self::TIME_TO);
    }

    /**
     * @param int $timeTo
     * @return $this
     */
    public function setTimeTo($timeTo)
    {
        return $this->setData(self::TIME_TO, $timeTo);
    }

    /**
     * @return bool
     */
    public function getIsCurbsidePickup()
    {
        return (bool)$this->getData(self::IS_CURBSIDE_PICKUP);
    }

    /**
     * @param bool $isCurbsidePickup
     * @return $this
     */
    public function setIsCurbsidePickup(bool $isCurbsidePickup)
    {
        return $this->setData(self::IS_CURBSIDE_PICKUP, $isCurbsidePickup);
    }

    /**
     * @return string
     */
    public function getCurbsidePickupComment()
    {
        return (string)$this->getData(self::CURBSIDE_PICKUP_COMMENT);
    }

    /**
     * @param string $comment
     * @return $this
     */
    public function setCurbsidePickupComment(string $comment)
    {
        return $this->setData(self::CURBSIDE_PICKUP_COMMENT, $comment);
    }
}
