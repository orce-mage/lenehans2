<?php
/**
 * Copyright © 2015 Swissup. All rights reserved.
 */

namespace Swissup\SoldTogether\Helper;

class Stock extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var string
     */
    protected $currentStockId;

    /**
     * @var \Magento\CatalogInventory\Model\StockFactory
     */
    protected $stockFactory;

    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    protected $inventoryStockHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\CatalogInventory\Model\StockFactory $stockFactory
     * @param \Magento\CatalogInventory\Helper\Stock $inventoryStockHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\CatalogInventory\Model\StockFactory $stockFactory,
        \Magento\CatalogInventory\Helper\Stock $inventoryStockHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->stockFactory = $stockFactory;
        $this->inventoryStockHelper = $inventoryStockHelper;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Get current stock ID
     *
     * @return string
     */
    public function getCurrentStockId()
    {
        if ($this->currentStockId === null) {
            $website = $this->storeManager->getWebsite();
            $stock = $this->stockFactory->create();
            $stock->load($website->getId(), 'website_id');
            if ($stock->getId()) {
                $this->currentStockId = $stock->getId();
            } else {
                // stock not found; use default stock
                // hardcoded in Magento\CatalogInventory\Setup\InstallData::install
                $this->currentStockId = '1';
            }
        }

        return $this->currentStockId;
    }

    /**
     * Add 'in stock' filter to collection
     *
     * @param Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection $collection
     */
    public function addInStockFilterToCollection($collection)
    {
        $this->inventoryStockHelper->addInStockFilterToCollection($collection);
        if ($this->_moduleManager->isEnabled('Magento_InventoryCatalog')) {
            // Magento_InventoryCatalog selects data respecting stock_id.
            // Plugin `adapt_add_in_stock_filter_to_collection` handles this.
            return;
        }

        // select only records for current stock
        $stockTableAlias = $collection::ATTRIBUTE_TABLE_ALIAS_PREFIX . 'inventory_in_stock';
        $collection->getSelect()->where(
            "$stockTableAlias.stock_id = ?",
            $this->getCurrentStockId()
        );
    }
}
