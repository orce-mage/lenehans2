<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\Code\ResourceModel\Grid;

use Amasty\GiftCard\Api\Data\CodeInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends SearchResult
{
    /**
     * @param int $codePool
     *
     * @return Collection
     */
    public function addCodePoolField(int $codePool): Collection
    {
        return $this->addFieldToFilter(CodeInterface::CODE_POOL_ID, $codePool);
    }

    /**
     * @return \Magento\Framework\Api\Search\DocumentInterface[]
     */
    public function getItems()
    {
        $this->_setIsLoaded(false);
        $this->_items = [];
        $searchCriteria = $this->getSearchCriteria();
        $this->setPageSize($searchCriteria->getPageSize());
        $this->setCurPage($searchCriteria->getCurrentPage());

        return parent::getItems();
    }
}
