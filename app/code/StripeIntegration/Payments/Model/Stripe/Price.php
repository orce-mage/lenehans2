<?php

namespace StripeIntegration\Payments\Model\Stripe;

class Price extends StripeObject
{
    protected $objectSpace = 'prices';

    public function fromOrderItem($item, $order, $stripeProduct)
    {
        $data = [
            'currency' => strtoupper($order->getOrderCurrencyCode()),
            'unit_amount' => $this->helper->convertMagentoAmountToStripeAmount($item->getPrice(), $order->getOrderCurrencyCode(), $order),
            'product' => $stripeProduct->id
        ];

        $priceId = implode('-', $data);

        if (!$this->lookupSingle($priceId))
        {
            $data['lookup_key'] = $priceId;
            $this->createObject($data);
        }

        if (!$this->object)
            throw new \Magento\Framework\Exception\LocalizedException(__("The price for product \"%1\" could not be created in Stripe: %2", $item->getName(), $this->lastError));

        return $this;
    }
}
