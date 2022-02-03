<?php

namespace Amasty\Faq\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface QuestionSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get FAQ questions list
     *
     * @return \Amasty\Faq\Api\Data\QuestionInterface[]
     */
    public function getItems();

    /**
     * Set FAQ questions list
     *
     * @param \Amasty\Faq\Api\Data\QuestionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
