<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocator\Api;

use Amasty\Storelocator\Model\ResourceModel\Location\Collection;

interface LocationCollectionForMapProviderInterface
{
    /**
     * @return Collection
     */
    public function getCollection(): Collection;
}
