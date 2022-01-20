<?php

namespace StripeIntegration\Payments\Model\Stripe;

class Product extends StripeObject
{
    protected $objectSpace = 'products';

    public function fromOrderItem($item)
    {
        $data = [
            'name' => $item->getName()
        ];

        $this->upsert($item->getProductId(), $data);

        if (!$this->object)
            throw new \Magento\Framework\Exception\LocalizedException(__("The product \"%1\" could not be created in Stripe: %2", $item->getName(), $this->lastError));

        return $this;
    }

}
