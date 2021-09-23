<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocator\Model\Location\Filter;

use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Model\Config\Source\ConditionType;
use Amasty\StorelocatorIndexer\Model\ResourceModel\LocationProductIndex;
use Amasty\StorePickupWithLocator\Api\Filter\LocationProductFilterInterface;
use Amasty\StorePickupWithLocator\Model\ConfigProvider;
use Amasty\StorePickupWithLocator\Model\Location\FilterIntersectLocations;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Model\Store;

class ProductAttributeConditionFilter implements LocationProductFilterInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var LocationProductIndex
     */
    private $locationProductIndex;

    /**
     * @var FilterIntersectLocations
     */
    private $filterIntersectLocations;

    public function __construct(
        ConfigProvider $configProvider,
        LocationProductIndex $locationProductIndex,
        FilterIntersectLocations $filterIntersectLocations
    ) {
        $this->configProvider = $configProvider;
        $this->locationProductIndex = $locationProductIndex;
        $this->filterIntersectLocations = $filterIntersectLocations;
    }

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $productIds
     * @param int $storeId
     */
    public function apply(SearchCriteriaBuilder $searchCriteriaBuilder, array $productIds, int $storeId): void
    {
        if (!$this->configProvider->isCheckProductAvailability()) {
            $searchCriteriaBuilder->addFilter(LocationInterface::CONDITION_TYPE, ConditionType::PRODUCT_ATTRIBUTE);

            return;
        }

        $storeIds = [$storeId];
        if ($storeId != Store::DEFAULT_STORE_ID) {
            $storeIds[] = Store::DEFAULT_STORE_ID;
        }

        $fields = $this->locationProductIndex->getLocationsByProduct($productIds, $storeIds);

        // @TODO: do it in indexer getLocationsByProduct method
        $productsWithLocations = [];
        foreach ($fields as $field) {
            $locationId = $field[LocationProductIndex::LOCATION_ID];
            $productId = $field[LocationProductIndex::PRODUCT_ID];
            $productsWithLocations[$productId][] = $locationId;
        }

        $locationIds = $this->filterIntersectLocations->filter($productIds, $productsWithLocations);

        if (!$locationIds) {
            return;
        }

        $searchCriteriaBuilder->addFilter(LocationInterface::ID, $locationIds, 'in');
    }
}
