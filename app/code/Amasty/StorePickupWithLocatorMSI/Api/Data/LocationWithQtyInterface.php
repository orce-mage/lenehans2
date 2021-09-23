<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Api\Data;

interface LocationWithQtyInterface
{
    const LOCATION = 'location';
    const QTY = 'qty';

    /**
     * @return \Amasty\StorePickupWithLocatorMSI\Api\Data\LocationDataInterface
     */
    public function getLocation(): \Amasty\StorePickupWithLocatorMSI\Api\Data\LocationDataInterface;

    /**
     * @param \Amasty\StorePickupWithLocatorMSI\Api\Data\LocationDataInterface $location
     * @return void
     */
    public function setLocation(\Amasty\StorePickupWithLocatorMSI\Api\Data\LocationDataInterface $location): void;

    /**
     * @return int
     */
    public function getQty(): int;

    /**
     * @param int $qty
     * @return void
     */
    public function setQty(int $qty): void;
}
