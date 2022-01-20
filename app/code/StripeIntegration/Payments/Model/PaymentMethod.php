<?php

namespace StripeIntegration\Payments\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Api\Data\CartInterface;
use StripeIntegration\Payments\Helper;
use Magento\Framework\Validator\Exception;
use StripeIntegration\Payments\Helper\Logger;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Framework\Exception\CouldNotSaveException;

class PaymentMethod extends \Magento\Payment\Model\Method\Adapter
{
    protected $_code                 = "stripe_payments";

    protected $_isInitializeNeeded      = false;
    protected $_canUseForMultishipping  = true;

    /**
     * @param ManagerInterface $eventManager
     * @param ValueHandlerPoolInterface $valueHandlerPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param string $code
     * @param string $formBlockType
     * @param string $infoBlockType
     * @param StripeIntegration\Payments\Model\Config $config
     * @param CommandPoolInterface $commandPool
     * @param ValidatorPoolInterface $validatorPool
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Payment\Gateway\Config\ValueHandlerPoolInterface $valueHandlerPool,
        \Magento\Payment\Gateway\Data\PaymentDataObjectFactory $paymentDataObjectFactory,
        $code,
        $formBlockType,
        $infoBlockType,
        \StripeIntegration\Payments\Model\Config $config,
        \StripeIntegration\Payments\Model\Method\Checkout $checkoutMethod,
        \StripeIntegration\Payments\Helper\Generic $helper,
        \StripeIntegration\Payments\Helper\Api $api,
        \StripeIntegration\Payments\Model\PaymentIntent $paymentIntent,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Payment\Gateway\Command\CommandPoolInterface $commandPool = null,
        \Magento\Payment\Gateway\Validator\ValidatorPoolInterface $validatorPool = null
    ) {
        $this->config = $config;
        $this->checkoutMethod = $checkoutMethod;
        $this->helper = $helper;
        $this->api = $api;
        $this->customer = $helper->getCustomerModel();
        $this->paymentIntent = $paymentIntent;
        $this->checkoutHelper = $checkoutHelper;
        $this->cache = $cache;

        $this->saveCards = $config->getSaveCards();
        $this->evtManager = $eventManager;

        parent::__construct(
            $eventManager,
            $valueHandlerPool,
            $paymentDataObjectFactory,
            $code,
            $formBlockType,
            $infoBlockType,
            $commandPool,
            $validatorPool
        );
    }

    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);

        if ($this->config->getIsStripeAPIKeyError())
            $this->helper->dieWithError("Invalid API key provided");

        // From Magento 2.0.7 onwards, the data is passed in a different property
        $additionalData = $data->getAdditionalData();
        if (is_array($additionalData))
            $data->setData(array_merge($data->getData(), $additionalData));

        $info = $this->getInfoInstance();

        $this->helper->assignPaymentData($info, $data, $this->config->useStoreCurrency());

        return $this;
    }

    public function authorize(InfoInterface $payment, $amount)
    {
        if ($amount > 0)
        {
            $this->paymentIntent->confirmAndAssociateWithOrder($payment->getOrder(), $payment);
        }

        return $this;
    }

    public function capture(InfoInterface $payment, $amount)
    {
        if ($amount > 0)
        {
            // We get in here when the store is configured in Authorize Only mode and we are capturing a payment from the admin
            $token = $payment->getTransactionId();
            if (empty($token))
                $token = $payment->getLastTransId(); // In case where the transaction was not created during the checkout, i.e. with a Stripe Webhook redirect

            if ($token)
            {
                // Capture an authorized payment from the admin area
                $this->helper->capture($token, $payment, $amount, $this->config->retryWithSavedCard());
            }
            else
            {
                // Create a new payment
                $this->paymentIntent->confirmAndAssociateWithOrder($payment->getOrder(), $payment);
            }
        }

        return $this;
    }

    public function checkIfWeCanRefundMore($refundedAmount, $canceledAmount, $remainingAmount, $requestedAmount, $order, $currency)
    {
        $cents = 100;
        if ($this->helper->isZeroDecimal($currency))
            $cents = 1;

        $refundedAndCanceledAmount = $refundedAmount + $canceledAmount;

        if ($remainingAmount <= 0)
        {
            if ($refundedAndCanceledAmount < $requestedAmount)
            {
                $humanReadable1 = $this->helper->addCurrencySymbol(($requestedAmount - $refundedAndCanceledAmount) / $cents, $currency);
                $humanReadable2 = $this->helper->addCurrencySymbol($requestedAmount / $cents, $currency);
                $msg = __('%1 out of %2 could not be refunded online. Creating an offline refund instead.', $humanReadable1, $humanReadable2);
                $this->helper->addWarning($msg);
                $this->helper->addOrderComment($msg, $order);
            }

            return false;
        }

        if ($refundedAndCanceledAmount >= $requestedAmount)
        {
            return false;
        }

        return true;
    }

    public function setRefundedAmount($amount, $requestedAmount, $currency, $order)
    {
        $currency = strtolower($currency);
        $orderCurrency = strtolower($order->getOrderCurrencyCode());
        $baseCurrency = strtolower($order->getBaseCurrencyCode());

        $cents = 100;
        if ($this->helper->isZeroDecimal($currency))
            $cents = 1;

        // If this is a partial refund (2nd or 3rd), there will be an amount set already which we need to adjust instead of overwrite
        if ($order->getTotalRefunded() > 0)
        {
            $diff = $amount - $requestedAmount;
            if ($diff == 0)
                return; // Let Magento set the refund amount

            $refunded = $diff / $cents;
        }
        else
        {
            $refunded = $amount / $cents;
        }

        if ($currency == $orderCurrency)
        {
            $order->setTotalRefunded($order->getTotalRefunded() + $refunded);
            $baseRefunded = $this->helper->convertOrderAmountToBaseAmount($refunded, $currency, $order);
            $order->setBaseTotalRefunded($order->getBaseTotalRefunded() + $baseRefunded);
        }
        else if ($currency == $baseCurrency)
        {
            $rate = ($order->getBaseToOrderRate() ? $order->getBaseToOrderRate() : 1);
            $order->setTotalRefunded($order->getTotalRefunded() + round($refunded * $rate, 2));
            $order->setBaseTotalRefunded($order->getBaseTotalRefunded() + $refunded);
        }
        else
        {
            $this->helper->addWarning(__("Could not set order refunded amount because the currency %1 matches neither the order currency, nor the base currency."), $currency);
        }

        return $this;
    }

    public function cancel(InfoInterface $payment, $amount = null)
    {
        $method = $payment->getMethod();
        $useStoreCurrency = $payment->getAdditionalInformation("use_store_currency");

        if ($method == "stripe_payments" && !$useStoreCurrency)
        {
            // Authorized Only
            $amount = (empty($amount)) ? $payment->getOrder()->getBaseGrandTotal() : $amount;
            $currency = $payment->getOrder()->getBaseCurrencyCode();
        }
        else
        {
            // Captured
            $creditmemo = $payment->getCreditmemo();
            if (!empty($creditmemo))
            {
                $rate = $creditmemo->getBaseToOrderRate();
                if (!empty($rate) && is_numeric($rate) && $rate > 0)
                {
                    $amount = round($amount * $rate, 2);
                    $diff = $amount - $payment->getAmountPaid();
                    if ($diff > 0 && $diff <= 1) // Solves a currency conversion rounding issue (Magento rounds .5 down)
                        $amount = $payment->getAmountPaid();
                }
            }

            // Authorized
            $amount = (empty($amount)) ? $payment->getOrder()->getGrandTotal() : $amount;
            $currency = $payment->getOrder()->getOrderCurrencyCode();
        }

        $transactionId = $this->helper->cleanToken($payment->getLastTransId());

        // Case where an invoice is in Pending status, with no transaction ID, receiving a source.failed event which cancels the invoice.
        if (empty($transactionId))
        {
            $humanReadable = $this->helper->addCurrencySymbol($amount, $currency);
            $msg = __("Cannot refund %1 online because the order has no transaction ID. Creating an offline Credit Memo instead.", $humanReadable);
            $this->helper->addWarning($msg);
            $this->helper->addOrderComment($msg, $payment->getOrder());
            return $this;
        }

        try {
            $refundSessionId = rand(); // In case cancel() is called multiple times in one of the automated tests

            $stripe = $this->config->getStripeClient();

            $order = $payment->getOrder();
            $capturedAmount = $this->helper->getAmountCaptured($order, $refundSessionId);
            $capturableAmount = $this->helper->getAmountAuthorized($order, $refundSessionId);

            if ($capturedAmount > 0)
            {
                $refundableAmount = $capturedAmount - $this->helper->getAmountRefunded($order, $refundSessionId);
            }
            else
            {
                $refundableAmount = $capturableAmount;
            }

            if ($refundableAmount < $amount)
            {
                $humanReadable = $this->helper->addCurrencySymbol($refundableAmount, $currency);
                throw new LocalizedException(__("The most amount that can be refunded online is %1.", $humanReadable));
            }

            // Refund strategy with $refundableAmount and $capturableAmount:
            // - Fully cancel authorizations; it is not possible to partially refund the order if there are authorizations, because you must first capture them. You can only cancel the whole order.
            // - Refund any regular paid PIs next; there should be only one.
            // - Refund paid amounts from subscription PIs; there can be one or more depending on how many subscriptions were in the cart.

            $cents = 100;
            if ($this->helper->isZeroDecimal($currency))
                $cents = 1;

            $refundedAmount = 0;
            $canceledAmount = 0;
            $requestedAmount = round($amount * $cents);
            $remainingAmount = $requestedAmount;

            // 1. Fully cancel authorizations. It is not possible to partially refund the order if there are authorizations,
            // because you must first capture them. You can only cancel the whole order.
            $paymentIntents = $this->helper->getOrderPaymentIntents($order, $refundSessionId);
            foreach ($paymentIntents as $paymentIntentId => $paymentIntent)
            {
                if ($paymentIntent->status != \StripeIntegration\Payments\Model\PaymentIntent::AUTHORIZED
                    || $paymentIntent->amount > $remainingAmount)
                    continue;

                foreach ($paymentIntent->charges->data as $charge)
                {
                    // If it is an uncaptured authorization
                    if (!$charge->captured)
                    {
                        $humanReadable = $this->helper->addCurrencySymbol($charge->amount / $cents, $currency);

                        // which has not expired yet
                        if (!$charge->refunded)
                        {
                            $this->cache->save($value = "1", $key = "admin_refunded_" . $charge->id, ["stripe_payments"], $lifetime = 60 * 60);
                            $msg = __('We refunded online/released the uncaptured amount of %1 via Stripe. Charge ID: %2', $humanReadable, $charge->id);
                            $this->helper->addOrderComment($msg, $order);
                            // We intentionally do not cancel the $charge in this block, there is a $paymentIntent->cancel() further down
                        }
                        // which has expired
                        else
                        {
                            $msg = __('We refunded offline the expired authorization of %1. Charge ID: %2', $humanReadable, $charge->id);
                            $this->helper->addOrderComment($msg, $order);
                        }

                        $remainingAmount -= $charge->amount;
                        $canceledAmount += $charge->amount;
                    }
                }

                // Fully cancel the payment intent
                $paymentIntent->cancel();
            }

            if (!$this->checkIfWeCanRefundMore($refundedAmount, $canceledAmount, $remainingAmount, $requestedAmount, $order, $currency))
            {
                $this->setRefundedAmount($refundedAmount, $requestedAmount, $currency, $order);
                return $this;
            }

            // 2. Refund any regular payments next; there should be only one.
            foreach ($paymentIntents as $paymentIntentId => $paymentIntent)
            {
                foreach ($paymentIntent->charges->data as $charge)
                {
                    if ($charge->captured && !$charge->invoice)
                    {
                        $amountToRefund = min($remainingAmount, $charge->amount_captured - $charge->amount_refunded);
                        if ($amountToRefund <= 0)
                            continue;

                        $this->cache->save($value = "1", $key = "admin_refunded_" . $charge->id, ["stripe_payments"], $lifetime = 60 * 60);
                        $refund = $stripe->refunds->create(['charge' => $charge->id, 'amount' => $amountToRefund]);

                        $humanReadable = $this->helper->addCurrencySymbol($amountToRefund / $cents, $currency);
                        $msg = __('We refunded online %1 via Stripe. Charge ID: %2', $humanReadable, $charge->id);
                        $this->helper->addOrderComment($msg, $order);

                        $remainingAmount -= $amountToRefund;
                        $refundedAmount += $amountToRefund;
                    }

                    if (!$this->checkIfWeCanRefundMore($refundedAmount, $canceledAmount, $remainingAmount, $requestedAmount, $order, $currency))
                    {
                        $this->setRefundedAmount($refundedAmount, $requestedAmount, $currency, $order);
                        return $this;
                    }
                }
            }

            if (!$this->checkIfWeCanRefundMore($refundedAmount, $canceledAmount, $remainingAmount, $requestedAmount, $order, $currency))
            {
                $this->setRefundedAmount($refundedAmount, $requestedAmount, $currency, $order);
                return $this;
            }

            // 3. Refund amounts from subscription payments; there can be one or more depending on how many subscriptions were in the cart.
            foreach ($paymentIntents as $paymentIntentId => $paymentIntent)
            {
                foreach ($paymentIntent->charges->data as $charge)
                {
                    if ($charge->captured && $charge->invoice)
                    {
                        $amountToRefund = min($remainingAmount, $charge->amount_captured - $charge->amount_refunded);
                        if ($amountToRefund <= 0)
                            continue;

                        $this->cache->save($value = "1", $key = "admin_refunded_" . $charge->id, ["stripe_payments"], $lifetime = 60 * 60);
                        $refund = $stripe->refunds->create(['charge' => $charge->id, 'amount' => $amountToRefund]);

                        $humanReadable = $this->helper->addCurrencySymbol($amountToRefund / $cents, $currency);
                        $msg = __('We refunded online %1 via Stripe. Charge ID: %2. Invoice ID: %3', $humanReadable, $charge->id, $charge->invoice);
                        $this->helper->addOrderComment($msg, $order);

                        $remainingAmount -= $amountToRefund;
                        $refundedAmount += $amountToRefund;
                    }

                    if (!$this->checkIfWeCanRefundMore($refundedAmount, $canceledAmount, $remainingAmount, $requestedAmount, $order, $currency))
                    {
                        $this->setRefundedAmount($refundedAmount, $requestedAmount, $currency, $order);
                        return $this;
                    }
                }
            }

            // We are calling checkIfWeCanRefundMore one last time in case an order comment/warning needs to be added
            $this->checkIfWeCanRefundMore($refundedAmount, $canceledAmount, $remainingAmount, $requestedAmount, $order, $currency);
            $this->setRefundedAmount($refundedAmount, $requestedAmount, $currency, $order);
        }
        catch (\Exception $e)
        {
            $this->helper->dieWithError(__('Could not refund payment: %1', $e->getMessage()), $e);
        }

        return $this;
    }

    public function cancelInvoice($invoice)
    {
        return $this;
    }

    public function refund(InfoInterface $payment, $amount)
    {
        $this->cancel($payment, $amount);

        return $this;
    }

    public function void(InfoInterface $payment)
    {
        $this->cancel($payment);

        return $this;
    }

    public function acceptPayment(InfoInterface $payment)
    {
        return parent::acceptPayment($payment);
    }

    public function denyPayment(InfoInterface $payment)
    {
        return parent::denyPayment($payment);
    }

    public function canCapture()
    {
        return parent::canCapture();
    }

    public function isApplePay()
    {
        $info = $this->getInfoInstance();
        if ($info)
            return $info->getAdditionalInformation("is_prapi");

        return false;
    }

    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if ($this->helper->isRecurringOrder($this))
            return true;

        if (!$this->config->isEnabled())
            return false;

        if ($this->checkoutMethod->isAvailable($quote) && !$this->isApplePay())
            return false;

        return parent::isAvailable($quote);
    }

    // Fixes https://github.com/magento/magento2/issues/5413 in Magento 2.1
    public function setId($code) { }
    public function getId() { return $this->_code; }
}
