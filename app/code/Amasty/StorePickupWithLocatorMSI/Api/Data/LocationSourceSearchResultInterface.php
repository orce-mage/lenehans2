<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Api\Data;

interface LocationSourceSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Gets collection items.
     *
     * @return \Amasty\StorePickupWithLocatorMSI\Api\Data\LocationWithQtyInterface[] Array of collection items.
     */
    public function getItems();

    /**
     * Set collection items.
     *
     * @param \Amasty\StorePickupWithLocatorMSI\Api\Data\LocationWithQtyInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
