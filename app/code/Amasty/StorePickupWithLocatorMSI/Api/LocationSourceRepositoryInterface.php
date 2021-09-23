<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Api;

use Amasty\StorePickupWithLocatorMSI\Api\Data\LocationSourceInterface;

interface LocationSourceRepositoryInterface
{
    /**
     * @param LocationSourceInterface $locationSource
     * @return LocationSourceInterface
     */
    public function save(LocationSourceInterface $locationSource): LocationSourceInterface;

    /**
     * @param LocationSourceInterface $locationSource
     * @return bool true on success
     */
    public function delete(LocationSourceInterface $locationSource): bool;
}
