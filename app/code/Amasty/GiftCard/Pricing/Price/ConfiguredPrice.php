<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Pricing\Price;

use Amasty\GiftCard\Api\Data\GiftCardOptionInterface;
use Magento\Catalog\Pricing\Price\ConfiguredPrice as CatalogConfiguredPrice;
use Magento\Catalog\Pricing\Price\ConfiguredPriceInterface;

class ConfiguredPrice extends CatalogConfiguredPrice implements ConfiguredPriceInterface
{
    /**
     * @return float
     */
    protected function calculateGiftCardPrice(): float
    {
        $product = $this->getProduct();
        $value = $product->getPrice();

        if ($product->hasCustomOptions()) {
            if ($amount = $product->getCustomOption(GiftCardOptionInterface::GIFTCARD_AMOUNT)) {
                $value = $amount->getValue() ?? 0.;
            }
        }

        return (float)$value;
    }

    public function getValue()
    {
        return $this->item ? $this->calculateGiftCardPrice() : $this->getBasePrice()->getValue();
    }
}
