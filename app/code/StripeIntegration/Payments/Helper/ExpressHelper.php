<?php

namespace StripeIntegration\Payments\Helper;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Order\Shipment;

class ExpressHelper
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    private $directoryHelper;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    private $taxHelper;

    /**
     * @var \Magento\Tax\Api\TaxCalculationInterface
     */
    private $taxCalculation;

    /**
     * @var \StripeIntegration\Payments\Helper\Generic
     */
    private $stripeHelper;

    /**
     * Helper constructor.
     *
     * @param ScopeConfigInterface                           $scopeConfig
     * @param StoreManagerInterface                          $storeManager
     * @param PriceCurrencyInterface                         $priceCurrency
     * @param \Magento\Directory\Helper\Data                 $directoryHelper
     * @param \Magento\Tax\Helper\Data                       $taxHelper
     * @param \Magento\Tax\Api\TaxCalculationInterface       $taxCalculation
     * @param \StripeIntegration\Payments\Helper\Generic       $stripeHelper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Tax\Api\TaxCalculationInterface $taxCalculation,
        \StripeIntegration\Payments\Helper\Generic $stripeHelper,
        \StripeIntegration\Payments\Helper\Address $addressHelper,
        \StripeIntegration\Payments\Model\Config $config,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
        $this->directoryHelper = $directoryHelper;
        $this->taxHelper = $taxHelper;
        $this->taxCalculation = $taxCalculation;
        $this->stripeHelper = $stripeHelper;
        $this->addressHelper = $addressHelper;
        $this->paymentsConfig = $config;
        $this->countryFactory = $countryFactory;
        $this->registry = $registry;
    }

    /**
     * Get Store Config
     * @param      $path
     * @param mixed $store
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreConfig($path, $store = null)
    {
        if (!$store) {
            $store = $this->getStoreId();
        }

        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get Store Id
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Return default country code
     *
     * @param \Magento\Store\Model\Store|string|int $store
     * @return string
     */
    public function getDefaultCountry($store = null)
    {
        return $this->directoryHelper->getDefaultCountry($store);
    }

    /**
     * Get Default Shipping Address
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDefaultShippingAddress()
    {
        $address = [];
        $address['country'] = $this->getStoreConfig(Shipment::XML_PATH_STORE_COUNTRY_ID);
        $address['postalCode'] = $this->getStoreConfig(Shipment::XML_PATH_STORE_ZIP);
        $address['city'] = $this->getStoreConfig(Shipment::XML_PATH_STORE_CITY);
        $address['addressLine'] = [];
        $address['addressLine'][0] = $this->getStoreConfig(Shipment::XML_PATH_STORE_ADDRESS1);
        $address['addressLine'][1] = $this->getStoreConfig(Shipment::XML_PATH_STORE_ADDRESS2);
        if ($regionId = $this->getStoreConfig(Shipment::XML_PATH_STORE_REGION_ID)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $region = $objectManager->create('Magento\Directory\Model\Region')
                                    ->load($regionId);

            $address['region_id'] = $region->getRegionId();
            $address['region'] = $region->getName();
        }

        return $address;
    }

    public function isSubscriptionProduct()
    {
        if (!$this->paymentsConfig->isSubscriptionsEnabled())
            return false;

        // Check the catalog product that we are viewing
        $product = $this->registry->registry('product');

        if ($product && $product->getId())
        {
            if ($product->getTypeId() == "configurable")
            {
                $children = $product->getTypeInstance()->getUsedProducts($product);
                foreach ($children as $child)
                {
                    $childProduct = $this->stripeHelper->loadProductById($child->getEntityId());
                    if ($childProduct && $childProduct->getStripeSubEnabled())
                        return true;
                }
            }
            else
            {
                return $product->getStripeSubEnabled();
            }
        }

        return false;
    }

    public function isEnabled($location)
    {
        $active = $this->paymentsConfig->initStripe();
        $activeLocation = $this->paymentsConfig->getConfigData($location, "express");

        return $active && $activeLocation && $this->paymentsConfig->canCheckout();
    }

    /**
     * Get Billing Address
     * @param $request
     *
     * @return array
     */
    public function getBillingAddress($data)
    {
        return $this->addressHelper->getMagentoAddressFromPRAPIPaymentMethodData($data);
    }

    /**
     * Get Shipping Address from Result
     * @param $result
     *
     * @return array
     */
    public function getShippingAddressFromResult($result)
    {
        $address = $this->addressHelper->getMagentoAddressFromPRAPIResult($result['shippingAddress'], __("shipping"));
        $address['email'] = $result['payerEmail'];
        return $address;
    }

    /**
     * Get Label
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return string
     */
    public function getLabel($quote = null)
    {
        return $this->paymentsConfig->getPRAPIDescription();

        // $email = $this->stripeHelper->getCustomerEmail();
        // $first = $quote->getCustomerFirstname();
        // $last = $quote->getCustomerLastname();

        // if (empty($email) && empty($first) && empty($last)) {
        //     return (string) __('Order');
        // } elseif (empty($email)) {
        //     return (string) __('Order by %1 %2', $first, $last);
        // }

        // return (string) __('Order by %1 %2 <%3>', $first, $last, $email);
    }

    /**
     * Get Cart items
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCartItems($quote)
    {
        // Get Currency and Amount
        $useStoreCurrency = $this->paymentsConfig->useStoreCurrency();
        if ($useStoreCurrency) {
            $amount = $quote->getGrandTotal();
            $currency = $quote->getQuoteCurrencyCode();
            if (empty($currency))
                $currency = $quote->getStore()->getCurrentCurrency()->getCode();
            $discount = $quote->getSubtotal() - $quote->getSubtotalWithDiscount();
        } else {
            $amount = $quote->getBaseGrandTotal();
            $currency = $quote->getBaseCurrencyCode();
            if (empty($currency))
                $currency = $quote->getStore()->getBaseCurrencyCode();
            $discount = $quote->getBaseSubtotal() - $quote->getBaseSubtotalWithDiscount();
        }

        // Get Quote Items
        $shouldInclTax = $this->shouldCartPriceInclTax($quote->getStore());
        $displayItems = [];
        $taxAmount = 0;
        $items = $quote->getAllVisibleItems();
        foreach ($items as $item)
        {
            /** @var $item \Magento\Quote\Model\Quote\Item */
            if ($item->getParentItem())
                continue;

            if ($useStoreCurrency)
            {
                $rowTotal = $shouldInclTax ? $item->getRowTotalInclTax() : $item->getRowTotal();
                $price = $shouldInclTax ? $item->getPriceInclTax() : $item->getPrice();

                if (!$shouldInclTax)
                    $taxAmount += $item->getTaxAmount();
            }
            else
            {
                $rowTotal = $shouldInclTax ? $item->getBaseRowTotalInclTax() : $item->getBaseRowTotal();
                $price = $shouldInclTax ? $item->getBasePriceInclTax() : $item->getBasePrice();

                if (!$shouldInclTax)
                    $taxAmount += $item->getBaseTaxAmount();
            }

            $label = $item->getName();
            if ($item->getQty() > 1) {
                $formattedPrice = $this->priceCurrency->format($price, false);
                $label .= sprintf(' (%s x %s)', $item->getQty(), $formattedPrice);
            }

            $displayItems[] = [
                'label' => $label,
                'amount' => $this->stripeHelper->convertMagentoAmountToStripeAmount($rowTotal, $currency),
                'pending' => false
            ];
        }

        // Add Shipping
        if (!$quote->getIsVirtual()) {
            $address = $quote->getShippingAddress();
            if ($address->getShippingInclTax() > 0) {
                if ($useStoreCurrency) {
                    $price = $shouldInclTax ? $address->getShippingInclTax() : $address->getShippingAmount();
                    $displayItems[] = [
                        'label' => (string)__('Shipping'),
                        'amount' => $this->stripeHelper->convertMagentoAmountToStripeAmount($price, $currency)
                    ];
                } else {
                    $price = $shouldInclTax ? $address->getBaseShippingInclTax() : $address->getBaseShippingAmount();
                    $displayItems[] = [
                        'label' => (string)__('Shipping'),
                        'amount' => $this->stripeHelper->convertMagentoAmountToStripeAmount($price, $currency)
                    ];
                }
            }
        }

        // Add Tax
        if ($taxAmount > 0) {
            $displayItems[] = [
                'label' => __('Tax'),
                'amount' => $this->stripeHelper->convertMagentoAmountToStripeAmount($taxAmount, $currency)
            ];
        }

        // Add Discount
        if ($discount > 0)
        {
            $displayItems[] = [
                'label' => __('Discount'),
                'amount' => -$this->stripeHelper->convertMagentoAmountToStripeAmount($discount, $currency)
            ];
        }

        $data = [
            'currency' => strtolower($currency),
            'total' => [
                'label' => $this->getLabel($quote),
                'amount' => $this->stripeHelper->convertMagentoAmountToStripeAmount($amount, $currency),
                'pending' => false
            ],
            'displayItems' => $displayItems
        ];

        return $data;
    }

    /**
     * Should Cart Price Include Tax
     *
     * @param  null|int|string|Store $store
     * @return bool
     */
    public function shouldCartPriceInclTax($store = null)
    {
        if ($this->taxHelper->displayCartBothPrices($store)) {
            return true;
        } elseif ($this->taxHelper->displayCartPriceInclTax($store)) {
            return true;
        }

        return false;
    }

    /**
     * Get Product Price with(without) Taxes
     * @param \Magento\Catalog\Model\Product $product
     * @param float|null $price
     * @param bool $inclTax
     * @param int $customerId
     * @param int $storeId
     *
     * @return float
     * @throws LocalizedException
     */
    public function getProductDataPrice($product, $price = null, $inclTax = false, $customerId = null, $storeId = null)
    {
        if (!($taxAttribute = $product->getCustomAttribute('tax_class_id')))
            return $price;

        if (!$price) {
            $price = $product->getPrice();
        }

        $productRateId = $taxAttribute->getValue();
        $rate = $this->taxCalculation->getCalculatedRate($productRateId, $customerId, $storeId);
        if ((int) $this->scopeConfig->getValue(
            'tax/calculation/price_includes_tax',
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) === 1
        ) {
            $priceExclTax = $price / (1 + ($rate / 100));
        } else {
            $priceExclTax = $price;
        }

        $priceInclTax = $priceExclTax + ($priceExclTax * ($rate / 100));

        return round($inclTax ? $priceInclTax : $priceExclTax, PriceCurrencyInterface::DEFAULT_PRECISION);
    }

    /**
     * Check is Shipping Required
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return bool
     */
    public function shouldRequestShipping($quote, $product = null, $attribute = null)
    {
        // If this is not a virtual product, ask or shipping details
        if ($product && $product->getTypeId() == 'simple')
            return true;

        if ($product && $product->getTypeId() == 'configurable' && $attribute)
        {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $productTypeInstance = $objectManager->get('Magento\ConfigurableProduct\Model\Product\Type\Configurable');
            $productAttributeOptions = $productTypeInstance->getConfigurableAttributesAsArray($product);
            $options = $product->getTypeInstance()->getConfigurableOptions($product);
            foreach ($options as $data)
            {
                foreach ($data as $option)
                {
                    if ($option['value_index'] == $attribute)
                    {
                        $selectedProduct = $this->stripeHelper->loadProductBySku($option['sku']);
                        if ($selectedProduct && $selectedProduct->getTypeId() == 'simple')
                            return true;
                    }
                }
            }
        }

        if (!$quote)
            return false;

        // Otherwise, assuming that there are more items in the quote, ensure that all of them are virtual
        foreach ($quote->getAllItems() as $quoteItem) {
            if (!$quoteItem->getIsVirtual()) {
                return true;
            }
        }

        return false;
    }
}
