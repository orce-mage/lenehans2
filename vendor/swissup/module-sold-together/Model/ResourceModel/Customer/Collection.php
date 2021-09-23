<?php
namespace Swissup\SoldTogether\Model\ResourceModel\Customer;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = 'relation_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Swissup\SoldTogether\Model\Customer',
            'Swissup\SoldTogether\Model\ResourceModel\Customer'
        );
    }
}
