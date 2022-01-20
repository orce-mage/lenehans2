<?php

namespace StripeIntegration\Payments\Helper;

use StripeIntegration\Payments\Helper\Logger;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class InitialFee
{
    public $serializer = null;

    public function __construct(
        \StripeIntegration\Payments\Helper\Generic $paymentsHelper
    ) {
        $this->paymentsHelper = $paymentsHelper;
    }

    public function getTotalInitialFeeForCreditmemo($creditmemo, $orderRate = true)
    {
        $payment = $creditmemo->getOrder()->getPayment();

        if (empty($payment))
            return 0;

        if (!$this->paymentsHelper->supportsSubscriptions($payment->getMethod()))
            return 0;

        if ($payment->getAdditionalInformation("is_recurring_subscription") || $payment->getAdditionalInformation("remove_initial_fee"))
            return 0;

        $items = $creditmemo->getAllItems();

        if ($orderRate)
            $rate = $creditmemo->getBaseToOrderRate();
        else
            $rate = 1;

        return $this->getInitialFeeForItems($items, $rate);
    }

    public function getTotalInitialFeeForInvoice($invoice, $invoiceRate = true)
    {
        $payment = $invoice->getOrder()->getPayment();

        if (empty($payment))
            return 0;

        if (!$this->paymentsHelper->supportsSubscriptions($payment->getMethod()))
            return 0;

        if ($payment->getAdditionalInformation("is_recurring_subscription") || $payment->getAdditionalInformation("remove_initial_fee"))
            return 0;

        $items = $invoice->getAllItems();

        if ($invoiceRate)
            $rate = $invoice->getBaseToOrderRate();
        else
            $rate = 1;

        return $this->getInitialFeeForItems($items, $rate);
    }

    public function getTotalInitialFeeFor($items, $quote, $quoteRate = 1)
    {
        if ($quote->getIsRecurringOrder() || $quote->getRemoveInitialFee())
            return 0;

        return $this->getInitialFeeForItems($items, $quoteRate);
    }

    public function getInitialFeeForItems($items, $rate)
    {
        $total = 0;

        foreach ($items as $item)
        {
            $qty = $this->paymentsHelper->getItemQty($item);
            $productId = $item->getProductId();
            $total += $this->getInitialFeeForProductId($productId, $rate, $qty);
        }
        return $total;
    }

    public function getInitialFeeForProductId($productId, $rate, $qty)
    {
        $product = $this->paymentsHelper->loadProductById($productId);

        if (!in_array($product->getTypeId(), ["simple", "virtual"]))
            return 0;

        if (!is_numeric($product->getStripeSubInitialFee()))
            return 0;

        return round($product->getStripeSubInitialFee() * $rate, 2) * $qty;
    }

    public function getAdditionalOptionsForChildrenOf($item)
    {
        $additionalOptions = array();

        foreach ($item->getQtyOptions() as $productId => $option)
        {
            $additionalOptions = array_merge($additionalOptions, $this->getAdditionalOptionsForProductId($productId, $item->getQty()));
        }

        return $additionalOptions;
    }

    public function getAdditionalOptionsForProductId($productId, $qty)
    {
        $profile = $this->getSubscriptionProfileForProductId($productId);
        if (!$profile)
            return array();

        $additionalOptions = array(
            array(
                'label' => 'Repeats Every',
                'value' => $profile['repeat_every']
            )
        );

        $quote = $this->paymentsHelper->getSessionQuote();

        if ($profile['initial_fee'] && is_numeric($profile['initial_fee']) && $profile['initial_fee'] > 0)
        {
            $additionalOptions[] = array(
                'label' => 'Initial Fee',
                'value' => $this->paymentsHelper->addCurrencySymbol($profile['initial_fee'] * $qty)
            );
        }

        if ($profile['trial_days'] && is_numeric($profile['trial_days']) && $profile['trial_days'] > 0)
        {
            $additionalOptions[] = array(
                'label' => 'Trial Period',
                'value' => $profile['trial_days'] . " days"
            );
        }

        return $additionalOptions;
    }

    public function getSubscriptionProfileForProductId($productId)
    {
        $product = $this->paymentsHelper->loadProductById($productId);

        if (!$product->getStripeSubEnabled())
            return null;

        $profile['initial_fee'] = $product->getStripeSubInitialFee();

        $intervalCount = $product->getStripeSubIntervalCount();
        $interval = ucfirst($product->getStripeSubInterval());
        $plural = ($intervalCount > 1 ? 's' : '');

        $profile['repeat_every'] = "$intervalCount $interval$plural";

        $trialDays = $product->getStripeSubTrial();
        if ($trialDays && is_numeric($trialDays) && $trialDays > 0)
            $profile['trial_days'] = round($trialDays);
        else
            $profile['trial_days'] = false;

        return $profile;
    }
}
