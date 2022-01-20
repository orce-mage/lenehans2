<?php

namespace StripeIntegration\Payments\Model\ResourceModel\PaymentIntent;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'pi_id';

    protected function _construct()
    {
        $this->_init('StripeIntegration\Payments\Model\PaymentIntent', 'StripeIntegration\Payments\Model\ResourceModel\PaymentIntent');
    }
}
