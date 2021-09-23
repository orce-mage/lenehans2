<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface QuoteSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return QuoteInterface[]
     */
    public function getItems();

    /**
     * @param QuoteInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
