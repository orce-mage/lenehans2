<?php

namespace StripeIntegration\Payments\Helper;

use StripeIntegration\Payments\Helper\Logger;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use StripeIntegration\Payments\Exception\SCANeededException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\CouldNotSaveException;

class Subscriptions
{
    public $couponCodes = [];
    public $subscriptions = [];
    public $invoices = [];
    public $paymentIntents = [];
    public $trialingSubscriptionsAmounts = null;
    public $shippingTaxPercent = null;

    public function __construct(
        \StripeIntegration\Payments\Helper\Rollback $rollback,
        \StripeIntegration\Payments\Helper\Generic $paymentsHelper,
        \StripeIntegration\Payments\Model\Config $config,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Tax\Model\Sales\Order\TaxManagement $taxManagement,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \StripeIntegration\Payments\Model\SubscriptionFactory $subscriptionFactory,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \StripeIntegration\Payments\Model\CouponFactory $stripeCouponFactory,
        \StripeIntegration\Payments\Helper\TaxHelper $taxHelper
    ) {
        $this->rollback = $rollback;
        $this->paymentsHelper = $paymentsHelper;
        $this->config = $config;
        $this->priceCurrency = $priceCurrency;
        $this->eventManager = $eventManager;
        $this->customer = $paymentsHelper->getCustomerModel();
        $this->cache = $cache;
        $this->taxManagement = $taxManagement;
        $this->invoiceService = $invoiceService;
        $this->quoteRepository = $quoteRepository;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->couponFactory = $couponFactory;
        $this->stripeCouponFactory = $stripeCouponFactory;
        $this->taxHelper = $taxHelper;
    }

    public function createSubscriptions($order, $isDryRun = false, $trialEnd = null)
    {
        $this->_subscriptionsTotal = 0;
        $this->_isDryRun = $isDryRun;
        $this->_piSecrets = [];
        $this->_createdSubscriptions = [];
        $this->_error = null;

        // Get all the products on the order
        $items = $order->getAllItems();
        foreach ($items as $item)
        {
            $product = $this->paymentsHelper->loadProductById($item->getProductId());
            if ($product->getStripeSubEnabled())
            {
                try
                {
                    $this->createSubscriptionForProduct($product, $order, $item, $isDryRun, $trialEnd);
                }
                catch (\Stripe\Exception\CardException $e)
                {
                    $this->rollback->run($e);
                    throw new CouldNotSaveException(__($e->getMessage()));
                }
                catch (CouldNotSaveException $e)
                {
                    $this->rollback->run($e);
                    throw $e;
                }
                catch (\Exception $e)
                {
                    $this->rollback->run($e);

                    // We get a \Stripe\Error\InvalidRequest if the customer is purchasing a subscription with a currency
                    // that is different from the currency they used for previous subscription purposes
                    $message = $e->getMessage();
                    if (preg_match('/with currency (\w+)$/', $message, $matches))
                    {
                        $currency = strtoupper($matches[1]);
                        $error = __("Your account has been configured to use a different currency. Please complete the purchase in the currency: %1", $currency);
                        throw new CouldNotSaveException($error);
                    }
                    else
                    {
                        \StripeIntegration\Payments\Helper\Logger::log($e->getMessage());
                        \StripeIntegration\Payments\Helper\Logger::log($e->getTraceAsString());
                        $error = __("Sorry, we could not create the subscription for %1. Please contact us for more help.", $product->getName());
                        throw new CouldNotSaveException($error);
                    }
                }
            }
        }

        return [
            "subscriptionsTotal" => $this->_subscriptionsTotal,
            "piSecrets" => $this->_piSecrets,
            "createdSubscriptions" => $this->_createdSubscriptions,
            "stripeCustomerId" => $this->customer->getStripeId(),
            "error" => $this->_error
        ];
    }

    /**
     * Description
     * @param \Magento\Sales\Model\Order $order
     * @return array<\Magento\Catalog\Model\Product,\Magento\Sales\Model\Quote\Item,array profile>
     */
    public function getSubscriptionsFromQuote($quote)
    {
        if (!$this->paymentsHelper->isSubscriptionsEnabled())
            return [];

        $items = $quote->getAllItems();
        $subscriptions = [];

        foreach ($items as $item)
        {
            $product = $this->paymentsHelper->getSubscriptionProductFromQuoteItem($item);
            if (!$product)
                continue;

            $subscriptions[] = [
                'product' => $product,
                'quote_item' => $item,
                'profile' => $this->getSubscriptionDetails($product, $quote, $item, false, null, $this->config->useStoreCurrency())
            ];
        }

        return $subscriptions;
    }

    /**
     * Description
     * @param \Magento\Sales\Model\Order $order
     * @return array<\Magento\Catalog\Model\Product,\Magento\Sales\Model\Order\Item,array profile>
     */
    public function getSubscriptionsFromOrder($order)
    {
        if (!$this->paymentsHelper->isSubscriptionsEnabled())
            return [];

        $items = $order->getAllItems();
        $subscriptions = [];

        foreach ($items as $item)
        {
            $product = $this->paymentsHelper->getSubscriptionProductFromOrderItem($item);
            if (!$product)
                continue;

            $subscriptions[$item->getQuoteItemId()] = [
                'product' => $product,
                'order_item' => $item,
                'profile' => $this->getSubscriptionDetails($product, $order, $item, false, null, $this->config->useStoreCurrency())
            ];
        }

        return $subscriptions;
    }

    public function getQuote()
    {
        $quote = $this->paymentsHelper->getQuote();
        $createdAt = $quote->getCreatedAt();
        if (empty($createdAt)) // case of admin orders
        {
            $quoteId = $quote->getQuoteId();
            $quote = $this->paymentsHelper->loadQuoteById($quoteId);
        }
        return $quote;
    }

