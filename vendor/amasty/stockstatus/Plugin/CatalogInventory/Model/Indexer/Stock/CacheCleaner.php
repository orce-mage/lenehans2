<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Plugin\CatalogInventory\Model\Indexer\Stock;

use Amasty\Stockstatus\Model\Resources\CheckIfProductsManageStock;
use Magento\CatalogInventory\Model\Configuration as InventoryConfig;
use Magento\CatalogInventory\Model\Indexer\Stock\CacheCleaner as NativeCacheCleaner;
use Magento\Catalog\Model\Product;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\Event\ManagerInterface;

class CacheCleaner
{
    /**
     * @var CacheContext
     */
    private $cacheContext;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var InventoryConfig
     */
    private $inventoryConfig;

    /**
     * @var CheckIfProductsManageStock
     */
    private $checkIfProductsManageStock;

    public function __construct(
        CacheContext $cacheContext,
        ManagerInterface $eventManager,
        InventoryConfig $inventoryConfig,
        CheckIfProductsManageStock $checkIfProductsManageStock
    ) {
        $this->cacheContext = $cacheContext;
        $this->eventManager = $eventManager;
        $this->inventoryConfig = $inventoryConfig;
        $this->checkIfProductsManageStock = $checkIfProductsManageStock;
    }

    /**
     * If products with manage_stock=1, need clear cache, because qty reduce always.
     *
     * @param NativeCacheCleaner $subject
     * @param null $result
     * @param array $productIds
     * @return void
     */
    public function afterClean(NativeCacheCleaner $subject, $result, array $productIds): void
    {
        $productIds = $this->checkIfProductsManageStock->execute(
            $productIds,
            (bool) $this->inventoryConfig->getManageStock()
        );

        if (!empty($productIds)) {
            $this->cacheContext->registerEntities(Product::CACHE_TAG, $productIds);
            $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
        }
    }
}
