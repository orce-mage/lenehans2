<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocator\Api\Filter;

use Magento\Framework\Api\SearchCriteriaBuilder;

interface LocationProductFilterInterface
{
    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $productIds
     * @param int $storeId
     * @return void
     */
    public function apply(SearchCriteriaBuilder $searchCriteriaBuilder, array $productIds, int $storeId): void;
}
