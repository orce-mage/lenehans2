<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Model;

use Amasty\StorePickupWithLocatorMSI\Api\Data\LocationSourceInterface;
use Magento\Framework\Model\AbstractModel;

class LocationSource extends AbstractModel implements LocationSourceInterface
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\LocationSource::class);
        $this->setIdFieldName(LocationSourceInterface::ENTITY_ID);
    }

    /**
     * @return int
     */
    public function getEntityId(): int
    {
        return (int)$this->_getData(self::ENTITY_ID);
    }

    /**
     * @param int $entityId
     *
     * @return void
     */
    public function setEntityId($entityId): void
    {
        $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * @return int
     */
    public function getLocationId(): int
    {
        return (int)$this->_getData(self::LOCATION_ID);
    }

    /**
     * @param int $locationId
     *
     * @return void
     */
    public function setLocationId(int $locationId): void
    {
        $this->setData(self::LOCATION_ID, $locationId);
    }

    /**
     * @return string
     */
    public function getSourceCode(): string
    {
        return $this->_getData(self::SOURCE_CODE);
    }

    /**
     * @param string $code
     *
     * @return void
     */
    public function setSourceCode(string $code): void
    {
        $this->setData(self::SOURCE_CODE, $code);
    }
}
