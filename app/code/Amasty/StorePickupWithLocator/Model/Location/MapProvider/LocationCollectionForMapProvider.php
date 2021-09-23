<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocator\Model\Location\MapProvider;

use Amasty\Storelocator\Model\ResourceModel\Location\Collection;
use Amasty\StorePickupWithLocator\Api\LocationCollectionForMapProviderInterface;
use Amasty\StorePickupWithLocator\Model\LocationProvider;

class LocationCollectionForMapProvider implements LocationCollectionForMapProviderInterface
{
    /**
     * @var LocationProvider
     */
    private $locationProvider;

    public function __construct(LocationProvider $locationProvider)
    {
        $this->locationProvider = $locationProvider;
    }

    /**
     * @return Collection
     */
    public function getCollection(): Collection
    {
        return $this->locationProvider->getPreparedCollection(false);
    }
}
