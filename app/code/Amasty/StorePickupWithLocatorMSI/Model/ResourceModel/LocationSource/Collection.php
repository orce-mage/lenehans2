<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Model\ResourceModel\LocationSource;

use Amasty\StorePickupWithLocatorMSI\Api\Data\LocationSourceInterface;
use Amasty\StorePickupWithLocatorMSI\Model\LocationSource;
use Amasty\StorePickupWithLocatorMSI\Model\ResourceModel\LocationSource as LocationSourceResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = LocationSourceInterface::ENTITY_ID;

    protected function _construct()
    {
        $this->_init(LocationSource::class, LocationSourceResource::class);
    }

    /**
     * @param int $locationId
     * @return Collection
     */
    public function addFilterByLocationId($locationId): Collection
    {
        return $this->addFieldToFilter(LocationSourceInterface::LOCATION_ID, $locationId);
    }
}
