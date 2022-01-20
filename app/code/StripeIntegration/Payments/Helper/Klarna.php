<?php

namespace StripeIntegration\Payments\Helper;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\CouldNotSaveException;

class Klarna
{
    public static $supported = [
        "AT" => ["country" => "Austria", "locales" => ["de-AT", "en-AT"], "currencies" => ["EUR"]],
        "BE" => ["country" => "Belgium", "locales" => ["fr-BE", "en-BE", "de-BE", "nl-BE"], "currencies" => ["EUR"]],
        "DK" => ["country" => "Denmark", "locales" => ["da-DK", "en-DK"], "currencies" => ["DKK"]],
        "FI" => ["country" => "Finland", "locales" => ["fi-FI", "sv-FI", "en-FI"], "currencies" => ["EUR"]],
        "DE" => ["country" => "Germany", "locales" => ["de-DE", "en-DE"], "currencies" => ["EUR"]],
        "IT" => ["country" => "Italy", "locales" => ["ca-IT", "de-IT", "fur-IT", "it-IT"], "currencies" => ["EUR"]],
        "NL" => ["country" => "Netherlands", "locales" => ["nl-NL", "en-NL"], "currencies" => ["EUR"]],
        "NO" => ["country" => "Norway", "locales" => ["nb-NO", "en-NO"], "currencies" => ["NOK"]],
        "ES" => ["country" => "Spain", "locales" => ["es-ES", "eu-ES", "gl-ES", "ast-ES", "ca-ES"], "currencies" => ["EUR"]],
        "SE" => ["country" => "Sweden", "locales" => ["sv-SE", "en-SE"], "currencies" => ["SEK"]],
        "CH" => ["country" => "Switzerland", "locales" => ["de-CH", "fr-CH", "it-CH", "en-CH"], "currencies" => ["CHF"]],
        "GB" => ["country" => "United Kingdom", "locales" => ["en-GB"], "currencies" => ["GBP"]],
        "US" => ["country" => "United States", "locales" => ["en-US"], "currencies" => ["USD"]]
    ];

    public function __construct(
        \StripeIntegration\Payments\Helper\Generic $helper,
        \Magento\Framework\Session\Generic $session,
        \StripeIntegration\Payments\Model\Config $config,
        \Magento\Checkout\Model\Cart $cart,
        \StripeIntegration\Payments\Helper\Locale $localeHelper,
        \StripeIntegration\Payments\Helper\Address $addressHelper
    ) {
        $this->helper = $helper;
        $this->session = $session;
        $this->config = $config;
        $this->cart = $cart;
        $this->localeHelper = $localeHelper;
        $this->addressHelper = $addressHelper;
    }

    public static function getSupportedCurrencies()
    {
        $currencies = [];
        foreach (Klarna::$supported as $key => $value) {
            foreach ($value["currencies"] as $currency) {
                $currencies[$currency] = $currency;
            }
        }
        return $currencies;
    }

    public static function getSupportedCountries()
    {
        $countries = [];
        foreach (Klarna::$supported as $countryCode => $value) {
            $countries[$countryCode] = $countryCode;
        }
        return $countries;
    }

    public function getSourceParams($quote)
    {
        $fields = $this->config->getAmountCurrencyFromQuote($quote);

        if ($quote->getBillingAddress() && $quote->getBillingAddress()->getCountryId())
            $address = $quote->getBillingAddress();
        elseif ($quote->getShippingAddress() && $quote->getShippingAddress()->getCountryId())
            $address = $quote->getShippingAddress();
        else
            throw new \Exception("Cannot create Klarna source; quote has no address set.");

        $country = $address->getCountryId();

        if (empty($address->getStreetLine(1)))
            throw new \Exception((string)__("You must first set a billing address before you can use this payment method"));

        $supported = $this::$supported;
        $email = $quote->getCustomerEmail();

        $testEmail = $this->getTestEmail();
        if (!empty($testEmail))
            $email = $testEmail;

        if (!in_array($country, array_keys($supported)))
            throw new \Exception((string)__("The selected billing country %1 is not supported by Klarna", $country));

        if (!in_array($fields["currency"], $supported[$country]["currencies"]))
            throw new \Exception((string)__("The selected currency %1 is not supported for the billing country %2", $fields["currency"], $supported[$country]["country"]));

        $params = [
            "type" => "klarna",
            "flow" => "redirect",
            "amount" => $fields["amount"],
            "currency" => $fields["currency"],
            "klarna" => [
                "product" => "payment",
                "purchase_country" => $country,
                "first_name" => $address->getFirstname(),
                "last_name" => $address->getLastname(),
                "locale" => $this->localeHelper->getKlarnaLocale()
            ],
            "source_order" => [
                "items" => $this->getOrderItems($quote)
            ],
            "owner" => $this->addressHelper->getStripeAddressFromMagentoAddress($address),
            "redirect" => [
                "return_url" => $this->helper->getUrl('stripe/payment/index')
            ]
        ];

        if (strtoupper($country) == "US")
        {
            $optionsConfig = $this->config->getConfigData("custom_payment_methods", "klarna");
            if (!empty($optionsConfig))
            {
                $options = [];
                if (strpos($optionsConfig, "payin4") !== false)
                    $options[] = "payin4";
                if (strpos($optionsConfig, "installments") !== false)
                    $options[] = "installments";

                if (!empty($options))
                    $params["klarna"]["custom_payment_methods"] = implode(",", $options);
            }
        }

        $attachment = $this->getKlarnaAttachment();
        if (!empty($attachment))
            $params["klarna"]["attachment"] = $attachment;

        if (!$quote->getIsVirtual())
        {
            if ($quote->getShippingAddress() && $quote->getShippingAddress()->getCountryId())
                $address = $quote->getShippingAddress();

            $params["source_order"]["shipping"] = $this->addressHelper->getStripeShippingAddressFromMagentoAddress($address);

            if ($address->getTelephone())
                $params["source_order"]["shipping"]["phone"] = $address->getTelephone();

            $params["klarna"]["shipping_first_name"] = $address->getFirstname();
            $params["klarna"]["shipping_last_name"] = $address->getLastname();

            if (!in_array($address->getCountryId(), array_keys($supported)))
                throw new \Exception((string)__("The selected shipping country %1 is not supported by Klarna", $address->getCountryId()));
        }

        if ($quote->getCustomerDob())
        {
            $parts = explode("-", $quote->getCustomerDob());
            $params["klarna"]["owner_dob_day"] = $parts[2];
            $params["klarna"]["owner_dob_month"] = $parts[1];
            $params["klarna"]["owner_dob_year"] = $parts[0];
        }

        return $params;
    }

