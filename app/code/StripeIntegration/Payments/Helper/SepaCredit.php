<?php

namespace StripeIntegration\Payments\Helper;

class SepaCredit
{
    public function __construct(
        \StripeIntegration\Payments\Helper\Generic $paymentsHelper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \StripeIntegration\Payments\Helper\Webhooks $webhooksHelper,
        \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender $orderCommentSender,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    )
    {
        $this->paymentsHelper = $paymentsHelper;
        $this->pricingHelper = $pricingHelper;
        $this->orderCommentSender = $orderCommentSender;
        $this->webhooksHelper = $webhooksHelper;
        $this->priceCurrency = $priceCurrency;
    }

    public function onTransactionCreated($order, $sourceId, $stripeCustomerId, $object)
    {
        $formattedAmount = $this->paymentsHelper->getFormattedStripeAmount($object["amount"], $object["currency"], $order);

        if ($object["status"] != "succeeded")
        {
            $comment = __("An unsuccessful transaction for the amount of %1 has been attempted by the customer.", $formattedAmount);
            $order->addStatusToHistory($status = false, $comment, $isCustomerNotified = false);
            $order->save();
            return;
        }

        $source = \Stripe\Source::retrieve($sourceId);
        $totalPaid = $this->paymentsHelper->convertStripeAmountToOrderAmount($source->receiver->amount_received, $source->currency, $order);
        $baseTotalPaid = $this->paymentsHelper->convertStripeAmountToBaseOrderAmount($source->receiver->amount_received, $source->currency, $order);
        $this->updateRefundInfo($source, $order, $object);

        $order->setTotalPaid($totalPaid);
        $order->setBaseTotalPaid($baseTotalPaid);
        $order->save();

        $object = [
            'id' => $source->id,
            'type' => $source->type,
            'currency' => $source->currency,
            $source->type => []
        ];
        $iban = $source->sepa_credit_transfer->iban;
        $bic = $source->sepa_credit_transfer->bic;

        if ($this->isUnderPaid($order, $totalPaid))
        {
            $dueAmount = $this->paymentsHelper->addCurrencySymbol($order->getTotalDue(), $order->getOrderCurrencyCode());
            $comment = __("Thank you for your payment of %1. Your order is still pending! To complete the payment, please transfer a further %2 to the bank account with IBAN %3 and BIC code %4.", $formattedAmount, $dueAmount, $iban, $bic);
            $this->notifyCustomer($order, $comment);
        }
        else if ($this->isPaid($order, $totalPaid) || $this->isOverPaid($order, $totalPaid))
        {
            if (!$order->canInvoice())
            {
                $this->createOverPayment($source, $stripeCustomerId, $order);
                $comment = __("A payment of %1 has been received, however this order is already being processed. Please hold while we review your order for possible overpayments.", $formattedAmount);
                $order->addStatusToHistory($status = false, $comment, $isCustomerNotified = true);
                $order->save();
                return;
            }

            $charge = $this->webhooksHelper->charge($order, $object, true, false);
            if ($charge->status == "succeeded")
            {
                if ($this->isPaid($order, $totalPaid))
                {
                    $comment = __("Thank you for your payment of %1. Your order is now complete and will be processed soon.", $formattedAmount);
                }
                else if ($this->isOverPaid($order, $totalPaid))
                {
                    $overpaymentAmount = $this->paymentsHelper->addCurrencySymbol($totalPaid - $order->getGrandTotal(), $order->getOrderCurrencyCode());
                    $comment = __("Thank you for your payment of %1. It seems that the order has been overpaid by %2 and a refund for this amount is due back to your bank account. In the meantime, your order is will be processed normally.", $formattedAmount, $overpaymentAmount);
                }

                $this->notifyCustomer($order, $comment);
            }
            else
            {
                $comment = __("A final payment of %1 has been made which completes the order, however creating a charge for the order failed.", $formattedAmount);
                $order->addStatusToHistory($status = false, $comment, $isCustomerNotified = false);
                $order->save();
            }

            $this->createOverPayment($source, $stripeCustomerId, $order);
        }
    }

    public function updateRefundInfo($source, $order, $object)
    {
        if (!empty($source->sepa_credit_transfer->refund_iban))
            return;

        $address = $order->getBillingAddress();
        $details = [
            "refund_iban" => $object['sepa_credit_transfer']['sender_iban'],
            "refund_account_holder_name" => $object['sepa_credit_transfer']['sender_name'],
            "refund_account_holder_address_line1" => $address->getStreetLine(1),
            "refund_account_holder_address_line2" => $address->getStreetLine(2),
            "refund_account_holder_address_city" => $address->getCity(),
            "refund_account_holder_address_state" => $address->getRegion(),
            "refund_account_holder_address_postal_code" => $address->getPostcode(),
            // "refund_account_holder_address_country" => $address->getCountryId()
        ];
        \Stripe\Source::update($source->id, ['sepa_credit_transfer' => $details]);
    }

    public function createOverPayment($source, $stripeCustomerId, $order)
    {
        $source->refresh(); // Because a charge may have preceded
        $overpaymentAmount = $source->receiver->amount_received - $source->receiver->amount_charged - $source->amount_returned;

        if ($overpaymentAmount <= 0)
            return;

        $params = array(
            "amount" => $overpaymentAmount,
            "currency" => $source->currency,
            "source" => $source->id,
            "description" => "Overpayment for order #" . $order->getIncrementId()
        );

        if (!empty($stripeCustomerId))
            $params["customer"] = $stripeCustomerId;

        return \Stripe\Charge::create($params);
    }

    public function isUnderPaid($order, $totalPaid)
    {
        return ($order->getGrandTotal() > $totalPaid);
    }

    public function isPaid($order, $totalPaid)
    {
        return ($order->getGrandTotal() == $totalPaid);
    }

    public function isOverPaid($order, $totalPaid)
    {
        return ($order->getGrandTotal() < $totalPaid);
    }

    public function notifyCustomer($order, $comment)
    {
        try
        {
            $order->addStatusToHistory($status = false, $comment, $isCustomerNotified = true);
            $order->setCustomerNote($comment);
            $order->save();
            $this->orderCommentSender->send($order, $notify = true, $comment);
        }
        catch (\Exception $e)
        {
            $this->webhooksHelper->log($e->getMessage(), $e);
        }
    }
}
