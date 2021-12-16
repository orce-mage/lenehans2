<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-sorting
 * @version   1.1.14
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Sorting\Factor;

use Magento\Framework\Module\Manager as ModuleManager;
use Mirasvit\Sorting\Api\Data\IndexInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Model\Indexer\FactorIndexer;

class StockFactor implements FactorInterface
{
    private $indexer;

    private $moduleManager;

    public function __construct(
        FactorIndexer $indexer,
        ModuleManager $moduleManager
    ) {
        $this->indexer       = $indexer;
        $this->moduleManager = $moduleManager;
    }

    public function getName(): string
    {
        return 'Stock Status';
    }

    public function getDescription(): string
    {
        return 'Rank products based on stock status.';
    }

    public function getUiComponent(): ?string
    {
        return null;
    }

    public function reindex(RankingFactorInterface $rankingFactor, array $productIds): void
    {
        if ($this->moduleManager->isEnabled('Magento_Inventory')) {
            $result = $this->getInventoryStock($productIds);
        } else {
            $result = $this->getDefaultStock($productIds);
        }

        $this->indexer->process($rankingFactor, $productIds, function () use ($result) {
            foreach ($result as $row) {
                $value = $row['value'];
                $score = $value ? IndexInterface::MAX : IndexInterface::MIN;

                $this->indexer->add((int)$row['entity_id'], $score, $value, (int)(isset($row['store_id']) ? $row['store_id'] : 0));
            }
        });
    }

    private function getDefaultStock(array $productIds): array
    {
        $resource   = $this->indexer->getResource();
        $connection = $resource->getConnection();

        $select = $connection->select();
        $select->from(
            ['e' => $resource->getTableName('catalog_product_entity')],
            ['entity_id', 'type_id']
        )->joinInner(
            ['stock' => $resource->getTableName('cataloginventory_stock_status')],
            'stock.product_id = e.entity_id',
            ['value' => 'stock_status']
        )->group('e.entity_id');

        if ($productIds) {
            $select->where('e.entity_id IN (?)', $productIds);
        }

        $stmt = $connection->query($select);

        return $stmt->fetchAll();
    }

    private function getInventoryStock(array $productIds): array
    {
        $resource   = $this->indexer->getResource();
        $connection = $resource->getConnection();

        $select = $connection->select();

        $select->from(
            ['store' => $resource->getTableName('store')],
            ['website_id', 'store_id']
        )->joinInner(
            ['store_website' => $resource->getTableName('store_website')],
            'store.website_id = store_website.website_id',
            null
        )->joinInner(
            ['stock' => $resource->getTableName('inventory_stock_sales_channel')],
            'store_website.code = stock.code',
            null
        )->joinInner(
            ['source_link' => $resource->getTableName('inventory_source_stock_link')],
            'stock.stock_id = source_link.stock_id',
            ['stock_id']
        );

        $stmt = $connection->query($select);

        $result = [];

        foreach ($stmt->fetchAll() as $row) {

            $stockSelect = $connection->select();
            $stockId     = $row['stock_id'];

            if ($connection->tableColumnExists($resource->getTableName("inventory_stock_$stockId"), 'product_id')) {
                $stockSelect->from(
                    ['stock' => $resource->getTableName("inventory_stock_$stockId")],
                    ['value' => 'is_salable', 'entity_id' => 'product_id']
                );

                if ($productIds) {
                    $stockSelect->where('stock.product_id IN (?)', $productIds);
                }
            } else {
                $stockSelect->from(
                    ['stock' => $resource->getTableName("inventory_stock_$stockId")],
                    ['value' => 'is_salable']
                )->joinInner(
                    ['e' => $resource->getTableName('catalog_product_entity')],
                    'e.sku = stock.sku',
                    ['entity_id']
                );

                if ($productIds) {
                    $stockSelect->where('e.entity_id IN (?)', $productIds);
                }
            }

            foreach ($connection->query($stockSelect)->fetchAll() as $stock) {
                $stock    = array_merge($stock, $row);
                $result[] = $stock;
            };
        }

        return $result;
    }
}
