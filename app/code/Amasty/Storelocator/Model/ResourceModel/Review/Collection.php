<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model\ResourceModel\Review;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Amasty\Storelocator\Model\Review;
use Amasty\Storelocator\Model\ResourceModel\Review as ReviewResource;

/**
 * Class Collection
 */
class Collection extends AbstractCollection
{
    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _construct()
    {
        $this->_init(Review::class, ReviewResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    public function joinCustomerData()
    {
        $this->getSelect()->joinLeft(
            ['customer' => $this->getTable('customer_entity')],
            'customer.entity_id = main_table.customer_id',
            [
                'email',
                'name' => new \Zend_Db_Expr("CONCAT_WS(' ', prefix, firstname, middlename, lastname, suffix)")
            ]
        );

        return $this;
    }
}
