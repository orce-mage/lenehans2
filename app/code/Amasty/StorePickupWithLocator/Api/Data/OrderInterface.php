<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Api\Data;

interface OrderInterface
{
    /**
     * Constants defined for keys of data array
     */
    const ID = 'id';
    const ORDER_ID = 'order_id';
    const STORE_ID = 'store_id';
    const DATE = 'date';
    const TIME_FROM = 'time_from';
    const TIME_TO = 'time_to';
    const IS_CURBSIDE_PICKUP = 'is_curbside_pickup';
    const CURBSIDE_PICKUP_COMMENT = 'curbside_pickup_comment';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int $entityId
     * @return OrderInterface
     */
    public function setId($entityId);

    /**
     * @return int|null
     */
    public function getOrderId();

    /**
     * @param int|null $orderId
     * @return OrderInterface
     */
    public function setOrderId($orderId);

    /**
     * @return int|null
     */
    public function getStoreId();

    /**
     * @param int|null $storeId
     * @return OrderInterface
     */
    public function setStoreId($storeId);

    /**
     * @return string|null
     */
    public function getDate();

    /**
     * @param string|null $date
     * @return OrderInterface
     */
    public function setDate($date);

    /**
     * @return int|null
     */
    public function getTimeFrom();

    /**
     * @param int|null $time
     * @return OrderInterface
     */
    public function setTimeFrom($time);

    /**
     * @return int|null
     */
    public function getTimeTo();

    /**
     * @param int|null $time
     * @return OrderInterface
     */
    public function setTimeTo($time);

    /**
     * @return bool
     */
    public function getIsCurbsidePickup();

    /**
     * @param bool $isCurbsidePickup
     * @return $this
     */
    public function setIsCurbsidePickup(bool $isCurbsidePickup);

    /**
     * @return string
     */
    public function getCurbsidePickupComment();

    /**
     * @param string $comment
     * @return $this
     */
    public function setCurbsidePickupComment(string $comment);
}
