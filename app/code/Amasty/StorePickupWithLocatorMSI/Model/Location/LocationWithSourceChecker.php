<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Model\Location;

use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory;
use Amasty\StorePickupWithLocatorMSI\Model\ResourceModel\LocationSourceDataJoiner;
use Amasty\StorePickupWithLocatorMSI\Plugin\Storelocator\Model\Config\Source\ConditionTypePlugin;
use Magento\Store\Model\Store;

class LocationWithSourceChecker
{
    /**
     * @var CollectionFactory
     */
    private $locationCollectionFactory;

    /**
     * @var LocationSourceDataJoiner
     */
    private $locationSourceDataJoiner;

    public function __construct(
        CollectionFactory $locationCollectionFactory,
        LocationSourceDataJoiner $locationSourceDataJoiner
    ) {
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->locationSourceDataJoiner = $locationSourceDataJoiner;
    }

    /**
     * @param int $storeId
     * @return bool
     */
    public function isExists(int $storeId): bool
    {
        $collection = $this->locationCollectionFactory->create();
        $collection->addFilterByStores([Store::DEFAULT_STORE_ID, $storeId]);
        $collection->addFieldToFilter(
            'main_table.' . LocationInterface::STATUS,
            LocationInterface::STATUS_ENABLED
        );
        $collection->addFieldToFilter(
            'main_table.' . LocationInterface::CONDITION_TYPE,
            ConditionTypePlugin::MSI_SOURCE
        );

        $select = $collection->getSelect();
        $this->locationSourceDataJoiner->joinData($select, $storeId);

        return $collection->getSize() > 0;
    }
}