    public function getShippingTax($paramName = "percent", $quote = null)
    {
        if (!empty($this->shippingTaxPercent))
            return $this->shippingTaxPercent;

        if (empty($quote))
            $quote = $this->getQuote();

        if ($quote->getIsVirtual())
            return 0;

        $address = $quote->getShippingAddress();
        $address->collectShippingRates();

        $taxes = $address->getItemsAppliedTaxes();

        if (!is_array($taxes) || !is_array($taxes['shipping']))
            return 0;

        foreach ($taxes['shipping'] as $tax)
        {
            if ($tax['item_type'] == "shipping")
                return $tax[$paramName];
        }

        return 0;
    }

    public function chargeShippingRecurringly()
    {
        $setting = $this->config->getConfigData("shipping", "subscriptions");
        return ($setting == "add_to_subscription");
    }

    public function chargeShippingOnlyOnce()
    {
        return !$this->chargeShippingRecurringly();
    }

    public function isOrder($order)
    {
        if (!empty($order->getOrderCurrencyCode()))
            return true;

        return false;
    }

    public function getSubscriptionDetails($product, $order, $item, $isDryRun, $trialEnd, $useStoreCurrency)
    {
        // Get billing interval and billing period
        $interval = $product->getStripeSubInterval();
        $intervalCount = $product->getStripeSubIntervalCount();

        if (!$interval)
            throw new \Exception("An interval period has not been specified for the subscription");

        if (!$intervalCount)
            $intervalCount = 1;

        $name = $item->getName();
        $qty = max(/* quote */ $item->getQty(), /* order */ $item->getQtyOrdered());
        $originalItem = $item;
        $item = $this->paymentsHelper->getSubscriptionQuoteItemWithTotalsFrom($item, $order);

        // Get the subscription currency and amount
        $initialFee = $product->getStripeSubInitialFee();

        if (!is_numeric($initialFee))
            $initialFee = 0;

        if ($useStoreCurrency)
        {
            if ($this->config->priceIncludesTax())
                $amount = $item->getPriceInclTax();
            else
                $amount = $item->getPrice();

            $discount = $item->getDiscountAmount();
            $tax = $item->getTaxAmount();

            if ($this->isOrder($order))
            {
                $currency = $order->getOrderCurrencyCode();
                $rate = $order->getBaseToOrderRate();
            }
            else
            {
                $currency = $order->getQuoteCurrencyCode();
                $rate = $order->getBaseToQuoteRate();
            }

            // This seems to be a Magento multi-currency bug, tested in v2.3.2
            if (is_numeric($rate) && $rate > 0 && $rate != 1 && $item->getPrice() == $item->getBasePrice())
                $amount = round($amount * $rate, 2); // We fix it by doing the calculation ourselves

            if (is_numeric($rate) && $rate > 0)
                $initialFee = round($initialFee * $rate, 2);
        }
        else
        {
            if ($this->config->priceIncludesTax())
                $amount = $item->getBasePriceInclTax();
            else
                $amount = $item->getBasePrice();

            $discount = $item->getBaseDiscountAmount();
            $tax = $item->getBaseTaxAmount();
            $currency = $order->getBaseCurrencyCode();
        }

        if ($this->isOrder($order))
        {
            $quote = $this->paymentsHelper->getQuoteFromOrder($order);
            $quoteItem = null;
            foreach ($quote->getAllItems() as $qItem)
            {
                if ($qItem->getSku() == $item->getSku())
                {
                    $quoteItem = $qItem;

                    if ($quoteItem->getParentItemId() && $originalItem->getParentItem()->getProductType() == "configurable")
                    {
                        $qty = $item->getQtyOrdered() * $quoteItem->getQty();
                        $quoteItem->setQtyCalculated($qty);
                    }
                }
            }

            if ($useStoreCurrency)
            {
                if ($item->getShippingAmount())
                    $shipping = $item->getShippingAmount();
                else if ($item->getBaseShippingAmount())
                    $shipping = $this->paymentsHelper->convertBaseAmountToStoreAmount($item->getBaseShippingAmount());
                else
                {
                    $baseShipping = $this->taxHelper->getBaseShippingAmountForQuoteItem($quoteItem, $quote);
                    $shipping = $this->paymentsHelper->convertBaseAmountToStoreAmount($baseShipping);
                }

                $orderShippingAmount = $order->getShippingAmount();
                $orderShippingTaxAmount = $order->getShippingTaxAmount();
            }
            else
            {
                if ($item->getBaseShippingAmount())
                    $shipping = $item->getBaseShippingAmount();
                else
                    $shipping = $this->taxHelper->getBaseShippingAmountForQuoteItem($quoteItem, $quote);

                $orderShippingAmount = $order->getBaseShippingAmount();
                $orderShippingTaxAmount = $order->getBaseShippingTaxAmount();
            }

            $shippingTaxPercent = $this->getShippingTax("percent");
            if ($orderShippingAmount == $shipping)
            {
                $shippingTaxAmount = $orderShippingTaxAmount;
            }
            else
            {
                $shippingTaxAmount = 0;

                if ($shippingTaxPercent && is_numeric($shippingTaxPercent) && $shippingTaxPercent > 0)
                {
                    if ($this->config->shippingIncludesTax())
                        $shippingTaxAmount = $this->taxHelper->taxInclusiveTaxCalculator($shipping, $shippingTaxPercent);
                    else
                        $shippingTaxAmount = $this->taxHelper->taxExclusiveTaxCalculator($shipping, $shippingTaxPercent);
                }
            }
        }
        else
        {
            $quote = $order;
            $quoteItem = $item;

            $baseShipping = $this->taxHelper->getBaseShippingAmountForQuoteItem($quoteItem, $quote);
            $shippingTaxRate = $this->taxHelper->getShippingTaxRateFromQuote($quote);

            if ($useStoreCurrency)
                $shipping = $this->paymentsHelper->convertBaseAmountToStoreAmount($baseShipping);
            else
                $shipping = $baseShipping;

            $shippingTaxAmount = 0;
            $shippingTaxPercent = 0;

            if ($shipping > 0 && $shippingTaxRate)
            {
                $shippingTaxPercent = $shippingTaxRate["percent"];
                if ($useStoreCurrency)
                    $shippingTaxAmount = $shippingTaxRate["amount"];
                else
                    $shippingTaxAmount = $shippingTaxRate["base_amount"];
            }
        }

        if (!is_numeric($amount))
            $amount = 0;

        if ($order->getPayment()->getAdditionalInformation("remove_initial_fee"))
            $initialFee = 0;

        if ($this->config->priceIncludesTax())
            $initialFeeTaxAmount = $this->taxHelper->taxInclusiveTaxCalculator($initialFee * $qty, $item->getTaxPercent());
        else
            $initialFeeTaxAmount = $this->taxHelper->taxExclusiveTaxCalculator($initialFee * $qty, $item->getTaxPercent());

        $params = [
            'name' => $name,
            'qty' => $qty,
            'interval' => $interval,
            'interval_count' => $intervalCount,
            'amount_magento' => $amount,
            'amount_stripe' => $this->paymentsHelper->convertMagentoAmountToStripeAmount($amount, $currency),
            'initial_fee_stripe' => $this->paymentsHelper->convertMagentoAmountToStripeAmount($initialFee, $currency),
            'initial_fee_magento' => $initialFee,
            'discount_amount_magento' => $discount,
            'discount_amount_stripe' => $this->paymentsHelper->convertMagentoAmountToStripeAmount($discount, $currency),
            'shipping_magento' => round($shipping, 2),
            'shipping_stripe' => $this->paymentsHelper->convertMagentoAmountToStripeAmount($shipping, $currency),
            'currency' => strtolower($currency),
            'tax_percent' => $item->getTaxPercent(),
            'tax_percent_shipping' => $shippingTaxPercent,
            'tax_amount_item' => $tax, // already takes $qty into account
            'tax_amount_shipping' => $shippingTaxAmount,
            'tax_amount_initial_fee' => $initialFeeTaxAmount,
            'trial_end' => $trialEnd,
            'trial_days' => 0,
            'coupon_code' => $this->getCouponId($discount, $currency, $order->getCouponCode(), $isDryRun, $item)
        ];

        if (!$trialEnd)
            $params['trial_days'] = $this->getTrialDays($product);

        return $params;
    }

