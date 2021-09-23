<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


namespace Amasty\Stockstatus\Model\ResourceModel;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\Exception\NoSuchEntityException;
use Zend_Db_Expr;

class Inventory extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const CUSTOM_IN_STOCK_COLUMN = 'am_is_in_stock';

    /**
     * @var array
     */
    private $stockIds;

    /**
     * @var bool
     */
    private $msiEnabled = null;

    /**
     * @var array
     */
    private $sourceCodes;

    /**
     * @var array
     */
    private $stockStatus;

    /**
     * @var array
     */
    private $qty;

    /**
     * @var array
     */
    private $qtyBySource;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->moduleManager = $moduleManager;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->stockIds = [];
        $this->sourceCodes = [];
        $this->stockStatus = [];
        $this->qty = [];
        $this->qtyBySource = [];
    }

    /**
     * @param string $productSku
     * @param string $websiteCode
     *
     * @return bool
     *
     * @throws NoSuchEntityException
     */
    public function getStockStatus(string $productSku, string $websiteCode): bool
    {
        if (!$this->hasStockStatusCache($productSku, $websiteCode)) {
            if ($this->isMSIEnabled()) {
                $result = $this->getMsiSalable([$productSku], $websiteCode);
                $result = array_shift($result);
            } else {
                $result = $this->getStockItem($productSku, $websiteCode)->getIsInStock();
            }

            $this->saveStockStatusCache($productSku, $websiteCode, (int)$result);
        }

        return (bool) $this->getStockStatusCache($productSku, $websiteCode);
    }

    /**
     * @param string[] $productSkus
     * @param string $websiteCode
     * @throws NoSuchEntityException
     */
    public function loadStockStatus(array $productSkus, string $websiteCode): void
    {
        if (!isset($this->stockStatus[$websiteCode])) {
            $this->stockStatus[$websiteCode] = [];
        }

        if (!array_diff($productSkus, array_keys($this->stockStatus[$websiteCode]))) {
            return;
        }

        if ($this->isMSIEnabled()) {
            $result = $this->getMsiSalable($productSkus, $websiteCode);
            $this->stockStatus[$websiteCode] = array_replace($this->stockStatus[$websiteCode], $result);
        } else {
            foreach ($productSkus as $productSku) {
                $this->saveStockStatusCache(
                    $productSku,
                    $websiteCode,
                    $this->getStockItem($productSku, $websiteCode)->getIsInStock()
                );
            }
        }
    }

    private function hasStockStatusCache(string $productSku, string $websiteCode): bool
    {
        return isset($this->stockStatus[$websiteCode][$productSku]);
    }

    private function getStockStatusCache(string $productSku, string $websiteCode): int
    {
        return $this->stockStatus[$websiteCode][$productSku];
    }

    private function saveStockStatusCache(string $productSku, string $websiteCode, int $stockStatus): void
    {
        $this->stockStatus[$websiteCode][$productSku] = $stockStatus;
    }

    /**
     * @param $productSku
     * @param $websiteCode
     *
     * @return float|int
     *
     * @throws NoSuchEntityException
     */
    public function getQty($productSku, $websiteCode)
    {
        if ($this->isMSIEnabled()) {
            $qty = $this->getMsiQty($productSku, $websiteCode);
        } else {
            $qty = $this->getStockItem($productSku, $websiteCode)->getQty();
        }

        return $qty;
    }

    /**
     * @return bool
     */
    private function isMSIEnabled()
    {
        if ($this->msiEnabled === null) {
            $this->msiEnabled = $this->moduleManager->isEnabled('Magento_Inventory');
        }

        return $this->msiEnabled;
    }

    /**
     * @param $productSku
     * @param $websiteCode
     *
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     *
     * @throws NoSuchEntityException
     */
    private function getStockItem($productSku, $websiteCode)
    {
        return $this->stockRegistry->getStockItemBySku($productSku, $websiteCode);
    }

    /**
     * For MSI. Need to get negative qty.
     * Emulate \Magento\InventoryReservations\Model\ResourceModel\GetReservationsQuantity::execute
     *
     * @param string $productSku
     * @param string $websiteCode
     *
     * @return float|int
     */
    public function getMsiQty($productSku, $websiteCode)
    {
        if (!isset($this->qty[$websiteCode][$productSku])) {
            $this->qty[$websiteCode][$productSku] = $this->getItemQty($productSku, $this->getSourceCodes($websiteCode))
                + $this->getReservationQty($productSku, $this->getStockId($websiteCode));
        }

        return $this->qty[$websiteCode][$productSku];
    }

    /**
     * @param string[] $productSkus
     * @param string $websiteCode
     * @return string[]
     */
    public function getMsiSalable(array $productSkus, string $websiteCode): array
    {
        $stockId = $this->getStockId($websiteCode);
        if ($stockId === Stock::DEFAULT_STOCK_ID) {
            $table = 'cataloginventory_stock_status';
            $column = 'stock_status';
            $joinCondition = [
                ['cpe' => $this->getTable('catalog_product_entity')],
                'stock.product_id = cpe.entity_id',
                []
            ];
        } else {
            $table = sprintf('inventory_stock_%d', $stockId);
            $column = 'is_salable';
        }

        $select = $this->getConnection()->select()->from(
            ['stock' => $this->getTable($table)],
            [new Zend_Db_Expr('sku'), $column]
        )->where('sku IN (?)', $productSkus);

        if (isset($joinCondition)) {
            $select->join(...$joinCondition);
        }

        return $this->getConnection()->fetchPairs($select);
    }

    private function getItemQty(string $productSku, array $sourceCodes): float
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable('inventory_source_item'), ['SUM(quantity)'])
            ->where('source_code IN (?)', $sourceCodes)
            ->where('sku = ?', $productSku)
            ->group('sku');

        return (float) $this->getConnection()->fetchOne($select);
    }

    public function getItemQtyBySource(string $productSku, string $sourceCode): float
    {
        if (!isset($this->qtyBySource[$sourceCode][$productSku])) {
            $this->qtyBySource[$sourceCode][$productSku] = $this->getItemQty($productSku, [$sourceCode]);
        }
        return $this->qtyBySource[$sourceCode][$productSku];
    }

    /**
     * For MSI.
     *
     * @param string $websiteCode
     *
     * @return int
     */
    public function getStockId($websiteCode)
    {
        if (!isset($this->stockIds[$websiteCode])) {
            $select = $this->getConnection()->select()
                ->from($this->getTable('inventory_stock_sales_channel'), ['stock_id'])
                ->where('type = \'website\' AND code = ?', $websiteCode);

            $this->stockIds[$websiteCode] = (int)$this->getConnection()->fetchOne($select);
        }

        return $this->stockIds[$websiteCode];
    }

    /**
     * For MSI.
     *
     * @param string $websiteCode
     * @return array
     */
    public function getSourceCodes(string $websiteCode)
    {
        if (!isset($this->sourceCodes[$websiteCode])) {
            $select = $this->getConnection()->select()
                ->from($this->getTable('inventory_source_stock_link'), ['source_code'])
                ->where('stock_id = ?', $this->getStockId($websiteCode));

            $this->sourceCodes[$websiteCode] = $this->getConnection()->fetchCol($select);
        }

        return $this->sourceCodes[$websiteCode];
    }

    public function getAllSources(): array
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable('inventory_source'), ['source_code', 'name']);

        return $this->getConnection()->fetchPairs($select);
    }

    /**
     * For MSI.
     *
     * @param string $sku
     * @param int $stockId
     *
     * @return int|string
     */
    private function getReservationQty($sku, $stockId)
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable('inventory_reservation'), ['quantity' => 'SUM(quantity)'])
            ->where('sku = ?', $sku)
            ->where('stock_id = ?', $stockId)
            ->limit(1);

        $reservationQty = $this->getConnection()->fetchOne($select);
        if ($reservationQty === false) {
            $reservationQty = 0;
        }

        return $reservationQty;
    }

    public function addStockStatusToCollection(ProductCollection $productCollection, string $websiteCode): void
    {
        if (!$productCollection->getFlag(static::CUSTOM_IN_STOCK_COLUMN)) {
            $table = 'cataloginventory_stock_status';
            $column = 'stock_status';
            $priJoinField = 'entity_id';
            $refJoinField = 'product_id';

            if ($this->isMSIEnabled()) {
                $stockId = $this->getStockId($websiteCode);
                $msiTable = sprintf('inventory_stock_%d', $stockId);

                if ($stockId !== Stock::DEFAULT_STOCK_ID
                    && $this->getConnection()->isTableExists($this->getTable($msiTable))
                ) {
                    $table = $msiTable;
                    $column = 'is_salable';
                    $priJoinField = $refJoinField = 'sku';
                }
            }

            $productCollection->getSelect()->join(
                ['amasty_stock_status' => $this->getTable($table)],
                sprintf('e.%s = amasty_stock_status.%s', $priJoinField, $refJoinField),
                [static::CUSTOM_IN_STOCK_COLUMN => $column]
            );
            $productCollection->setFlag(static::CUSTOM_IN_STOCK_COLUMN, true);
        }
    }
}
