<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Test\Integration;

use Amasty\StorePickupWithLocatorMSI\Model\Location\GetLocationIdsByProducts;
use Magento\Store\Model\StoreRepository;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class GetAvailableLocationsTest extends TestCase
{
    /**
     * @magentoDataFixture Magento_InventoryApi::Test/_files/products.php
     * @magentoDataFixture Magento_InventoryApi::Test/_files/sources.php
     * @magentoDataFixture Magento_InventoryApi::Test/_files/stocks.php
     * @magentoDataFixture Magento_InventoryApi::Test/_files/stock_source_links.php
     * @magentoDataFixture Magento_InventorySalesApi::Test/_files/websites_with_stores.php
     * @magentoDataFixture Magento_InventorySalesApi::Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture Magento_InventoryApi::Test/_files/source_items.php
     * @magentoDataFixture Amasty_StorePickupWithLocatorMSI::Test/Integration/_files/locations_with_stock_source_type_available.php
     * @dataProvider availableLocationsDataProvider
     * @magentoAppArea frontend
     *
     * @magentoDbIsolation disabled
     *
     * @param array $productSkus
     * @param string $storeViewCode
     * @param array $expectedIds
     */
    public function testGetAvailableLocationsByProductsArray(
        array $productSkus,
        string $storeViewCode,
        array $expectedIds
    ) {
        /** @var StoreRepository $storeRepository */
        $storeRepository = Bootstrap::getObjectManager()->get(StoreRepository::class);

        $store = $storeRepository->getActiveStoreByCode($storeViewCode);

        /** @var $getLocationIdsByProducts getLocationIdsByProducts */
        $getLocationIdsByProducts = Bootstrap::getObjectManager()->get(GetLocationIdsByProducts::class);

        $availableLocationsIds = $getLocationIdsByProducts->getAvailableLocationIds($productSkus, (int)$store->getId());
        $this->assertLocationIds($expectedIds, $availableLocationsIds);
    }

    /**
     * @param array $expectedIds
     * @param array $actualResult
     */
    private function assertLocationIds(array $expectedIds, array $actualResult)
    {
        sort($actualResult);
        $this->assertSame($expectedIds, $actualResult);
    }

    /**
     * @return array[]
     */
    public function availableLocationsDataProvider(): array
    {
        return [
            // store_for_eu_website
            // SKU-1 -> eu-1, eu-2, eu-3
            // SKU-4 -> eu-2
            [
                'skusWithQtys' => ['SKU-1' => 5.5, 'SKU-4' => 6],
                'store_id' => 'store_for_eu_website',
                'expectedIds' => [20, 30, 40]
            ],
            // store_for_eu_website
            // SKU-1 -> eu-1, eu-2, eu-3
            [
                'skusWithQtys' => ['SKU-1' => 5.5],
                'store_id' => 'store_for_eu_website',
                'expectedIds' => [10, 20, 30, 40, 50]
            ],
            // store_for_eu_website
            // SKU-1 -> eu-1, eu-2, eu-3
            [
                'skusWithQtys' => ['SKU-1' => 8],
                'store_id' => 'store_for_eu_website',
                'expectedIds' => [20, 30, 40]
            ],
            // store_for_us_website
            // SKU-2 -> us-1 -> qty = 5, requested qty 5
            [
                'skusWithQtys' => ['SKU-2' => 5],
                'store_id' => 'store_for_us_website',
                'expectedIds' => [40, 50]
            ],
            // store_for_us_website
            // SKU-2 -> us-1 -> qty = 5, requested qty 6
            // returned empty result
            [
                'skusWithQtys' => ['SKU-2' => 6],
                'store_id' => 'store_for_us_website',
                'expectedIds' => []
            ],
            // store_for_global_website
            // SKU-2 -> us-1
            // SKU-4 -> eu-2
            [
                'skusWithQtys' => ['SKU-2' => 5, 'SKU-4' => 6],
                'store_id' => 'store_for_global_website',
                'expectedIds' => [40]
            ],
            // store_for_eu_website
            // SKU-1 -> eu-1, eu-2, eu-3
            // SKU-2 -> us-1
            // returned empty result
            [
                'skusWithQtys' => ['SKU-1' => 5.5, 'SKU-2' => 5],
                'store_id' => 'store_for_eu_website',
                'expectedIds' => []
            ],
        ];
    }
}
