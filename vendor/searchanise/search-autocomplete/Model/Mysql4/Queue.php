<?php

namespace Searchanise\SearchAutocomplete\Model\Mysql4;

class Queue extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Searchanise\SearchAutocomplete\Model\ResourceModel\Queue::class);
    }
}
