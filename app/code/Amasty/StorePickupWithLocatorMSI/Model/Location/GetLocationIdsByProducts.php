<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Model\Location;

use Amasty\StorePickupWithLocator\Model\Location\FilterIntersectLocations;
use Amasty\StorePickupWithLocatorMSI\Model\ResourceModel\LocationResource;
use Amasty\StorePickupWithLocatorMSI\Model\StockIdResolver;

/**
 * Get common location ids for all products
 */
class GetLocationIdsByProducts
{
    /**
     * @var LocationResource
     */
    private $locationResource;

    /**
     * @var StockIdResolver
     */
    private $stockIdResolver;

    /**
     * @var FilterIntersectLocations
     */
    private $filterIntersectLocations;

    public function __construct(
        LocationResource $locationResource,
        StockIdResolver $stockIdResolver,
        FilterIntersectLocations $filterIntersectLocations
    ) {
        $this->locationResource = $locationResource;
        $this->stockIdResolver = $stockIdResolver;
        $this->filterIntersectLocations = $filterIntersectLocations;
    }

    /**
     * @param array $skusWithQtys
     * @param int $storeId
     * @return int[]
     */
    public function getAvailableLocationIds(array $skusWithQtys, int $storeId): array
    {
        $stockId = $this->stockIdResolver->getStockId($storeId);
        $skus = array_keys($skusWithQtys);
        $productLocationDataList = $this->locationResource->getProductsLocationData($skus, $stockId);

        $productsWithLocations = [];
        foreach ($productLocationDataList as $productLocationData) {
            $sku = $productLocationData['sku'];
            if ((float)$productLocationData['qty'] >= (float)$skusWithQtys[$sku]) {
                $productsWithLocations[$sku][] = $productLocationData['location_id'];
            }
        }

        return $this->filterIntersectLocations->filter(
            $skus,
            $productsWithLocations
        );
    }
}
