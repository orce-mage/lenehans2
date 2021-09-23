<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Block\Email\Block\Sales\Product;

class ConfigurableRenderer extends \Magetrend\Email\Block\Email\Block\Sales\Product\DefaultRenderer
{
    public function getFormatedPrice()
    {
        $product = $this->getProduct();
        $price = $product->getPriceInfo()->getPrice('regular_price');
        return $this->priceCurrency->format(
            $price->getAmount()->getValue(),
            false,
            \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
            $product->getStore()
        );
    }

    public function getFormatedSpecialPrice()
    {
        $product = $this->getProduct();
        $price = $product->getPriceInfo()->getPrice('regular_price');
        $regularPrice = $price->getAmount()->getValue();
        $specialPrice = $product->getFinalPrice();

        if ($specialPrice == 0 || $specialPrice >= $regularPrice) {
            return false;
        }

        return $this->priceCurrency->format(
            $specialPrice,
            false,
            \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
            $product->getStore()
        );
    }
}