<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Product;

/**
 * Used for save qty of product, which after added for current DB qty.
 * @see GetQty
 */
class QtyRegistry
{
    /**
     * @var array
     */
    private $productQty = [];

    public function add(int $productId, float $qty): void
    {
        if (isset($this->productQty[$productId])) {
            $this->productQty[$productId] += $qty;
        } else {
            $this->productQty[$productId] = $qty;
        }
    }

    public function get(int $productId): float
    {
        return $this->productQty[$productId] ?? 0;
    }
}
