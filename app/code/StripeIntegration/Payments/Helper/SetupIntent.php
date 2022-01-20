<?php

namespace StripeIntegration\Payments\Helper;

use StripeIntegration\Payments\Helper\Logger;
use Magento\Framework\Exception\LocalizedException;

class SetupIntent
{
    public function __construct(
        \StripeIntegration\Payments\Helper\Generic $helper,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\Order\Invoice $invoice,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Customer\Model\Address $customerAddress,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->helper = $helper;
        $this->stripeCustomer = $helper->getCustomerModel();
        $this->order = $order;
        $this->invoice = $invoice;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->quoteFactory = $quoteFactory;
        $this->cart = $cart;
        $this->customerAddress = $customerAddress;
        $this->transactionFactory = $transactionFactory;
        $this->cache = $cache;
        $this->urlBuilder = $urlBuilder;
    }

    public function shouldUseSetupIntents()
    {
        if ($this->helper->isAdmin())
            return false;

        if ($this->helper->hasOnlyTrialSubscriptions())
            return true;

        return false;
    }

    public function destroy()
    {
        $quote = $this->helper->getQuote();
        if ($quote && $quote->getId())
        {
            $key = 'setup_intent_' . $quote->getId();
            $this->cache->remove($key);
        }
    }

    public function create($customerData = null)
    {
        if (!$this->shouldUseSetupIntents())
            return null;

        if (!$this->helper->isCustomerLoggedIn())
        {
            if (empty($customerData['billingAddress']))
                return null;

            if (empty($customerData["billingAddress"]["firstname"]))
                return null;

            if (empty($customerData["billingAddress"]["lastname"]))
                return null;

            if (!empty($customerData["id"]))
                $id = $customerData["id"];
            else
                $id = 0;

            if (!empty($customerData["guestEmail"]))
                $email = $customerData["guestEmail"];
            else if (!empty($customerData["billingAddress"]["email"]))
                $email = $customerData["billingAddress"]["email"];
            else
                return null;

            // $customer = $this->stripeCustomer->createNewStripeCustomer(
            //     $customerData["billingAddress"]["firstname"],
            //     $customerData["billingAddress"]["lastname"],
            //     $email,
            //     $id
            // );
        }

        $params = [
            "usage" => "on_session"
            // "customer" => $customer->id
        ];

        $quote = $this->helper->getQuote();
        if ($quote && $quote->getId())
        {
            $key = 'setup_intent_' . $quote->getId();
            $setupIntentClientSecret = $this->cache->load($key);

            if ($setupIntentClientSecret)
                return $setupIntentClientSecret;

            // Create a fresh SetupIntent
            $setupIntent = \Stripe\SetupIntent::create($params);
            $tags = ['stripe_payments_setup_intents'];
            $lifetime = 12 * 60 * 60; // 12 hours
            $this->cache->save($setupIntent->client_secret, $key, $tags, $lifetime);
            return $setupIntent->client_secret;
        }
        else
        {
            // We don't have any items in the cart yet
            return null;
        }
    }

    public function setAuthorizationData()
    {
        $customerId = $this->customerSession->getCustomerId();
        $tags = ['stripe_payments_setup_intents'];
        $lifetime = 5 * 60; // 5 mins
        $this->cache->save($data = $this->urlBuilder->getUrl('*/*/*'), $key = $customerId . "_success_url", $tags, $lifetime);
        $this->cache->save($data = $this->urlBuilder->getUrl('*/*/billing'), $key = $customerId . "_fail_url", $tags, $lifetime);
        $this->cache->save($data = $this->urlBuilder->getUrl('stripe/authorization/multishipping'), $key = $customerId . "_authorization_url", $tags, $lifetime);
    }

    public function clearAuthorizationData()
    {
        $customerId = $this->customerSession->getCustomerId();
        $this->cache->remove($customerId . "_authorization_url");
        $this->cache->remove($customerId . "_success_url");
        $this->cache->remove($customerId . "_fail_url");
    }
}
