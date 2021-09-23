<?php

namespace Swissup\CheckoutSuccess\Model\Filter;

class Html implements \Zend_Filter_Interface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $countryFactory;

    /**
     * Cunstruction regular expression
     */
    protected $constructionPattern = '/{{([a-z,A-Z]{0,30})(.*?)}}/si';

    /**
     * Construnctor
     *
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param array $variables
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Directory\Model\CountryFactory $countryFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->countryFactory = $countryFactory;
    }

    /**
     * Filter the string as template.
     *
     * @param string $value
     * @return string
     */
    public function filter($value)
    {
        if (!$this->getOrder()->getId()) {
            return $value;
        }
        if (preg_match_all($this->constructionPattern, $value, $constructions, PREG_SET_ORDER)) {
            foreach ($constructions as $index => $construction) {
                $replacedValue = '';
                $callback = array($this, $construction[1].'Directive');
                if (is_callable($callback)) {
                    try {
                        $replacedValue = call_user_func($callback, $construction);
                        $value = str_replace($construction[0], $replacedValue, $value);
                    } catch (\Exception $e) {
                        throw $e;
                    }
                }
            }
        }
        return $value;
    }

    /**
     * Get order from checkout sesssion
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->checkoutSession->getLastRealOrder();
    }

    /**
     * External order ID directive
     *
     * @return string
     */
    public function orderIdDirective()
    {
        return $this->getOrder()->getIncrementId();
    }

    /**
     * Order amount directive
     *
     * @return float
     */
    public function orderAmountDirective()
    {
        return number_format($this->getOrder()->getGrandTotal(), 2);
    }

    /**
     * Order base amount directive (amount in base currency)
     *
     * @return float
     */
    public function orderBaseAmountDirective()
    {
        return number_format($this->getOrder()->getBaseGrandTotal(), 2);
    }

    /**
     * Order currency code directive
     *
     * @return string
     */
    public function currencyDirective()
    {
        return $this->getOrder()->getOrderCurrency()->getCurrencyCode();
    }

    /**
     * Order currency symbol directive
     *
     * @return string
     */
    public function currencySymbolDirective()
    {
        return $this->getOrder()->getOrderCurrency()->getCurrencySymbol();
    }

    /**
     * Customer email from order directive
     *
     * @return string
     */
    public function customerEmailDirective()
    {
        return $this->getOrder()->getCustomerEmail();
    }

    /**
     * Customer Id from order directive
     *
     * @return string
     */
    public function customerIdDirective()
    {
        return $this->getOrder()->getCustomerId();
    }

    /**
     * Code of payment method directive
     *
     * @return string
     */
    public function paymentCodeDirective()
    {
        return $this->getOrder()->getPayment()->getMethodInstance()->getCode();
    }

    /**
     * Title of payment method directive
     *
     * @return string
     */
    public function paymentTitleDirective()
    {
        return $this->getOrder()->getPayment()->getMethodInstance()->getTitle();
    }

    /**
     * Code of shipping method directive
     *
     * @return string
     */
    public function shippingCodeDirective()
    {
        return $this->getOrder()->getShippingMethod();
    }

    /**
     * Title of shipping method directive
     *
     * @return string
     */
    public function shippingTitleDirective()
    {
        return $this->getOrder()->getShippingDescription();
    }

    /**
     * Order itemes data directive
     *
     * @return string
     */
    public function orderItemsDirective()
    {
        $orderedItems = [];
        $valuesToGet = array_flip([
            'product_id',
            'sku',
            'name',
            'weight',
            'qty_ordered',
            'row_total',
            'base_row_total',
            'row_total_incl_tax',
            'base_row_total_incl_tax'
        ]);
        foreach ($this->getOrder()->getAllVisibleItems() as $item) {
            $orderedItems[] = array_intersect_key($item->getData(), $valuesToGet);
        }

        return json_encode($orderedItems, JSON_HEX_APOS);
    }

    /**
     * Convert address data to JSON string
     *
     * @param  array $addrData
     * @return string
     */
    protected function processAddressData($addrData)
    {
        $valuesToGet = array_flip([
            'city',
            'company',
            'country_id',
            'fax',
            'firstname',
            'lastname',
            'middlename',
            'postcode',
            'prefix',
            'region',
            'region_id',
            'street',
            'suffix',
            'telephone'
        ]);
        $newData = array_intersect_key($addrData, $valuesToGet);
        $newData = array_filter($newData);
        if (isset($newData['country_id'])) {
            $country = $this->countryFactory->create();
            $country->loadByCode($newData['country_id']);
            $newData['country'] = $country->getName();
        }

        return json_encode($newData, JSON_HEX_APOS);
    }

    /**
     * Order billing address directive
     *
     * @return string
     */
    public function orderBillingAddressDirective()
    {
        return $this->processAddressData(
            $this->getOrder()->getBillingAddress()->getData()
        );
    }

    /**
     * Order shiping address directive
     *
     * @return string
     */
    public function orderShippingAddressDirective()
    {
        return $this->processAddressData(
            $this->getOrder()->getShippingAddress()->getData()
        );
    }
}
