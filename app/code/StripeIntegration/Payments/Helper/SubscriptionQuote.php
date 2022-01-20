<?php

namespace StripeIntegration\Payments\Helper;

use StripeIntegration\Payments\Helper\Logger;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use StripeIntegration\Payments\Exception\SCANeededException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\CouldNotSaveException;

class SubscriptionQuote
{
    public function __construct(
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Store\Model\Store $storeManager,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory,
        \Magento\Sales\Model\AdminOrder\Create $adminOrderCreateModel,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \StripeIntegration\Payments\Helper\Address $addressHelper
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->storeManager = $storeManager;
        $this->quoteManagement = $quoteManagement;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->customerFactory = $customerFactory;
        $this->adminOrderCreateModel = $adminOrderCreateModel;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->productFactory = $productFactory;
        $this->currencyFactory = $currencyFactory;
        $this->addressHelper = $addressHelper;
    }

    public function createNewQuoteFrom($order, $productId, $qty, $baseCustomPrice, $customPrice = null)
    {
        $store = $this->storeManager->load($order->getStoreId());
        $currency = $store->getCurrentCurrencyCode();

        if (empty($currency))
        {
            if ($order->getIncrementId())
            {
                $currency = $order->getOrderCurrencyCode();
            }
            else
            {
                $currency = $order->getQuoteCurrencyCode();
            }

            $store->setCurrentCurrencyCode($currency);
        }

        $quote = $this->quoteFactory->create();
        $quote->setStore($store);
        $quote->setStoreId($store->getId());
        $quote->setQuoteCurrencyCode($currency);
        $quote->setCustomerEmail($order->getCustomerEmail());

        // Set quote customer
        if ($order->getCustomerIsGuest())
        {
            $quote->setCustomerIsGuest(true);
        }
        else
        {
            $customer = $this->customerRepositoryInterface->getById($order->getCustomerId());
            $quote->assignCustomer($customer);
        }

        // Set currency conversion rates
        $currentCurrencyToBaseCurrencyRate = 1;
        if ($order->getBaseToQuoteRate())
        {
            $quote->setBaseToQuoteRate($order->getBaseToQuoteRate());
            $currentCurrencyToBaseCurrencyRate = round(1 / $order->getBaseToQuoteRate(), 4);
        }
        if ($order->getBaseToOrderRate())
        {
            $quote->setBaseToOrderRate($order->getBaseToOrderRate());
            $currentCurrencyToBaseCurrencyRate = round(1 / $order->getBaseToOrderRate(), 4);
        }

        if (empty($customPrice))
            $customPrice = $store->getBaseCurrency()->convert($baseCustomPrice, $currency);

        if (empty($baseCustomPrice))
            $baseCustomPrice = round($customPrice * $currentCurrencyToBaseCurrencyRate, 4);

        // Set quote items
        $productModel = $this->productFactory->create()->load($productId);
        $quoteItem = $quote->addProduct($productModel, $qty);
        $quoteItem->setCustomPrice($customPrice);
        $quoteItem->setOriginalCustomPrice($customPrice);

        // Set quote addresses
        if ($quote->getIsVirtual())
        {
            $data = $this->addressHelper->filterAddressData($order->getBillingAddress()->getData());
            $quote->getBillingAddress()->addData($data);
        }
        else
        {
            $data = $this->addressHelper->filterAddressData($order->getBillingAddress()->getData());
            $quote->getBillingAddress()->addData($data);

            $data = $this->addressHelper->filterAddressData($order->getShippingAddress()->getData());
            $quote->getShippingAddress()->addData($data);

            // Set the shipping method
            $quote->getShippingAddress()
                ->setShippingMethod($order->getShippingMethod())
                ->setCollectShippingRates(true);
        }

        // Set the discount coupon
        if (!empty($order->getCouponCode()))
            $quote->setCouponCode($order->getCouponCode());

        // Collect quote totals
        $quote->setTotalsCollectedFlag(false)->collectTotals();

        // Depending on the shipping method, the shipping amount may not be set on the item itself
        if ($quote->getShippingAmount() > 0)
        {
            $quoteItem->setShippingAmount($quote->getShippingAmount());
            $quoteItem->setBaseShippingAmount($quote->getBaseShippingAmount());
        }

        return $quote;
    }
}
