<?php

namespace Searchanise\SearchAutocomplete\Model\ResourceModel;

class Queue extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * {@inheritDoc}
     *
     * @see \Magento\Framework\Model\ResourceModel\AbstractResource::_construct()
     */
    protected function _construct()
    {
        $this->_init('searchanise_queue', 'queue_id');
    }
}