    public function getTrialDays($product)
    {
        $trialDays = $product->getStripeSubTrial();
        if (!empty($trialDays) && is_numeric($trialDays) && $trialDays > 0)
            return $trialDays;

        return 0;
    }

    // The returned shipping amount will take into account this->config->useStoreCurrency()
    public function calculateShippingCostFor($order, $item)
    {
        if ($item->getProductType() == "virtual" || $order->getIsVirtual())
            return 0;

        if ($order->getIncrementId())
        {
            $orderItem = $item;
            $quote = $this->quoteRepository->get($order->getQuoteId());
            $item = $quote->getItemById($item->getQuoteItemId());
            $item->setQty($orderItem->getQtyOrdered());
            $shippingAddress = $quote->getShippingAddress();
        }
        else // quote
            $shippingAddress = $order->getShippingAddress();

        $shippingAddress->requestShippingRates($item);

        if ($this->config->useStoreCurrency())
            return $this->paymentsHelper->convertBaseAmountToStoreAmount($item->getBaseShippingAmount());
        else
            return $item->getBaseShippingAmount();

        return 0;
    }

    public function getCouponExpirationParams($couponCode)
    {
        $defaults = ['duration' => 'forever'];

        if (empty($couponCode))
            return $defaults;

        $ruleId = $this->couponFactory->create()->loadByCode($couponCode)->getRuleId();
        if (empty($ruleId))
            return $defaults;

        $coupon = $this->stripeCouponFactory->create()->load($ruleId, 'rule_id');
        $duration = $coupon->duration();
        $months = $coupon->months();

        if ($months && $months > 0)
        {
            return [
                'duration' => $duration,
                'duration_in_months' => $months
            ];
        }

        return ['duration' => $duration];
    }

    public function getCouponId($amount, $currency, $couponCode, $isDryRun, $item)
    {
        if ($isDryRun)
            return null;

        if ($amount <= 0)
            return null;

        if (is_numeric($item->getDiscountPercent()) && $item->getDiscountPercent() > 0)
        {
            $discountType = "percent_off";
            $stripeAmount = $item->getDiscountPercent();
            $couponId = ((string)$stripeAmount) . "percent";
            $name = $stripeAmount . "% Discount";
        }
        else
        {
            $discountType = "amount_off";
            $stripeAmount = $this->paymentsHelper->convertMagentoAmountToStripeAmount($amount, $currency);
            $couponId = ((string)$stripeAmount) . strtoupper($currency);
            $name = $this->paymentsHelper->addCurrencySymbol($amount, $currency) . " Discount";
        }

        $expirationParams = $this->getCouponExpirationParams($couponCode);
        if ($expirationParams['duration'] != 'forever')
            $couponId .= "-" . $expirationParams['duration'];

        if (isset($expirationParams['duration_in_months']))
            $couponId .= "-" . $expirationParams['duration_in_months'];

        try
        {
            $coupon = \Stripe\Coupon::retrieve($couponId);
        }
        catch (\Exception $e)
        {
            $coupon = null;
        }

        if (!$coupon)
        {
            try
            {
                $params = [
                    'id' => $couponId,
                    $discountType => $stripeAmount,
                    'currency' => $currency,
                    'name' => $name
                ];

                $params = array_merge($params, $expirationParams);

                $coupon = \Stripe\Coupon::create($params);
            }
            catch (\Exception $e)
            {
                $this->paymentsHelper->dieWithError("Sorry, the discount coupon could not be applied on a subscription product! Please contact us for help.", $e);
            }
        }

        return $coupon->id;;
    }

