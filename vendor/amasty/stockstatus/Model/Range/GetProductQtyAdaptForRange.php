<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Range;

use Amasty\Stockstatus\Model\ConfigProvider;
use Amasty\Stockstatus\Model\Product\GetQty;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;

class GetProductQtyAdaptForRange
{
    /**
     * @var GetQty
     */
    private $getQty;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    public function __construct(
        GetQty $getQty,
        ConfigProvider $configProvider,
        StockRegistryInterface $stockRegistry
    ) {
        $this->getQty = $getQty;
        $this->configProvider = $configProvider;
        $this->stockRegistry = $stockRegistry;
    }

    public function execute(ProductInterface $product): float
    {
        $productQty = $this->getQty->execute($product);

        if ($this->configProvider->isThresholdForRanges()) {
            $minQty = $this->stockRegistry->getStockItemBySku($product->getData('sku'))->getMinQty();
            $productQty -= $minQty;
        }

        return $productQty;
    }
}
