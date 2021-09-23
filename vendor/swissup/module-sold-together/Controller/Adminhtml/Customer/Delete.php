<?php

namespace Swissup\SoldTogether\Controller\Adminhtml\Customer;

class Delete extends \Swissup\SoldTogether\Controller\Adminhtml\Order\Delete
{
    /**
     * ACL resource name
     *
     * @var string
     */
    protected $_aclRecourseName = 'Swissup_SoldTogether::customer_delete';

    /**
     * Get model soldtogether customer
     *
     * @return \Swissup\SoldTogether\Model\Customer
     */
    public function getModel()
    {
        return $this->_objectManager->create('Swissup\SoldTogether\Model\Customer');
    }
}