    public function getSubscriptionTotalFromProfile($profile)
    {
        $subscriptionTotal =
            ($profile['qty'] * $profile['initial_fee_magento']) +
            ($profile['qty'] * $profile['amount_magento']) +
            $profile['shipping_magento'] -
            $profile['discount_amount_magento'];

        if (!$this->config->shippingIncludesTax())
            $subscriptionTotal += $profile['tax_amount_shipping']; // Includes qty calculation

        if (!$this->config->priceIncludesTax())
        {
            $subscriptionTotal += $profile['tax_amount_item']; // Includes qty calculation
            $subscriptionTotal += $profile['tax_amount_initial_fee']; // Includes qty calculation
        }

        return round($subscriptionTotal, 2);
    }

    public function createSubscriptionForProduct($product, $order, $item, $isDryRun, $trialEnd = null)
    {
        $profile = $this->getSubscriptionDetails($product, $order, $item, $isDryRun, $trialEnd, $this->config->useStoreCurrency());

        $this->_subscriptionsTotal += $this->getSubscriptionTotalFromProfile($profile);

        if ($this->_isDryRun)
            return;

        $metadata = $this->collectMetadata($profile, $product, $order);

        $orderId = $order->getIncrementId();
        $itemId = $item->getQuoteItemId();
        $key = "{$order->getQuoteId()}_subscription_id_item_" . $itemId;
        $subscriptionId = $this->cache->load($key);

        try
        {
            // if it returns false, it means that the subscription was not created yet
            if ($this->confirm($subscriptionId, $profile, $metadata, $order))
                return;
        }
        catch (SCANeededException $e)
        {
            if ($this->paymentsHelper->isAdmin())
            {
                $this->paymentsHelper->addError("This card cannot be used because it requires a 3D Secure authentication by the customer. Your Stripe account needs to be MOTO enabled to use 3D Secure cards from the admin area.");
                throw new LocalizedException(__("This card cannot be used because it requires a 3D Secure authentication by the customer. Your Stripe account needs to be MOTO enabled to use 3D Secure cards from the admin area."));
            }
            else if (!empty($subscriptionId))
            {
                $this->cache->save($subscriptionId, $key, $tags = ["unconfirmed_subscriptions"], $lifetime = 60 * 60);
            }

            return;
        }
        catch (CouldNotSaveException $e)
        {
            // A payment error has occurred, i.e. Insufficient funds
            $this->cache->remove($key);
            throw $e;
        }

        $this->createProduct($profile, $product);
        $planId = $this->generatePlanId($profile, $product);
        $plan = $this->createPlan($profile, $planId, $product);
        $customer = $this->createCustomer($profile, $order);
        $paymentMethod = $this->attachCustomerToPaymentMethod($customer, $order->getPayment()->getAdditionalInformation('token'));
        $order->getPayment()->setAdditionalInformation('token', $paymentMethod->id);

        $this->collectInitialFee($customer, $profile, $orderId);
        $this->collectShipping($product, $customer, $profile);

        $subscription = $this->subscribeCustomer(
            $product,
            $customer,
            $plan,
            $paymentMethod->id,
            $profile,
            $metadata,
            $order->getPayment()->getAdditionalInformation("off_session")
        );

        $this->updateSubscriptionEntry($subscription, $order, $product);

        $this->_createdSubscriptions[$key] = $subscription->id;

        try
        {
            $this->confirm($subscription->id, $profile, $metadata, $order);
        }
        catch (SCANeededException $e)
        {
            if ($this->paymentsHelper->isAdmin())
            {
                $this->paymentsHelper->addError("This card cannot be used because it requires a 3D Secure authentication by the customer.");
                throw new LocalizedException(__("This card cannot be used because it requires a 3D Secure authentication by the customer."));
            }
            else if (!empty($subscription->id))
            {
                $this->cache->save($subscription->id, $key, $tags = ["unconfirmed_subscriptions"], $lifetime = 60 * 60);
            }

            return;
        }
        catch (CouldNotSaveException $e)
        {
            // A payment error has occurred, i.e. Insufficient funds
            $this->cache->remove($key);
            throw $e;
        }
    }

    public function updateSubscriptionEntry($subscription, $order, $product)
    {
        if ($order->getPayment()->getMethod() != "stripe_payments")
            return null;

        $entry = $this->subscriptionFactory->create();
        $entry->load($subscription->id, 'subscription_id');
        $entry->initFrom($subscription, $order, $product);
        $entry->save();
        return $entry;
    }

    public function findSubscriptionItem($sub)
    {
        if (empty($sub->items->data))
            return null;

        foreach ($sub->items->data as $item)
        {
            if (!empty($item->price->product->metadata->{"Type"}) && $item->price->product->metadata->{"Type"} == "Product" && $item->price->type == "recurring")
                return $item;
        }

        return null;
    }

    public function isStripeCheckoutSubscription($sub)
    {
        if (empty($sub->metadata->{"Order #"}))
            return false;

        return $this->paymentsHelper->isStripeCheckoutMethod($order->getPayment()->getMethod());
    }

