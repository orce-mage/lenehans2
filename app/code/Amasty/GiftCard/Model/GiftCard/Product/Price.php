<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\GiftCard\Product;

use Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
use Amasty\GiftCard\Model\Config\Source\Fee;
use Amasty\GiftCard\Model\GiftCard\Attributes;
use Magento\Catalog\Model\Product;

class Price extends \Magento\Catalog\Model\Product\Type\Price
{
    public function getPrice($product)
    {
        $price = 0;

        if ($product->getData('price')) {
            $price = $product->getData('price');
        } elseif (!$product->getAmAllowOpenAmount()
            && (count($this->getAmounts($product)) === 1)
            && !$product->hasCustomOptions()
        ) {
            $amounts = $this->getAmounts($product);
            $amount = array_shift($amounts);
            $price = (float)$amount['value'];
        }

        return $price;
    }

    /**
     * @param Product $product
     *
     * @return array
     */
    public function getAmounts(Product $product): array
    {
        $prices = $product->getAmGiftcardPrices();

        if ($prices === null) {
            if ($attribute = $product->getResource()->getAttribute(Attributes::GIFTCARD_PRICES)) {
                $attribute->getBackend()->afterLoad($product);
                $prices = $product->getAmGiftcardPrices();
            }
        }

        return (array)$prices;
    }

    public function getFinalPrice($qty, $product)
    {
        $finalPrice = $product->getPrice();

        if ($product->hasCustomOptions() && $product->getCustomOption(GiftCardOptionInterface::GIFTCARD_AMOUNT)) {
            $customValue = (float)$product->getCustomOption(GiftCardOptionInterface::GIFTCARD_AMOUNT)->getValue();

            if ($product->getAmGiftcardFeeEnable()) {
                $customValue = $this->applyFee($product, $customValue);
            }
            $finalPrice += $customValue;
        }
        $finalPrice = $this->_applyOptionsPrice($product, $qty, $finalPrice);
        $product->setData('final_price', $finalPrice);

        return max(0, $product->getData('final_price'));
    }

    /**
     * @param Product $product
     * @param float $customValue
     *
     * @return float
     */
    protected function applyFee(Product $product, float $customValue): float
    {
        $feeType = $product->getAmGiftcardFeeType();
        $feeValue = $product->getAmGiftcardFeeValue();

        if ($feeType && $feeValue) {
            switch ($feeType) {
                case Fee::PRICE_TYPE_PERCENT:
                    $customValue += $customValue * $feeValue / 100;
                    break;
                case Fee::PRICE_TYPE_FIXED:
                    $customValue += $feeValue;
                    break;
            }
        }

        return $this->priceCurrency->round($customValue);
    }
}
