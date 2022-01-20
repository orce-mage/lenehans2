<?php

namespace StripeIntegration\Payments\Model;

use StripeIntegration\Payments\Helper\Logger;
use StripeIntegration\Payments\Exception;

class Subscription extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \StripeIntegration\Payments\Model\Config $config,
        \StripeIntegration\Payments\Helper\Generic $helper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_config = $config;
        $this->_helper = $helper;
        $this->_customerSession = $customerSession;
        $this->_registry = $registry;
        $this->_appState = $context->getAppState();
        $this->_eventManager = $context->getEventDispatcher();
        $this->_cacheManager = $context->getCacheManager();
        $this->_resource = $resource;
        $this->_resourceCollection = $resourceCollection;
        $this->_logger = $context->getLogger();
        $this->_actionValidator = $context->getActionValidator();

        if (method_exists($this->_resource, 'getIdFieldName')
            || $this->_resource instanceof \Magento\Framework\DataObject
        ) {
            $this->_idFieldName = $this->_getResource()->getIdFieldName();
        }

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init('StripeIntegration\Payments\Model\ResourceModel\Subscription');
    }

    public function getSubscriptionCurrency($subscription, $order)
    {
        if (!empty($subscription->plan->currency))
        {
            // Subscription was created with Stripe Elements and InvoiceItems
            $currency = $subscription->plan->currency;
        }
        else if (!empty($subscription->items->data))
        {
            // Subscription was created with Stripe Checkout and SubscriptionItems
            foreach ($subscription->items->data as $sub)
            {
                if (!empty($sub->plan->currency))
                {
                    $currency = $sub->plan->currency;
                }
            }
        }

        if (empty($currency))
            $currency = $order->getOrderCurrencyCode();

        return $currency;
    }

    public function initFrom($subscription, $order, $product)
    {
        $this->addData([
            "created_at" => $subscription->created,
            "store_id" => $order->getStore()->getId(),
            "livemode" => $subscription->livemode,
            "subscription_id" => $subscription->id,
            "order_increment_id" => $order->getIncrementId(),
            "product_id" => $product->getId(),
            "magento_customer_id" => $order->getCustomerId(),
            "stripe_customer_id" => $subscription->customer,
            "payment_method_id" => $subscription->default_payment_method,
            "name" => $product->getName(),
            "quantity" => $subscription->quantity,
            "currency" => $subscription->plan->currency,
            "grand_total" => $order->getGrandTotal()
        ]);

        return $this;
    }

    public function cancel($subscriptionId)
    {
        $this->_config->getStripeClient()->subscriptions->cancel($subscriptionId, []);

        $this->load($subscriptionId, "subscription_id");
        if ($this->getId())
            $this->delete();
    }
}
