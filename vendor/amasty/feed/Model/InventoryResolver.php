<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


declare(strict_types=1);

namespace Amasty\Feed\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;

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

    public function __construct(
        ResourceConnection $resource,
        Manager $moduleManager,
        ObjectManagerInterface $objectManager
    ) {
        $this->resource = $resource;
        $this->moduleManager = $moduleManager;
        $this->connection = $resource->getConnection();

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
    public function getOutOfStockProductIds(): array
    {
        if ($this->isMagentoInventoryEnable()) {
            $productIds = $this->getProductIdsFromInventoryProducts();
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
    private function getStockIndexTableName(): string
    {
        $stockId = $this->getStockIdForCurrentWebsite->execute();

        return $this->stockIndexTableNameResolver->execute($stockId);
    }

    /**
     * Returns a "select" object using "Magento Inventory" module
     *
     * @param  int[] $productIds
     * @return Select
     */
    private function getSelectInventoryProducts(): Select
    {
        $stockIndexTableName = $this->getStockIndexTableName();

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
    private function getProductIdsFromInventoryProducts(): array
    {
        $select = $this->getSelectInventoryProducts()
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns(['product.entity_id'])
            ->where(
                'stock_index.is_salable = ?',
                0
            );

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
