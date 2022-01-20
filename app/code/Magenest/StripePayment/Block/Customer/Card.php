<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Block\Customer;

use Magento\Catalog\Block\Product\Context;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Sales\Model\OrderFactory;
use Stripe;

class Card extends \Magento\Framework\View\Element\Template
{
    protected $_currentCustomer;

    protected $_helper;

    protected $_orderFactory;

    protected $_cardFactory;

    protected $_config;

    protected $_customerSession;

    public function __construct(
        Context $context,
        CurrentCustomer $currentCustomer,
        \Magenest\StripePayment\Model\CardFactory $cardFactory,
        \Magenest\StripePayment\Helper\Config $config,
        OrderFactory $orderFactory,
        \Magento\Customer\Model\Session $customerSession,
        array $data
    ) {
        $this->_currentCustomer = $currentCustomer;
        $this->_orderFactory = $orderFactory;
        $this->_cardFactory = $cardFactory;
        $this->_config = $config;
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    public function getDataCard()
    {
        $customer_id = $this->_customerSession->getCustomerId();
        $model = $this->_cardFactory->create()
            ->getCollection()
            ->addFieldToFilter('magento_customer_id', $customer_id)
            ->getData();
        $this->checkFlag = count($model);

        return $model;
    }

    public function getStripePublickey()
    {
        return $this->_config->getPublishableKey();
    }
}
