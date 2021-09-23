<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Plugin\HidePrice\Helper;

use Amasty\Stockstatus\Model\Stockstatus\IsHidePrice;
use Magento\Catalog\Api\Data\ProductInterface;

class Data
{
    /**
     * @var IsHidePrice
     */
    private $isHidePrice;

    public function __construct(IsHidePrice $isHidePrice)
    {
        $this->isHidePrice = $isHidePrice;
    }

    public function afterCheckStockStatus($subject, bool $result, bool $resultBefore, ProductInterface $product): bool
    {
        if ($this->isHidePrice->execute($product)) {
            $result = true;
        }

        return $result;
    }
}