    public function createSource($quote)
    {
        $params = $this->getSourceParams($quote);
        return \Stripe\Source::create($params);
    }

    public function updateSource($sourceId, $quote)
    {
        $source = \Stripe\Source::retrieve($sourceId);
        $params = $this->getSourceParams($quote);
        unset($params["type"]);
        unset($params["currency"]);
        unset($params["flow"]);
        unset($params["redirect"]);
        unset($params["klarna"]["product"]);
        unset($params["klarna"]["purchase_country"]);
        unset($params["klarna"]["custom_payment_methods"]);
        unset($params["source_order"]["items"]);
        return \Stripe\Source::update($sourceId, $params);
    }

    public function getKlarnaAttachment()
    {
        // Overwrite this method to provide additional purchase information for your business to increase approval rates
        // Values provided vary based on business type and can be found at https://developers.klarna.com/api/#checkout-api__create-a-new-order__attachment

        // $quote = $this->helper->getQuote();

        return null;
    }

    public function getOrderItems($quote)
    {
        $items = [];
        $useStoreCurrency = $this->config->getConfigData('use_store_currency');
        $tax = 0;
        $discount = 0;
        $shipping = 0;

        if ($useStoreCurrency)
        {
            $currency = $quote->getQuoteCurrencyCode();
            if (!$quote->getIsVirtual())
            {
                $shippingAddress = $quote->getShippingAddress();
                $shipping = $shippingAddress->getShippingAmount();
                $tax += $shippingAddress->getShippingTaxAmount();
            }

            $discount = $quote->getSubtotal() - $quote->getSubtotalWithDiscount();
        }
        else
        {
            $currency = $quote->getBaseCurrencyCode();
            if (!$quote->getIsVirtual())
            {
                $shippingAddress = $quote->getShippingAddress();
                $shipping = $shippingAddress->getBaseShippingAmount();
                $tax += $shippingAddress->getBaseShippingTaxAmount();
            }

            $discount = $quote->getBaseSubtotal() - $quote->getBaseSubtotalWithDiscount();
        }

        $cents = $this->helper->isZeroDecimal($currency) ? 1 : 100;

        $quoteItems = $quote->getAllVisibleItems();
        foreach ($quoteItems as $item)
        {
            if ($useStoreCurrency)
            {
                $amount = $item->getRowTotal();
                $tax += $item->getTaxAmount();
            }
            else
            {
                $amount = $item->getBaseRowTotal();
                $tax += $item->getBaseTaxAmount();
            }

            $items[] = [
                "type" => "sku",
                "parent" => $item->getSku(),
                "description" => $item->getName(),
                "quantity" => $item->getQty(),
                "currency" => $currency,
                "amount" => round($amount * $cents)
            ];
        }

        if ($tax > 0)
        {
            $items[] = [
                "type" => "tax",
                "description" => "Tax",
                "currency" => $currency,
                "amount" => round($tax * $cents)
            ];
        }

        if ($discount > 0)
        {
            $items[] = [
                "type" => "discount",
                "description" => "Discount",
                "currency" => $currency,
                "amount" => -round($discount * $cents)
            ];
        }

        if ($shipping > 0)
        {
            $items[] = [
                "type" => "shipping",
                "description" => "Shipping",
                "currency" => $currency,
                "amount" => round($shipping * $cents)
            ];
        }

        return $items;
    }

    public function getPaymentOptions($source)
    {
        $paymentOptions = [];

        if (empty($source->klarna->payment_method_categories))
            throw new LocalizedException(__("Sorry, there are no available payment options"));

        $optionsConfig = explode(",", $this->config->getConfigData("custom_payment_methods", "klarna"));
        $categories = explode(",", $source->klarna->payment_method_categories);
        foreach ($categories as $category)
        {
            if (!in_array($category, $optionsConfig))
                continue;

            if (!$this->meetsMinimumAmount($category, $source))
                continue;

            $keyName = $category . "_name";
            $keyRedirectUrl = $category . "_redirect_url";
            $paymentOptions[$category] = [
                "key" => $category,
                "name" => $source->klarna->$keyName,
                "redirect_url" => $source->klarna->$keyRedirectUrl
            ];
        }

        return $paymentOptions;
    }

    public function meetsMinimumAmount($category, $source)
    {
        if ($category != "pay_over_time")
            return true;

        switch ($source->klarna->purchase_country)
        {
            case "US":
                return ($source->amount >= 3500);
            case "UK":
                return ($source->amount >= 3500);
            default:
                return true;
        }
    }

    // Use this for testing based on https://stripe.com/docs/sources/klarna#testing-klarna-payments
    public function getTestEmail()
    {
        // return "test+red@example.com"; // (US) The selected payment option becomes unavailable
        // return "test+denied@stripe.com"; // (US) Authorization fails with "Your purchase can not be accepted"
        return null;
    }
}
