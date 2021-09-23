<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Model\ResourceModel\VersionHistory;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = \Wyomind\Framework\Api\Data\VersionHistoryInterface::ID;

    /**
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init(\Wyomind\Framework\Model\VersionHistory::class, \Wyomind\Framework\Model\ResourceModel\VersionHistory::class);
    }
}
