<?php

namespace StripeIntegration\Payments\Model\ResourceModel\Coupon;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init('StripeIntegration\Payments\Model\Coupon', 'StripeIntegration\Payments\Model\ResourceModel\Coupon');
    }
}