    public function formatSubscriptionName($sub)
    {
        if (empty($sub))
            return "Unknown subscription (err: 1)";

        // Subscription Items
        if ($this->isStripeCheckoutSubscription($sub))
        {
            $item =  $this->findSubscriptionItem($sub);

            if (!$item)
                return "Unknown subscription (err: 2)";

            if (!empty($item->price->product->name))
                $name = $item->price->product->name;
            else
                return "Unknown subscription (err: 3)";

            $currency = $item->price->currency;
            $amount = $item->price->unit_amount;
            $quantity = $item->quantity;
        }
        // Invoice Items
        else
        {
            if (!empty($sub->plan->name))
                $name = $sub->plan->name;

            if (empty($name) && isset($sub->plan->product) && is_numeric($sub->plan->product))
            {
                $product = $this->paymentsHelper->loadProductById($sub->plan->product);
                if ($product && $product->getName())
                    $name = $product->getName();
            }
            else
                return "Unknown subscription (err: 4)";

            $currency = $sub->plan->currency;
            $amount = $sub->plan->amount;
            $quantity = $sub->quantity;
        }

        $precision = PriceCurrencyInterface::DEFAULT_PRECISION;
        $cents = 100;
        $qty = '';

        if ($this->paymentsHelper->isZeroDecimal($currency))
        {
            $cents = 1;
            $precision = 0;
        }

        $amount = $amount / $cents;

        if ($quantity > 1)
        {
            $qty = " x " . $quantity;
        }

        $this->priceCurrency->getCurrency()->setCurrencyCode(strtoupper($currency));
        $cost = $this->priceCurrency->format($amount, false, $precision);

        return "$name ($cost$qty)";
    }

    public function generatePlanId($profile, $product)
    {
        // Validate the billing period
        switch ($profile['interval'])
        {
            case 'day':
            case 'week':
            case 'month':
            case 'year':
                break;
            default:
                $this->paymentsHelper->dieWithError("Could not complete subscription because of an invalid billing period unit!");
                break;
        }

        $amount = $profile['amount_stripe'] . $profile['currency'];
        $frequency = $profile['interval_count'] . strtoupper($profile['interval']) . ($profile['interval_count'] > 1 ? 'S' : '');

        $pieces = [
            'amount' => $amount,
            'frequency' => $frequency,
            'product' => $product->getId()
        ];

        return implode('-', $pieces);
    }

    public function createPlan($profile, $planId, $product)
    {
        try
        {
            $plan = \Stripe\Plan::retrieve($planId);
        }
        catch (\Exception $e)
        {
            $plan = \Stripe\Plan::create([
                "amount" => $profile['amount_stripe'],
                "interval" => $profile['interval'],
                "interval_count" => $profile['interval_count'],
                "product" => $product->getId(),
                "currency" => $profile['currency'],
                "id" => $planId
            ]);
        }

        return $plan;
    }

    public function createProduct($profile, $product)
    {
        try
        {
            $obj = \Stripe\Product::retrieve($product->getId());
        }
        catch (\Exception $e)
        {
            // Product does not exist yet
            $obj = \Stripe\Product::create([
                "id" => $product->getId(),
                "name" => $product->getName(),
                "type" => "service",
            ]);
        }

        return $obj;
    }

    public function collectMetadata($profile, $product, $order)
    {
        $shipping = $this->paymentsHelper->getAddressFrom($order);

        if ($order->getIncrementId())
        {
            $orderIncrementId = $order->getIncrementId();
            $quote = $order->getQuote();
        }
        else
        {
            $quote = $order;
            $order = null;

            if (!$quote->getReservedOrderId())
                $quote->reserveOrderId()->save(); // Warning: The may cause order ID skipping if the customer abandons the checkout

            $orderIncrementId = $quote->getReservedOrderId();
        }

        // Build the metadata for this subscription - the customer will be able to edit these in the future
        $metadata = [
            "Product ID" => $product->getId(),
            "Customer ID" => $this->customer->getCustomerId(),
            "Order #" => $orderIncrementId,
            "Module" => \StripeIntegration\Payments\Model\Config::$moduleName . " v" . \StripeIntegration\Payments\Model\Config::$moduleVersion
        ];

        if ($shipping)
        {
            $metadata["Shipping First Name"] = $shipping["firstname"];
            $metadata["Shipping Last Name"] = $shipping["lastname"];
            $metadata["Shipping Company"] = $shipping["company"];
            $metadata["Shipping Street"] = $shipping["street"];
            $metadata["Shipping City"] = $shipping["city"];
            $metadata["Shipping Region"] = $shipping["region"];
            $metadata["Shipping Postcode"] = $shipping["postcode"];
            $metadata["Shipping Country"] = $shipping["country_id"];
            $metadata["Shipping Telephone"] = $shipping["telephone"];
        }

        if ($profile['trial_days'] > 0)
            $metadata["Trial"] = $profile['trial_days'] . " days";

        // Event to collect additional metadata, use this in your own local module
        $returnObject = new \Magento\Framework\DataObject();
        $this->eventManager->dispatch('stripe_subscriptions_metadata', array(
            'product' => $product,
            'order' => $order,
            'quote' => $quote,
            'metadata' => $metadata,
            'returnObject' => $returnObject
        ));

        foreach ((array) $returnObject->getMetadata() as $key => $value)
            $metadata[$key] = $value;

        return $metadata;
    }

