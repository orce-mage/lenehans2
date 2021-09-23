<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Api;

interface LocationSourceManagementInterface
{
    /**
     * @param int $productId
     * @return \Amasty\StorePickupWithLocatorMSI\Api\Data\LocationSourceSearchResultInterface
     */
    public function getLocationsByProduct(
        int $productId
    ): \Amasty\StorePickupWithLocatorMSI\Api\Data\LocationSourceSearchResultInterface;
}
