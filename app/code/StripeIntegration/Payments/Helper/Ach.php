<?php

namespace StripeIntegration\Payments\Helper;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\CouldNotSaveException;
use StripeIntegration\Payments\Model;
use StripeIntegration\Payments\Model\PaymentMethod;
use StripeIntegration\Payments\Model\Config;
use Magento\Framework\Validator\Exception;
use StripeIntegration\Payments\Helper\Logger;

class Ach
{
    public function __construct(
        \StripeIntegration\Payments\Model\Config $config,
        \StripeIntegration\Payments\Helper\Generic $helper,
        \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection
    ) {
        $this->helper = $helper;
        $this->config = $config;
        $this->stripeCustomer = $helper->getCustomerModel();
        $this->orderCollection = $orderCollection;
    }

    public function verify($customerId, $bankAccountId, $amount1, $amount2)
    {
        try
        {
            $customer = $this->stripeCustomer->retrieveByStripeID($customerId);
            if (empty($customer))
                throw new \Exception("Sorry, we could not find your customer account with Stripe", 200);

            $account = $customer->sources->retrieve($bankAccountId);
            if (!isset($account->id))
                throw new \Exception("Sorry, we could not find your bank account with Stripe", 200);

            if ($account->status == "verified")
                return;

            $currency = $this->helper->getCurrentCurrencyCode();
            $amount1 = $this->helper->convertMagentoAmountToStripeAmount($amount1, $currency);
            $amount2 = $this->helper->convertMagentoAmountToStripeAmount($amount2, $currency);

            $account->verify(array('amounts' => array($amount1, $amount2)));
        }
        catch (\Exception $e)
        {
            if ($e->getCode() != 200)
                $this->helper->dieWithError($e->getMessage(), $e);
            else
                $this->helper->dieWithError($e->getMessage());
        }
    }

    public function isACHBankAccountVerification($data)
    {
        if (empty($data['object']['status']) || empty($data['previous_attributes']['status']))
            return false;

        if ($data['object']['object'] != "bank_account")
            return false;

        return ($data['previous_attributes']['status'] == "new" && $data['object']['status'] == "verified");
    }

    public function findOrdersFor($bankAccountId, $customerId)
    {
        $collection = $this->orderCollection
            ->join(
                array('payment' => 'sales_order_payment'),
                'main_table.entity_id=payment.parent_id',
                array('payment_method' => 'payment.method')
            );

        $collection->addFieldToFilter('payment.method', array(array('eq' => 'stripe_payments_ach')))
            ->addFieldToFilter('payment.additional_information', array(array('like' => "%$bankAccountId%")))
            ->addFieldToFilter('payment.additional_information', array(array('like' => "%$customerId%")))
            ->addFieldToFilter('state', array(array('eq' => 'payment_review')));;

        return $collection;
    }

    public function charge($order)
    {
        $payment = $order->getPayment();

        $params = $this->config->getStripeParamsFrom($order);

        $params["source"] = $payment->getAdditionalInformation('bank_account');
        $params["customer"] = $payment->getAdditionalInformation('customer_stripe_id');

        return \Stripe\Charge::create($params);
    }

}
