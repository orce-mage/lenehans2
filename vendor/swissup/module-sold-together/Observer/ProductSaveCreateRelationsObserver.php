<?php
namespace Swissup\SoldTogether\Observer;

class ProductSaveCreateRelationsObserver extends AbstractObserver
{
    /**
     * Create order relations
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $controller = $observer->getEvent()->getController();
        $product = $observer->getEvent()->getProduct();
        $linksData = $controller->getRequest()->getParam('links');
        if (!$linksData) {
            return $this;
        }

        $orderData = $linksData['sold_order'] ?? [];
        $this->orderModel->updateProductRelations(
            $orderData,
            $product->getId(),
            $product->getName()
        );

        $customerData = $linksData['sold_customer'] ?? [];
        $this->customerModel->updateProductRelations(
            $customerData,
            $product->getId(),
            $product->getName()
        );

        return $this;
    }
}
