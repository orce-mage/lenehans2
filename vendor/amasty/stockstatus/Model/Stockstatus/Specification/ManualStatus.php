<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Stockstatus\Specification;

use Amasty\Stockstatus\Model\Source\StockStatus;
use Magento\Catalog\Api\Data\ProductInterface;

class ManualStatus implements SpecificationInterface
{
    public function resolve(ProductInterface $product): ?int
    {
        return $product->hasData(StockStatus::ATTIRUBTE_CODE)
            ? (int) $product->getData(StockStatus::ATTIRUBTE_CODE)
            : null;
    }
}