    public function createCustomer($profile, $order)
    {
        $quote = $order->getQuote();
        $params = [];

        if ($order->getPayment()->getAdditionalInformation("subscription_customer"))
            $customerStripeId = $order->getPayment()->getAdditionalInformation("subscription_customer"); // This is used when migrating subscriptions from the CLI
        else
            $customerStripeId = $this->customer->getStripeId();

        if (!$customerStripeId)
        {
            $customer = $this->customer->createStripeCustomer($order, $params);
        }
        else
        {
            $customer = $this->customer->retrieveByStripeID($customerStripeId);

            if (!$customer)
                $customer = $this->customer->createStripeCustomer($order, $params); // This should overwrite the Stripe customer ID association
        }

        return $customer;
    }

    public function attachCustomerToPaymentMethod($customer, $paymentMethodId)
    {
        try
        {
            $paymentMethod = \Stripe\PaymentMethod::retrieve($paymentMethodId);
            if (!empty($paymentMethod->customer))
            {
                if ($paymentMethod->customer != $customer->id)
                {
                    $e = new \Exception("Error: This card belongs to a different customer.");
                    $this->paymentsHelper->dieWithError($e->getMessage(), $e);
                }
            }
            else
                $paymentMethod->attach([ 'customer' => $customer->id ]);

            return $paymentMethod;
        }
        catch (\Stripe\Exception\CardException $e)
        {
            $this->paymentsHelper->dieWithError($e->getMessage());
        }
    }

    public function collectInitialFee($customer, $profile, $realOrderId)
    {
        $initialFee = $profile['initial_fee_stripe'] * $profile['qty'];
        $currency = $profile['currency'];

        if ($initialFee && $initialFee > 0)
        {
            try
            {
                $taxRate = $this->retrieveTaxRate($profile['tax_percent'], $this->config->priceIncludesTax());

                $params = array(
                    'customer' => $customer->id,
                    'amount' => $initialFee,
                    'currency' => $currency,
                    'description' => "Initial fee",
                    'tax_rates' => [$taxRate],
                    'discountable' => false
                );
                $invoiceItem = \Stripe\InvoiceItem::create($params);
            }
            catch (\Exception $e)
            {
                $this->paymentsHelper->dieWithError($e->getMessage(), $e);
            }
        }
    }

    protected function getTrialEnd($profile)
    {
        if ($profile['trial_days'] > 0)
            return (time() + $profile['trial_days'] * 24 * 60 * 60);

        if (is_numeric($profile['trial_end']) && $profile['trial_end'] > time())
            return $profile['trial_end'];

        return false;
    }

    public function collectShipping($product, $customer, $profile, $subscriptionId = null)
    {
        $currency = $profile['currency'];
        $shippingAmount = $profile['shipping_stripe'];
        $isNonRecurringShippingCost = empty($subscriptionId);
        $isRecurringShippingCost = !$isNonRecurringShippingCost;

        if ($shippingAmount && $shippingAmount > 0)
        {
            if ($isRecurringShippingCost && $this->chargeShippingOnlyOnce())
                return;

            if ($isNonRecurringShippingCost && $this->chargeShippingRecurringly() && $this->getTrialEnd($profile))
                return;

            try
            {
                $params = array(
                    'customer' => $customer->id,
                    'amount' => $shippingAmount,
                    'currency' => $currency,
                    'description' => "Shipping",
                    'discountable' => false
                );

                if ($subscriptionId)
                    $params['subscription'] = $subscriptionId;

                $taxPercent = $profile['tax_percent_shipping'];
                if ($taxPercent && is_numeric($taxPercent))
                    $params['tax_rates'] = [$this->retrieveTaxRate($taxPercent, $this->config->shippingIncludesTax())];
                else
                    $params['tax_rates'] = [$this->retrieveTaxRate(0)];

                $invoiceItem = \Stripe\InvoiceItem::create($params);
            }
            catch (\Exception $e)
            {
                $this->paymentsHelper->dieWithError($e->getMessage(), $e);
            }
        }
    }

    public function subscribeCustomer($product, $customer, $plan, $paymentMethodId, $profile, $metadata, $offSession = false)
    {
        $taxPercent = $profile['tax_percent'];
        $shipping = $profile['shipping_stripe'];
        $couponCode = $profile['coupon_code'];
        $qty = $profile['qty'];

        $params = [
            'customer' => $customer->id,
            'plan' => $plan->id,
            'quantity' => (int)$qty,
            'default_payment_method' => $paymentMethodId,
            'enable_incomplete_payments' => true,
            'metadata' => $metadata,
            'expand' => ['latest_invoice.payment_intent']
        ];

        if (is_numeric($taxPercent) && $taxPercent > 0)
            $params['default_tax_rates'] = [$this->retrieveTaxRate($taxPercent, $this->config->priceIncludesTax())];

        if ($couponCode)
            $params['coupon'] = $couponCode;

        if ($this->getTrialEnd($profile))
            $params['trial_end'] = $this->getTrialEnd($profile);

        if ($this->paymentsHelper->isAdmin() || $offSession)
            $params['off_session'] = true;

        $subscription = \Stripe\Subscription::create($params);
        $this->rollback->addSubscription($subscription->id);

        if ($shipping && $shipping > 0)
        {
            $this->collectShipping($product, $customer, $profile, $subscription->id);
        }

        $this->subscriptions[$subscription->id] = $subscription;
        $this->paymentIntents[$subscription->id] = $subscription->latest_invoice->payment_intent;

        // Trial subscriptions will not have any charges
        if (!empty($subscription->latest_invoice->payment_intent->charges->data))
            foreach ($subscription->latest_invoice->payment_intent->charges->data as $charge)
                $this->rollback->addCharge($charge->id);

        return $subscription;
    }

    public function updatePaymentIntentFrom($paymentIntent, $profile, $metadata)
    {
        if ($profile['qty'] > 1)
            $qty = $profile['qty'] . " x ";
        else
            $qty = "";

        $name = $profile['name'];

        $paymentIntent->description = $qty . $name;
        $paymentIntent->metadata = $metadata;
        $paymentIntent->save();
    }

