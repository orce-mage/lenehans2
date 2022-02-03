<?php

namespace Amasty\Faq\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface CategorySearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get FAQ categories list
     *
     * @return \Amasty\Faq\Api\Data\CategoryInterface[]
     */
    public function getItems();

    /**
     * Set FAQ categories list
     *
     * @param \Amasty\Faq\Api\Data\CategoryInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
