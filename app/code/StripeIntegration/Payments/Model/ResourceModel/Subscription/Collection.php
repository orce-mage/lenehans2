<?php

namespace StripeIntegration\Payments\Model\ResourceModel\Subscription;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init('StripeIntegration\Payments\Model\Subscription', 'StripeIntegration\Payments\Model\ResourceModel\Subscription');
    }

    public function getByOrderIncrementId($incrementId)
    {
        $this->clear()->getSelect()->reset(\Zend_Db_Select::WHERE);

        $collection = $this->addFieldToSelect('*')
                    ->addFieldToFilter('order_increment_id', ['eq' => $incrementId])
                    ->setOrder('created_at','ASC');

        return $collection;
    }
}
