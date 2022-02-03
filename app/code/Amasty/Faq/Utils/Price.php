<?php

namespace Amasty\Faq\Utils;

use Magento\Framework\Locale\CurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;

class Price
{
    /**
     * @var CurrencyInterface
     */
    private $currency;

    public function __construct(CurrencyInterface $localeCurrency, StoreManagerInterface $storeManager)
    {
        $this->currency = $localeCurrency->getCurrency(
            $storeManager->getStore()->getBaseCurrencyCode()
        );
    }

    /**
     * @param int|string|float $price
     *
     * @return string
     */
    public function toDefaultCurrency($price = 0)
    {
        return $this->currency->toCurrency(sprintf("%f", $price));
    }
}
