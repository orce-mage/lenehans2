<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */

\Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize();

/** @var \Magento\TestFramework\ObjectManager $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$arrayLocations = [
    [
        'id' => 10,
        'name' => 'location1',
        'sources' => ['eu-1'],
        'status' => 1,
        'condition_type' => 2
    ],
    [
        'id' => 20,
        'name' => 'location2',
        'sources' => ['eu-1', 'eu-2'],
        'status' => 1,
        'condition_type' => 2
    ],
    [
        'id' => 30,
        'name' => 'location3',
        'sources' => ['eu-1', 'eu-2', 'eu-3'],
        'status' => 1,
        'condition_type' => 2
    ],
    [
        'id' => 40,
        'name' => 'location4',
        'sources' => ['eu-1', 'eu-2', 'eu-3', 'us-1'],
        'status' => 1,
        'condition_type' => 1
    ],
    [
        'id' => 50,
        'name' => 'location5',
        'sources' => ['us-1', 'eu-1'],
        'status' => 1,
        'condition_type' => 1
    ]
];

foreach ($arrayLocations as $locationData) {
    /** @var \Amasty\Storelocator\Model\Location $location */
    $location = $objectManager->create(\Amasty\Storelocator\Model\Location::class);

    $location->setId($locationData['id']);
    $location->setName($locationData['name']);
    $location->setCountry('AF');
    $location->setCity('New Albany');
    $location->setZip('43054');
    $location->setAddress('New Albany Rd West, 5946');
    $location->setStatus($locationData['status']);
    $location->setLat(0.00000000);
    $location->setLng(0.00000000);
    $location->setPosition(0);
    $location->setState('Ohio');
    $location->setDescription(
        '<div data-content-type="row" data-appearance="contained" data-element="main">
                    <div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}"
                    data-background-type="image" data-video-loop="true" data-video-play-only-visible="true"
                    data-video-lazy-load="true" data-video-fallback-src="" data-element="inner"
                    style="justify-content: flex-start; display: flex; flex-direction: column;
                    background-position: left top; background-size: cover; background-repeat: no-repeat;
                    background-attachment: scroll; border-style: none; border-width: 1px; border-radius: 0px;
                     margin: 0px 0px 10px; padding: 10px;">

</div></div>'
    );
    $location->setPhone(14086477351);
    $location->setEmail('admin@admin.com');
    $location->setActionsSerialized(
        '{"type":"Magento\\\CatalogRule\\\Model\\\Rule\\\Condition\\\Combine",
"attribute":null,"operator":null,"value":"1","is_value_processed":null,"aggregator":"all"}'
    );
    $location->setShowSchedule(1);
    $location->setMetaRobots('INDEX,FOLLOW');
    $location->setConditionType($locationData['condition_type']);
    $location->setStores(0);
    $location->setStoreImg('');
    $location->setMarkerImg('');

    $location->isObjectNew(true);

    /**
     * @var \Amasty\Storelocator\Model\ResourceModel\Location $resourceLocations
     */
    $resourceLocations = $objectManager->create(\Amasty\Storelocator\Model\ResourceModel\Location::class);
    $resourceLocations->setResourceFlags();
    $resourceLocations->save($location);
}

/** @var \Amasty\StorePickupWithLocatorMSI\Api\LocationSourceRepositoryInterface $locationSourceRepository */
$locationSourceRepository = $objectManager->create(
    \Amasty\StorePickupWithLocatorMSI\Api\LocationSourceRepositoryInterface::class
);
foreach ($arrayLocations as $locationData) {
    foreach ($locationData['sources'] as $source) {
        /** @var \Amasty\StorePickupWithLocatorMSI\Model\LocationSource $locationSource */
        $locationSource = $objectManager->create(\Amasty\StorePickupWithLocatorMSI\Model\LocationSource::class);
        $locationSource->setSourceCode($source);
        $locationSource->setLocationId($locationData['id']);
        $locationSourceRepository->save($locationSource);
    }
}
