<?php
namespace Swissup\SoldTogether\Observer;

class CreateOrderRelationsObserver extends AbstractObserver
{
    /**
     * Create order relations
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if ($this->isSetFlag('order')) {
            $this->orderModel->createNewRelations($order);
        }

        if ($this->isSetFlag('customer')) {
            $this->customerModel->createNewRelations($order);
        }

        return $this;
    }

    protected function isSetFlag($relationName)
    {
        // this config is set on default (global) level only
        return $this->scopeConfig->isSetFlag(
            "soldtogether/relations/{$relationName}_on_order_create"
        );
    }
}