    public function retrieveSubscription($subscriptionId)
    {
        if (isset($this->subscriptions[$subscriptionId]))
            return $this->subscriptions[$subscriptionId];

        try
        {
            $this->subscriptions[$subscriptionId] = \Stripe\Subscription::retrieve([ 'id' => $subscriptionId, 'expand' => ['latest_invoice.payment_intent'] ]);
            return $this->subscriptions[$subscriptionId];
        }
        catch (\Exception $e)
        {
            // In the case we have an invalid subscription ID, recreate the subscription
            return false;
        }
    }

    public function retrievePaymentIntentFor($subscriptionId, $profile)
    {
        if (isset($this->paymentIntents[$subscriptionId]))
            return $this->paymentIntents[$subscriptionId];

        try
        {
            $subscription = $this->retrieveSubscription($subscriptionId);

            if (empty($subscription->latest_invoice->payment_intent))
                return null;

            $this->paymentIntents[$subscriptionId] = $subscription->latest_invoice->payment_intent;

            return $this->paymentIntents[$subscriptionId];
        }
        catch (\Exception $e)
        {
            Logger::log($e->getMessage());
            return null;
        }
    }

    public function retrieveTaxRate($percent, $inclusive = false)
    {
        $key = (string)$percent;
        if ($inclusive)
            $key .= "-inclusive";

        if (isset($this->taxRates[$key]))
            return $this->taxRates[$key];

        $rates = $this->config->getStripeClient()->taxRates->all(['limit' => 100, 'inclusive' => $inclusive]);

        foreach ($rates as $rate)
        {
            $key2 = (string)$rate->percentage;
            if ($rate->inclusive)
                $key2 .= "-inclusive";

            $this->taxRates[$key2] = $rate;

            if ($key == $key2)
            {
                return $rate;
            }
        }

        $rate = \Stripe\TaxRate::create([
            "display_name" => 'VAT',
            "description" => "$percent% VAT",
            "percentage" => $percent,
            "inclusive" => $inclusive
        ]);
        $this->taxRates[$key] = $rate;
        return $rate;
    }

    // Returns true if we have an active subscription
    // Returns false if the subscription has not been created yet
    // Throws an Exception if the card has been declined
    // Throws an SCANeededException if authentication is needed
    public function confirm($subscriptionId, $profile, $metadata, $order)
    {
        if (empty($subscriptionId))
            return false;

        $subscription = $this->retrieveSubscription($subscriptionId);

        // In the case we have an invalid subscription ID, recreate the subscription
        if (empty($subscription))
            return false;

        $paymentIntent = $this->retrievePaymentIntentFor($subscriptionId, $profile);

        if (empty($paymentIntent))
        {
            if ($profile['trial_days'] > 0)
            {
                // If 3DS is needed, we should send a link to the customer upon trial-end https://stripe.com/docs/billing/invoices/hosted
                // @todo - should maximize our chances for exemptions by authorizing for $0 as per https://stripe.com/docs/billing/subscriptions/payment
                return true;
            }
            else
                $this->_error = __("Could not retrieve Payment Intent for subscription");

            throw new SCANeededException("Unknown Error");
        }

        $subscription = $this->subscriptions[$subscriptionId];

        if ($subscription->status == "active" || $subscription->status == "trialing")
        {
            $this->updatePaymentIntentFrom($paymentIntent, $profile, $metadata);

            return true;
        }
        // In theory it should only be requires_action
        else if ($paymentIntent->status == "requires_action" || $paymentIntent->status == "requires_source_action")
        {
            $this->_piSecrets[$paymentIntent->id] = $paymentIntent->client_secret;
            throw new SCANeededException("Authentication Required");
        }
        else if ($subscription->status == "incomplete")
        {
            if (!empty($paymentIntent->last_payment_error->message))
                throw new CouldNotSaveException(__($paymentIntent->last_payment_error->message));
            else
                throw new CouldNotSaveException(__("Your card has been declined"));
        }
        else if ($subscription->status == "canceled")
        {
            return false;
        }

        return true;
    }

    public function confirmUpcomingInvoices($subscription, $profile)
    {
        if ($profile['trial_days'] == 0)
            return;

        if ($this->hasInitialFees($profile))
        {
            // At the moment, upcoming invoices do not have a Payment Intent and we cannot return the PI secret for authorization
            $this->_error = __("The subscription has a trial period but the card requires immediate authorization. Please use a different card.");
        }
    }

    public function hasInitialFees($profile)
    {
        if ($profile['initial_fee_stripe'] > 0)
            return true;

        if ($profile['shipping_stripe'] > 0 && $this->chargeShippingOnlyOnce())
            return true;
    }

