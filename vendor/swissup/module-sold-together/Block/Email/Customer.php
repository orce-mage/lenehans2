<?php
/**
 * Copyright © 2015 Swissup. All rights reserved.
 */
namespace Swissup\SoldTogether\Block\Email;

class Customer extends Order
{
    /**
     * Name of table in DB
     *
     * @var string
     */
    protected $_tableName = 'swissup_soldtogether_customer';

    /**
     * Get product limit in email
     *
     * @param  string $key
     * @return string
     */
    public function getEmailLimit()
    {
        return $this->_scopeConfig->getValue(
            "soldtogether/email/customer_count",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
