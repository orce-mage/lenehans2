<?php

namespace Swissup\SoldTogether\Observer;

use Magento\Framework\Event\ObserverInterface;

abstract class AbstractObserver implements ObserverInterface
{
    /**
     * @var \Swissup\SoldTogether\Model\Order
     */
    protected $orderModel;

    /**
     * @var \Swissup\SoldTogether\Model\Customer
     */
    protected $customerModel;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Swissup\SoldTogether\Model\Order $orderModel
     * @param \Swissup\SoldTogether\Model\Customer $customerModel
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Swissup\SoldTogether\Model\Order $orderModel,
        \Swissup\SoldTogether\Model\Customer $customerModel,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->orderModel = $orderModel;
        $this->customerModel = $customerModel;
        $this->scopeConfig = $scopeConfig;
    }
}