    public function getTrialingSubscriptionsAmounts($quote = null)
    {
        if ($this->trialingSubscriptionsAmounts)
            return $this->trialingSubscriptionsAmounts;

        if (!$quote)
            $quote = $this->paymentsHelper->getQuote();

        $trialingSubscriptionsAmounts = [
            "subscriptions_total" => 0,
            "base_subscriptions_total" => 0,
            "shipping_total" => 0,
            "base_shipping_total" => 0,
            "discount_total" => 0,
            "base_discount_total" => 0,
            "tax_total" => 0,
            "base_tax_total" => 0
        ];

        if (!$quote)
            return $trialingSubscriptionsAmounts;

        $this->trialingSubscriptionsAmounts = $trialingSubscriptionsAmounts;

        $items = $quote->getAllItems();
        foreach ($items as $item)
        {
            $product = $this->paymentsHelper->getSubscriptionProductFromOrderItem($item);
            if (!$product)
                continue;

            if (!$product->getStripeSubEnabled())
                continue;

            $trial = $product->getStripeSubTrial();
            if (is_numeric($trial) && $trial > 0)
            {
                $item = $this->paymentsHelper->getSubscriptionQuoteItemWithTotalsFrom($item, $quote);

                $profile = $this->getSubscriptionDetails($product, $quote, $item, true, $trialEnd = null, true);
                $baseProfile = $this->getSubscriptionDetails($product, $quote, $item, true, $trialEnd = null, false);

                $shipping = $profile["shipping_magento"];
                $baseShipping = $baseProfile["shipping_magento"];
                if ($this->config->shippingIncludesTax())
                {
                    // $shipping -= $profile["tax_amount_shipping"];
                    // $baseShipping -= $baseProfile["tax_amount_shipping"];
                }

                $subtotal = $item->getRowTotal();
                $baseSubtotal = $item->getBaseRowTotal();
                if ($this->config->priceIncludesTax())
                {
                    $subtotal = $item->getRowTotalInclTax();
                    $baseSubtotal = $item->getBaseRowTotalInclTax();
                }

                $this->trialingSubscriptionsAmounts["subscriptions_total"] += $subtotal;
                $this->trialingSubscriptionsAmounts["base_subscriptions_total"] += $baseSubtotal;
                $this->trialingSubscriptionsAmounts["shipping_total"] += $shipping;
                $this->trialingSubscriptionsAmounts["base_shipping_total"] += $baseShipping;
                $this->trialingSubscriptionsAmounts["discount_total"] += $profile["discount_amount_magento"];
                $this->trialingSubscriptionsAmounts["base_discount_total"] += $baseProfile["discount_amount_magento"];
                $this->trialingSubscriptionsAmounts["tax_total"] += $profile["tax_amount_item"] + $profile["tax_amount_shipping"];
                $this->trialingSubscriptionsAmounts["base_tax_total"] += $baseProfile["tax_amount_item"] + $baseProfile["tax_amount_shipping"];
            }
        }

        return $this->trialingSubscriptionsAmounts;
    }

    public function formatInterval($stripeAmount, $currency, $intervalCount, $intervalUnit)
    {
        $amount = $this->paymentsHelper->formatStripePrice($stripeAmount, $currency);

        if ($intervalCount > 1)
            return __("%1 every %2 %3", $amount, $intervalCount, $intervalUnit . "s");
        else
            return __("%1 every %2", $amount, $intervalUnit);
    }

    public function invoiceNonTrialSubscriptionItems($order, $transactionId)
    {
        $qtysToInvoice = [];
        $orderItems = $this->getNonTrialSubscriptionOrderItems($order);
        $trialOrderItems = $this->getTrialSubscriptionOrderItems($order);
        $shipping = $baseShipping = 0;
        $shippingTax = $baseShippingTax = 0;

        foreach ($orderItems as $orderItem)
            $qtysToInvoice[$orderItem->getId()] = $orderItem->getQtyOrdered();

        // Magento 2.4.x partial invoices include the shipping cost of the items that were not invoiced
        // so we manually remove the shipping cost and shipping tax from the invoice grand total
        foreach ($trialOrderItems as $trialOrderItem)
        {
            $products = $this->paymentsHelper->getSubscriptionProductsFromOrderItem($trialOrderItem);
            foreach ($products as $product)
            {
                $profile = $this->getSubscriptionDetails($product, $order, $trialOrderItem, $isDryRun = false, $trialEnd = null, true);
                $baseProfile = $this->getSubscriptionDetails($product, $order, $trialOrderItem, $isDryRun = false, $trialEnd = null, false);
                $shipping += $profile["shipping_magento"];
                $baseShipping += $baseProfile["shipping_magento"];
            }
        }

        $invoice = $this->invoiceService->prepareInvoice($order, $qtysToInvoice);
        $invoice->setTransactionId($transactionId);
        $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
        // Magento force-includes the full order shipping amount on partial invoices - https://github.com/magento/magento2/issues/26286
        // $invoice->setBaseShipping($order->getBaseShippingAmount() - $baseShipping);
        // $invoice->setShipping($order->getShippingAmount() - $shipping);
        $invoice->setBaseTax($invoice->getBaseTax() - $baseShippingTax);
        $invoice->setTax($invoice->getTax() - $shippingTax);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $baseShipping - $baseShippingTax);
        $invoice->setGrandTotal($invoice->getGrandTotal() - $shipping - $shippingTax);
        $invoice->register();
        $this->paymentsHelper->saveInvoice($invoice);

        return $invoice;
    }

    public function getNonTrialSubscriptionOrderItems($order)
    {
        $items = [];
        $trialOrderItems = $this->getTrialSubscriptionOrderItems($order);

        foreach ($order->getAllVisibleItems() as $orderItem)
        {
            foreach ($trialOrderItems as $trialOrderItem)
                if ($orderItem->getId() == $trialOrderItem->getId())
                    continue 2;

            $items[$orderItem->getId()] = $orderItem;
        }

        return $items;
    }

    public function getTrialSubscriptionOrderItems($order)
    {
        $trialOrderItems = [];

        foreach ($order->getAllVisibleItems() as $orderItem)
        {
            $productIds = $this->paymentsHelper->getSubscriptionIdsFromOrderItem($orderItem);
            foreach ($productIds as $productId)
            {
                $product = $this->paymentsHelper->loadProductById($productId);
                if ($product->getStripeSubEnabled() && $product->getStripeSubTrial() > 0)
                    $trialOrderItems[$orderItem->getId()] = $orderItem;
            }
        }

        return $trialOrderItems;
    }
}
