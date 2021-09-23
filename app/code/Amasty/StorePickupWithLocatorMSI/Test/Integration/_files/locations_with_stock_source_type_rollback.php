<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;

/** @var \Amasty\Storelocator\Model\ResourceModel\Location $resourceLocations */
$resourceLocations = Bootstrap::getObjectManager()->get(\Amasty\Storelocator\Model\ResourceModel\Location::class);
/** @var \Amasty\Storelocator\Model\Location $location */
$location = Bootstrap::getObjectManager()->get(\Amasty\Storelocator\Model\Location::class);

$arrayDeleteIdsLocations = [10, 20, 30, 40];

foreach ($arrayDeleteIdsLocations as $deleteId) {
    $resourceLocations->load($location, $deleteId);
    $resourceLocations->delete($location);

    /** @var \Amasty\StorePickupWithLocatorMSI\Model\LocationSource $locationSource */
    $locationSource = Bootstrap::getObjectManager()->get(\Amasty\StorePickupWithLocatorMSI\Model\LocationSource::class);

    /** @var \Amasty\StorePickupWithLocatorMSI\Model\ResourceModel\LocationSource $locationSourceResource */
    $locationSourceResource = Bootstrap::getObjectManager()->get(
        \Amasty\StorePickupWithLocatorMSI\Model\ResourceModel\LocationSource::class
    );
    $locationSourceResource->load($locationSource, $deleteId, 'location_id');

    /** @var \Amasty\StorePickupWithLocatorMSI\Model\LocationSourceRepository $locationSourceRepository */
    $locationSourceRepository = Bootstrap::getObjectManager()->get(
        \Amasty\StorePickupWithLocatorMSI\Model\LocationSourceRepository::class
    );
    $locationSourceRepository->delete($locationSource);
}
