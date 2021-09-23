<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Test\Integration;

use Amasty\StorePickupWithLocatorMSI\Model\Location\GetLocationsByProduct;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreRepository;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use PHPUnit\Framework\TestCase;
use \Amasty\Storelocator\Model\ResourceModel\Location\Collection;

class GetLocationsCollectionByProductTest extends TestCase
{
    /**
     * @var GetLocationsByProduct
     */
    private $getLocationByProduct;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->getLocationByProduct = Bootstrap::getObjectManager()->get(GetLocationsByProduct::class);
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
    }

    /**
     * @magentoDataFixture Magento_InventoryApi::Test/_files/products.php
     * @magentoDataFixture Magento_InventoryApi::Test/_files/sources.php
     * @magentoDataFixture Magento_InventoryApi::Test/_files/stocks.php
     * @magentoDataFixture Magento_InventoryApi::Test/_files/stock_source_links.php
     * @magentoDataFixture Magento_InventorySalesApi::Test/_files/websites_with_stores.php
     * @magentoDataFixture Magento_InventorySalesApi::Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture Magento_InventoryApi::Test/_files/source_items.php
     * @magentoDataFixture Amasty_StorePickupWithLocatorMSI::Test/Integration/_files/locations_with_stock_source_type.php
     * @dataProvider executeDataProviderInStockOnlyOn
     * @magentoAppArea frontend
     * @magentoCache config disabled
     *
     * @magentoDbIsolation disabled
     *
     * @param string $productSku
     * @param int $equalValue
     * @param string $storeViewCode
     * @param array $expectedIds
     * @param int $inStockOnly
     *
     * @throws NoSuchEntityException
     */
    public function testGetLocationsWithoutInStockConfig(
        string $productSku,
        int $equalValue,
        string $storeViewCode,
        array $expectedIds,
        int $inStockOnly
    ) {
        $this->execute($productSku, $equalValue, $storeViewCode, $expectedIds, (bool)$inStockOnly);
    }

    /**
     * @magentoDataFixture Magento_InventoryApi::Test/_files/products.php
     * @magentoDataFixture Magento_InventoryApi::Test/_files/sources.php
     * @magentoDataFixture Magento_InventoryApi::Test/_files/stocks.php
     * @magentoDataFixture Magento_InventoryApi::Test/_files/stock_source_links.php
     * @magentoDataFixture Magento_InventorySalesApi::Test/_files/websites_with_stores.php
     * @magentoDataFixture Magento_InventorySalesApi::Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture Magento_InventoryApi::Test/_files/source_items.php
     * @magentoDataFixture Amasty_StorePickupWithLocatorMSI::Test/Integration/_files/locations_with_stock_source_type.php
     * @dataProvider executeDataProviderInStockOnlyOff
     * @magentoAppArea frontend
     * @magentoCache config disabled
     *
     * @magentoDbIsolation disabled
     *
     * @param string $productSku
     * @param int $equalValue
     * @param string $storeViewCode
     * @param array $expectedIds
     * @param int $inStockOnly
     *
     * @throws NoSuchEntityException
     */
    public function testGetLocationsWithInStockConfig(
        string $productSku,
        int $equalValue,
        string $storeViewCode,
        array $expectedIds,
        int $inStockOnly
    ) {
        $this->execute($productSku, $equalValue, $storeViewCode, $expectedIds, (bool)$inStockOnly);
    }

    /**
     * @param string $productSku
     * @param int $equalValue
     * @param string $storeViewCode
     * @param array $expectedIds
     * @param bool $inStockOnly
     */
    private function execute(
        string $productSku,
        int $equalValue,
        string $storeViewCode,
        array $expectedIds,
        bool $inStockOnly
    ) {
        $storeRepository = Bootstrap::getObjectManager()->get(StoreRepository::class);
        $store = $storeRepository->getActiveStoreByCode($storeViewCode);
        $locations = $this->getLocations($productSku, (int)$store->getId(), (bool)$inStockOnly);
        $locationItems = $locations->getItems();
        $this->assertEquals($equalValue, count($locationItems));
        $this->assertLocationIds($locationItems, $expectedIds);
    }

    /**
     * @param string $productSku
     * @param int $storeId
     * @param bool $inStockOnly
     *
     * @return Collection
     */
    private function getLocations(string $productSku, int $storeId, bool $inStockOnly): Collection
    {
        return $this->getLocationByProduct->getLocationsByProduct($productSku, $storeId, $inStockOnly);
    }

    /**
     * @param array $locations
     * @param array $expectedIds
     */
    private function assertLocationIds(array $locations, array $expectedIds)
    {
        $actualResult = array_map(
            function ($location) {
                return (int)$location->getId();
            },
            $locations
        );

        sort($actualResult);

        $this->assertSame($expectedIds, $actualResult);
    }

    /**
     * [
     *      Product Sku
     *      Equal value
     *      Store View
     *      config_value storepickup_locator/general/include_out_of_stock_locations 1
     * ]
     *
     * @return array
     */
    public function executeDataProviderInStockOnlyOn(): array
    {
        return [
            [
                'SKU-1',
                1,
                'store_for_eu_website',
                'expected_ids' => [10],
                'in_stock_only' => 1
            ],
            [
                'SKU-2',
                0,
                'store_for_us_website',
                'expected_ids' => [],
                'in_stock_only' => 1
            ],
        ];
    }

    /**
     * [
     *      Product Sku
     *      Equal value
     *      Store View
     *      config_value storepickup_locator/general/include_out_of_stock_locations 0
     * ]
     *
     * @return array
     */
    public function executeDataProviderInStockOnlyOff(): array
    {
        return [
            [
                'SKU-1',
                2,
                'store_for_eu_website',
                'expected_ids' => [10, 20],
                'in_stock_only' => 0
            ],
        ];
    }
}
