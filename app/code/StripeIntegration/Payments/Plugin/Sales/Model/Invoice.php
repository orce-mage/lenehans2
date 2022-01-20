<?php

namespace StripeIntegration\Payments\Plugin\Sales\Model;

class Invoice
{
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory
    )
    {
        $this->productFactory = $productFactory;
    }

    public function aroundCanCapture($subject, \Closure $proceed)
    {
        // Deprecated as of v2.7.1
        return /* !$this->hasSubscriptions($subject) && */ $proceed();
    }

    public function aroundCanCancel($subject, \Closure $proceed)
    {
        // Deprecated as of v2.7.1
        return /* !$this->hasSubscriptions($subject) && */ $proceed();
    }

    public function isUnpaid($subject)
    {
        $transactionId = $subject->getTransactionId();
        if (empty($transactionId))
            return true;

        if (strpos($transactionId, "sub_") !== false) // Trialing subscription invoice
            return true;

        return false;
    }

    public function hasSubscriptions($subject)
    {
        $items = $subject->getAllItems();

        foreach ($items as $item)
        {
            if (!$item->getProductId())
                continue;

            $product = $this->loadProductById($item->getProductId());
            if ($product->getStripeSubEnabled())
                return true;
        }

        return false;
    }

    public function loadProductById($productId)
    {
        if (!isset($this->products))
            $this->products = [];

        if (!empty($this->products[$productId]))
            return $this->products[$productId];

        $this->products[$productId] = $this->productFactory->create()->load($productId);

        return $this->products[$productId];
    }

}
