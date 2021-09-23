<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Stockstatus\Specification;

use Magento\Catalog\Api\Data\ProductInterface;

interface SpecificationInterface
{
    /**
     * Check if product suitable for current specification.
     *
     * @param ProductInterface $product
     * @return int|null
     */
    public function resolve(ProductInterface $product): ?int;
}
