<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface RuleSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \Amasty\Stockstatus\Api\Data\RuleInterface[]
     */
    public function getItems();

    /**
     * @param \Amasty\Stockstatus\Api\Data\RuleInterface[] $items
     * @return void
     */
    public function setItems(array $items);
}
