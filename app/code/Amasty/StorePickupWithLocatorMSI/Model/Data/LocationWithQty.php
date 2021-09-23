<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Model\Data;

use Amasty\StorePickupWithLocatorMSI\Api\Data\LocationDataInterface;
use Amasty\StorePickupWithLocatorMSI\Api\Data\LocationWithQtyInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class LocationWithQty extends AbstractSimpleObject implements LocationWithQtyInterface
{
    /**
     * @return LocationDataInterface
     */
    public function getLocation(): LocationDataInterface
    {
        return $this->_get(self::LOCATION);
    }

    /**
     * @param LocationDataInterface $location
     */
    public function setLocation(LocationDataInterface $location): void
    {
        $this->setData(self::LOCATION, $location);
    }

    /**
     * @return int
     */
    public function getQty(): int
    {
        return $this->_get(self::QTY);
    }

    /**
     * @param int $qty
     */
    public function setQty(int $qty): void
    {
        $this->setData(self::QTY, $qty);
    }
}
