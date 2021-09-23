<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Range;

use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Catalog\Api\Data\ProductInterface;

class IsAvailableForProduct
{
    private $nonRangesTypes = [
        BundleType::TYPE_CODE
    ];

    public function execute(ProductInterface $product): bool
    {
        return !in_array($product->getTypeId(), $this->nonRangesTypes);
    }
}
