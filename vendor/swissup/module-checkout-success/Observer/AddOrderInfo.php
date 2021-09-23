<?php
namespace Swissup\CheckoutSuccess\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;

class AddOrderInfo implements ObserverInterface
{
    /**
     * Path to module enabled store config
     *
     * @var string
     */
    const CONFIG_PATH = 'success_page/general/enabled';
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;
    /*
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->registry = $registry;
        $this->orderFactory = $orderFactory;
        $this->scopeConfig = $scopeConfig;
    }
    /**
     * Add additional order info to success page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $enabled = $this->scopeConfig->getValue(self::CONFIG_PATH, ScopeInterface::SCOPE_STORE);
        if (!$enabled || $this->registry->registry('current_order'))
        {
            return $this;
        }

        $orderId = $observer->getOrderIds()[0];
        $order = $this->orderFactory->create()->load($orderId);
        $this->registry->register('current_order', $order);
    }
}
