<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


declare(strict_types=1);

namespace MidoriWeb\Inventory\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;

class InventoryResolver
{
    const MAGENTO_INVENTORY_MODULE_NAMESPACE = 'Magento_Inventory';

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var StockIndexTableNameResolverInterface
     */
    private $stockIndexTableNameResolver;

    /**
     * @var GetStockIdForCurrentWebsite
     */
    private $getStockIdForCurrentWebsite;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var AdapterInterface
     */
    private $connection;

    private $stockResolver;

    public function __construct(
        ResourceConnection $resource,
        Manager $moduleManager,
        ObjectManagerInterface $objectManager,
        StockResolverInterface $stockResolver
    ) {
        $this->resource = $resource;
        $this->moduleManager = $moduleManager;
        $this->connection = $resource->getConnection();
        $this->stockResolver = $stockResolver;

        if ($this->isMagentoInventoryEnable()) {
            $this->stockIndexTableNameResolver = $objectManager->get(StockIndexTableNameResolverInterface::class);
            $this->getStockIdForCurrentWebsite = $objectManager->get(GetStockIdForCurrentWebsite::class);
        }
    }

    /**
     * Retrieve catalog inventory data using "Magento Inventory" module if available
     *
     * @param int[] $productIds
     * @return array
     */
    public function getInventoryData(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        if ($this->isMagentoInventoryEnable()) {
            $data = $this->getInventoryProductsData($productIds);
        } else {
            $data = $this->getCatalogInventoryProductsData($productIds);
        }

        return $this->prepareCatalogInventoryData($data);
    }

    /**
     * Retrieve a list of IDs for out of stock products using "Magento Inventory" module if available
     *
     * @return array
     */
    public function getOutOfStockProductIds($storeId = null): array
    {
        if ($this->isMagentoInventoryEnable()) {
            $productIds = $this->getProductIdsFromInventoryProducts($storeId);
        } else {
            $productIds = $this->getProductIdsFromCatalogInventoryProducts();
        }

        return $productIds;
    }

    /**
     * Checks if "Magento Inventory" module is enabled
     *
     * @return bool
     */
    private function isMagentoInventoryEnable(): bool
    {
        return $this->moduleManager->isEnabled(self::MAGENTO_INVENTORY_MODULE_NAMESPACE);
    }

    /**
     * Get stock index table by stock id.
     *
     * @return string
     */
    private function getStockIndexTableName($storeId = null): string
    {
        if($storeId != null) {
            $websiteId = (int)$this->storeManager->getStore($storeId)->getWebsiteId();
            $websiteCode = $this->storeManager->getWebsite($websiteId)->getCode();

            $stock = $this->stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode);
            $stockId = (int)$stock->getStockId();
        }else {
            $stockId = $this->getStockIdForCurrentWebsite->execute();
        }

        //$stockId = 2;
        return $this->stockIndexTableNameResolver->execute($stockId);
    }

    /**
     * Returns a "select" object using "Magento Inventory" module
     *
     * @param  int[] $productIds
     * @return Select
     */
    private function getSelectInventoryProducts($storeId = null): Select
    {
        $stockIndexTableName = $this->getStockIndexTableName($storeId);

        return $this->connection->select()
            ->from(
                ['stock_index' => $this->resource->getTableName($stockIndexTableName)],
                ['qty' => 'stock_index.quantity']
            )->join(
                ['product' => $this->resource->getTableName('catalog_product_entity')],
                'product.sku = stock_index.sku',
                ['product_id' => 'product.entity_id']
            );
    }

    /**
     * Returns a "select" object using "Magento Inventory" module
     *
     * @param  int[] $productIds
     * @return array
     */
    private function getInventoryProductsData($productIds): array
    {
        $select = $this->getSelectInventoryProducts()
            ->where(
                'product.entity_id IN (?)',
                $productIds
            );

        return $this->connection->fetchAll($select);
    }

    /**
     * Retrieve a list of IDs for out of stock products using "Magento Inventory" module
     *
     * @return array
     */
    private function getProductIdsFromInventoryProducts($storeId = null): array
    {
        $select = $this->getSelectInventoryProducts($storeId)
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns(['product.entity_id'])
            ->where(
                'stock_index.is_salable = ?',
                0
            );

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/fa.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($select->assemble());

        return $this->connection->fetchCol($select);
    }

    /**
     * Returns a "select" object using "Magento Catalog Inventory" module
     *
     * @param  int[] $productIds
     * @return Select
     */
    private function getSelectCatalogInventoryProducts(): Select
    {
        return $this->connection->select()
            ->from(['stock_item' => $this->resource->getTableName('cataloginventory_stock_item')]);
    }

    /**
     * Retrieve a list of IDs for out of stock products using "Magento Catalog Inventory" module
     *
     * @return array
     */
    private function getProductIdsFromCatalogInventoryProducts(): array
    {
        $select = $this->getSelectCatalogInventoryProducts()
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns(['stock_item.product_id'])
            ->where(
                'stock_item.is_in_stock = ?',
                0
            );

        return $this->connection->fetchCol($select);
    }

    /**
     * Returns a "select" object using "Magento Catalog Inventory" module
     *
     * @param  int[] $productIds
     * @return array
     */
    private function getCatalogInventoryProductsData($productIds): array
    {
        $select = $this->getSelectCatalogInventoryProducts()
            ->where(
                'stock_item.product_id IN (?)',
                $productIds
            );

        return $this->connection->fetchAll($select);
    }

    /**
     * Retrieve catalog inventory data by "select" object
     *
     * @param  array $data
     * @return array
     */
    private function prepareCatalogInventoryData(array $data): array
    {
        $stockItemRows = [];

        foreach ($data as $stockItemRow) {
            $productId = $stockItemRow['product_id'];

            unset(
                $stockItemRow['item_id'],
                $stockItemRow['product_id'],
                $stockItemRow['low_stock_date'],
                $stockItemRow['stock_id'],
                $stockItemRow['stock_status_changed_auto']
            );

            $stockItemRows[$productId] = $stockItemRow;
        }

        return $stockItemRows;
    }
}
