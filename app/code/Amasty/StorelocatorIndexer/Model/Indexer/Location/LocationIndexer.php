<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorelocatorIndexer
 */


namespace Amasty\StorelocatorIndexer\Model\Indexer\Location;

use Amasty\StorelocatorIndexer\Model\Indexer\AbstractIndexer;
use Magento\Framework\Exception\LocalizedException;

class LocationIndexer extends AbstractIndexer
{
    /**
     * @param int[] $ids
     *
     * @throws LocalizedException
     */
    protected function doExecuteList($ids)
    {
        $this->indexBuilder->reindexByIds($ids);
    }

    /**
     * @param int $id
     *
     * @throws LocalizedException
     */
    protected function doExecuteRow($id)
    {
        $this->indexBuilder->reindexById($id);
    }
}
