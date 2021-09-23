<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Model;

use Amasty\StorePickupWithLocatorMSI\Api\Data\LocationSourceInterface;
use Amasty\StorePickupWithLocatorMSI\Api\LocationSourceRepositoryInterface;
use Amasty\StorePickupWithLocatorMSI\Model\ResourceModel\LocationSource;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;

class LocationSourceRepository implements LocationSourceRepositoryInterface
{
    /**
     * @var LocationSource
     */
    private $locationSourceResource;

    public function __construct(
        LocationSource $locationSourceResource
    ) {
        $this->locationSourceResource = $locationSourceResource;
    }

    /**
     * @param LocationSourceInterface $locationSource
     * @return LocationSourceInterface
     * @throws CouldNotSaveException
     */
    public function save(LocationSourceInterface $locationSource): LocationSourceInterface
    {
        try {
            $this->locationSourceResource->save($locationSource);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Unable to save new entity. Error: %1', $e->getMessage()));
        }

        return $locationSource;
    }

    /**
     * @param LocationSourceInterface $locationSource
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(LocationSourceInterface $locationSource): bool
    {
        try {
            $this->locationSourceResource->delete($locationSource);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Unable to remove entity. Error: %1', $e->getMessage()));
        }

        return true;
    }
}
